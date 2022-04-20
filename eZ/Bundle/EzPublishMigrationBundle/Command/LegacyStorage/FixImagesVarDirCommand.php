<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishMigrationBundle\Command\LegacyStorage;

use DOMDocument;
use Exception;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ChainConfigResolver;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Gateway as ContentGateway;
use eZ\Publish\Core\FieldType\Image\ImageStorage\Gateway as ImageGateway;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Console\Exception\RuntimeException;

class FixImagesVarDirCommand extends Command
{
    const DEFAULT_ITERATION_COUNT = 100;
    const STORAGE_IMAGES_PATH = '/storage/images/';

    /**
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    private $db;

    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Gateway
     */
    private $contentGateway;

    /**
     * @var \eZ\Publish\Core\FieldType\Image\ImageStorage\Gateway
     */
    private $imageGateway;

    /**
     * @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ChainConfigResolver
     */
    private $configResolver;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\SiteAccess
     */
    private $siteaccess;

    /**
     * @var int
     */
    protected $done = 0;

    /**
     * @var string
     */
    private $phpPath;

    /**
     * @var bool
     */
    private $dryRun;

    /**
     * @var int
     */
    private $varDir;

    /**
     * @var array
     */
    private $imageAttributes = [];

    public function __construct(
        ChainConfigResolver $configResolver,
        SiteAccess $siteaccess,
        ContentGateway $contentGateway,
        ImageGateway $imageGateway
    ) {
        parent::__construct();
        $this->configResolver = $configResolver;
        $this->siteaccess = $siteaccess;
        $this->contentGateway = $contentGateway;
        $this->imageGateway = $imageGateway;
    }

    protected function configure()
    {
        $this
            ->setName('ezplatform:fix_images_var_dir')
            ->setDescription(
                'This update script will fix database references to images that are not placed in the current var_dir.'
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Execute a dry run'
            )
            ->addOption(
                'iteration-count',
                null,
                InputArgument::OPTIONAL,
                'Limit how many records get updated by single process',
                self::DEFAULT_ITERATION_COUNT
            )
            ->setHelp(
                <<<EOT
The command <info>%command.name%</info> fixes database references to images that are not placed in the current var_dir.

This may for instance occur when the var_dir setting is changed. This script will update the database references to the new path

Since this script can potentially run for a very long time, to avoid memory exhaustion run it in
production environment using <info>--env=prod</info> switch and with <info>--no-debug</info> for non-prod environments.

EOT
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $iterationCount = (int) $input->getOption('iteration-count');
        $this->dryRun = $input->getOption('dry-run');
        $consoleScript = $_SERVER['argv'][0];

        $this->varDir = $this->configResolver->getParameter(
            'var_dir',
            null,
            $this->siteaccess->name
        );

        if (getenv('INNER_CALL')) {
            $this->processImages($iterationCount, $output);
            $output->writeln($this->done);
        } else {
            $output->writeln([
                sprintf('Fixing image references using siteaccess %s (var_dir: %s)', $this->siteaccess->name, $this->varDir),
                'Calculating number of Images to fix...',
            ]);

            $count = $this->countImagesToFix();
            $output->writeln([
                sprintf('Found total of Images for fixing: %d', $count),
                '',
            ]);

            if ($count == 0) {
                $output->writeln('Nothing to process, exiting.');

                return;
            }

            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion(
                '<question>Are you sure you want to proceed?</question> ',
                false
            );

            if (!$helper->ask($input, $output, $question)) {
                $output->writeln('');

                return;
            }

            $progressBar = $this->getProgressBar($count, $output);
            $progressBar->start();

            for ($fixed = 0; $fixed < $count; $fixed += $iterationCount) {
                $processScriptFragments = [
                    $this->getPhpPath(),
                    $consoleScript,
                    $this->getName(),
                    '--iteration-count=' . $iterationCount,
                    '--siteaccess=' . $this->siteaccess->name,
                ];

                $process = new Process(
                    implode(' ', $processScriptFragments)
                );

                $process->setEnv(['INNER_CALL' => 1]);
                $process->run();

                if (!$process->isSuccessful()) {
                    throw new RuntimeException($process->getErrorOutput());
                }

                $doneInProcess = (int)$process->getOutput();
                $this->done += $doneInProcess;
                $progressBar->advance($doneInProcess);
            }

            $progressBar->finish();
            $output->writeln([
                '',
                sprintf('Done: %d', $this->done),
            ]);
        }
    }

    /**
     * @param int $limit
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function processImages($limit, OutputInterface $output)
    {
        $images = $this->getImagesToFix($limit);

        foreach ($images as $image) {
            $filePath = $image['filepath'];
            $relativePath = substr(
                $filePath,
                strpos($filePath, self::STORAGE_IMAGES_PATH)
            );

            $newFilePath = $this->varDir . $relativePath;

            if (!$this->dryRun) {
                $this->updateImage($image['id'], $image['contentobject_attribute_id'], $filePath, $newFilePath);
            }

            ++$this->done;
        }

        if (!$this->dryRun) {
            $this->updateContentObjectAtributes();
        }
    }

    /**
     * @param int $imageId
     * @param int $contentObjectAttributeId
     * @param string $oldFilePath
     * @param string $newFilePath
     */
    protected function updateImage($imageId, $contentObjectAttributeId, $oldFilePath, $newFilePath)
    {
        $this->imageGateway->updateImageFilePath($imageId, $newFilePath);
        $this->imageAttributes[$contentObjectAttributeId][$oldFilePath] = $newFilePath;
    }

    protected function updateContentObjectAtributes()
    {
        foreach ($this->imageAttributes as $attributeId => $files) {
            $attributeObjects = $this->contentGateway->getContentObjectAttributesById($attributeId);

            foreach ($attributeObjects as $attributeObject) {
                $dom = new DOMDocument('1.0', 'utf-8');

                try {
                    $dom->loadXML('');
                } catch (Exception $e) {
                    continue;
                }

                foreach ($dom->getElementsByTagName('ezimage') as $ezimageNode) {
                    $oldPath = $ezimageNode->getAttribute('url');

                    if (isset($files[$oldPath])) {
                        $ezimageNode->setAttribute('url', $files[$oldPath]);
                        $ezimageNode->setAttribute('dirpath', \dirname($files[$oldPath]));
                    }

                    foreach ($ezimageNode->getElementsByTagName('alias') as $ezimageAlias) {
                        $oldPath = $ezimageAlias->getAttribute('url');
                        if (isset($files[$oldPath])) {
                            $ezimageAlias->setAttribute('url', $files[$oldPath]);
                            $ezimageAlias->setAttribute('dirpath', \dirname($files[$oldPath]));
                        }
                    }
                }

                $this->contentGateway->updateContentObjectAtribute($attributeObject['id'], $attributeObject['version'], $dom->saveXML());
            }
        }
    }

    /**
     * @param int $limit
     *
     * @return array
     */
    protected function getImagesToFix($limit)
    {
        return $this->imageGateway->getImagesOutsidePath('/' . $this->varDir . '/storage/', $limit, 0);
    }

    /**
     * @return int
     */
    protected function countImagesToFix()
    {
        return $this->imageGateway->countImageReferencesOutsidePath('/' . $this->varDir . '/storage/');
    }

    /**
     * @param int $maxSteps
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return \Symfony\Component\Console\Helper\ProgressBar
     */
    protected function getProgressBar($maxSteps, OutputInterface $output)
    {
        $progressBar = new ProgressBar($output, $maxSteps);
        $progressBar->setFormat(
            ' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%'
        );

        return $progressBar;
    }

    /**
     * @return string
     */
    private function getPhpPath()
    {
        if ($this->phpPath) {
            return $this->phpPath;
        }
        $phpFinder = new PhpExecutableFinder();
        $this->phpPath = $phpFinder->find();
        if (!$this->phpPath) {
            throw new RuntimeException(
                'The php executable could not be found, it\'s needed for executing parable sub processes, so add it to your PATH environment variable and try again'
            );
        }

        return $this->phpPath;
    }
}
