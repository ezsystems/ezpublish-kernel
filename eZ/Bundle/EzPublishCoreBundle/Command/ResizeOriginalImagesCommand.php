<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\Command;

use Exception;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\Core\FieldType\Image\Value;
use eZ\Publish\Core\IO\IOServiceInterface;
use Liip\ImagineBundle\Binary\BinaryInterface;
use Liip\ImagineBundle\Exception\Imagine\Filter\NonExistingFilterException;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Model\Binary;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface;

/**
 * This command resizes original images stored in ezimage FieldType in given ContentType using the selected variation.
 */
class ResizeOriginalImagesCommand extends Command
{
    const DEFAULT_ITERATION_COUNT = 25;
    const DEFAULT_REPOSITORY_USER = 'admin';

    /**
     * @var \eZ\Publish\API\Repository\PermissionResolver
     */
    private $permissionResolver;

    /**
     * @var \eZ\Publish\API\Repository\UserService
     */
    private $userService;

    /**
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    private $contentTypeService;

    /**
     * @var \eZ\Publish\API\Repository\SearchService
     */
    private $searchService;

    /**
     * @var \Liip\ImagineBundle\Imagine\Filter\FilterManager
     */
    private $filterManager;

    /**
     * @var \eZ\Publish\Core\IO\IOServiceInterface
     */
    private $ioService;

    /**
     * @var \Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface
     */
    private $extensionGuesser;

    public function __construct(
        PermissionResolver $permissionResolver,
        UserService $userService,
        ContentTypeService $contentTypeService,
        SearchService $searchService,
        FilterManager $filterManager,
        IOServiceInterface $ioService,
        ExtensionGuesserInterface $extensionGuesser
    ) {
        $this->permissionResolver = $permissionResolver;
        $this->userService = $userService;
        $this->contentTypeService = $contentTypeService;
        $this->searchService = $searchService;
        $this->filterManager = $filterManager;
        $this->ioService = $ioService;
        $this->extensionGuesser = $extensionGuesser;

        parent::__construct();
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->permissionResolver->setCurrentUserReference(
            $this->userService->loadUserByLogin($input->getOption('user'))
        );
    }

    protected function configure()
    {
        $this->setName('ezplatform:images:resize-original')->setDefinition(
            [
                new InputArgument('contentTypeIdentifier', InputArgument::REQUIRED,
                    'Indentifier of ContentType which has ezimage FieldType.'),
                new InputArgument('imageFieldIdentifier', InputArgument::REQUIRED,
                    'Identifier of field of ezimage type.'),
                new InputArgument('variation', InputArgument::OPTIONAL,
                    'Variation which will be used for original images.', 'original'),
                new InputArgument('iteration-count', InputArgument::OPTIONAL,
                    'Iteration count. Number of images to be recreated in a single iteration, for avoiding using too much memory.',
                    self::DEFAULT_ITERATION_COUNT),
            ]
        )->addOption(
            'user',
            'u',
            InputOption::VALUE_OPTIONAL,
            'eZ Platform username (with Role containing at least Content policies: read, versionread)',
            self::DEFAULT_REPOSITORY_USER
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $contentTypeIdentifier = $input->getArgument('contentTypeIdentifier');
        $imageFieldIdentifier = $input->getArgument('imageFieldIdentifier');
        $variation = $input->getArgument('variation');
        $iterationCount = (int)$input->getArgument('iteration-count');

        $contentType = $this->contentTypeService->loadContentTypeByIdentifier($contentTypeIdentifier);
        $fieldType = $contentType->getFieldDefinition($imageFieldIdentifier);
        if (!$fieldType || $fieldType->fieldTypeIdentifier !== 'ezimage') {
            $output->writeln(
                sprintf(
                    "<error>FieldType of identifier '%s' of ContentType '%s' has to be 'ezimage', '%s' given.</error>",
                    $imageFieldIdentifier,
                    $contentType->identifier,
                    $fieldType ? $fieldType->fieldTypeIdentifier : ''
                )
            );

            return;
        }

        try {
            $this->filterManager->getFilterConfiguration()->get($variation);
        } catch (NonExistingFilterException $e) {
            $output->writeln(
                sprintf(
                    '<error>%s</error>',
                    $e->getMessage()
                )
            );

            return;
        }

        $query = new Query();
        $query->filter = new Query\Criterion\ContentTypeIdentifier($contentType->identifier);

        $totalCount = $this->searchService->findContent($query)->totalCount;
        $query->limit = $iterationCount;

        if ($totalCount > 0) {
            $output->writeln(
                sprintf(
                    '<info>Found %d images matching given criteria.</info>',
                    $totalCount
                )
            );
        } else {
            $output->writeln(
                sprintf(
                    '<info>No images matching given criteria (ContentType: %s, FieldType %s) found. Exiting.</info>',
                    $contentTypeIdentifier,
                    $imageFieldIdentifier
                )
            );

            return;
        }

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('<question>The changes you are going to perform cannot be undone. Please remember to do a proper backup before. Would you like to continue?</question> ', false);
        if (!$helper->ask($input, $output, $question)) {
            return;
        }

        $progressBar = new ProgressBar($output, $totalCount);
        $progressBar->start();

        while ($query->offset <= $totalCount) {
            $results = $this->searchService->findContent($query);

            /** @var SearchHit $hit */
            foreach ($results->searchHits as $hit) {
                $this->resize($output, $hit, $imageFieldIdentifier, $variation);
                $progressBar->advance();
            }

            $query->offset += $iterationCount;
        }

        $progressBar->finish();
        $output->writeln('');
        $output->writeln(
            sprintf(
                "<info>All images have been successfully resized using '%s' variation.</info>",
                $variation
            )
        );
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \eZ\Publish\API\Repository\Values\Content\Search\SearchHit $hit
     * @param string $imageFieldIdentifier
     * @param string $variation
     */
    private function resize(OutputInterface $output, SearchHit $hit, string $imageFieldIdentifier, string $variation)
    {
        try {
            /** @var Value $field */
            foreach ($hit->valueObject->fields[$imageFieldIdentifier] as $language => $field) {
                if (null === $field->id) {
                    continue;
                }
                $binaryFile = $this->ioService->loadBinaryFile($field->id);
                $mimeType = $this->ioService->getMimeType($field->id);
                $binary = new Binary(
                    $this->ioService->getFileContents($binaryFile),
                    $mimeType,
                    $this->extensionGuesser->guess($mimeType)
                );

                $recreated = $this->filterManager->applyFilter($binary, $variation);
                $this->store($recreated, $field);
            }
        } catch (Exception $e) {
            $output->writeln(
                sprintf(
                    '<error>Can not resize image ID: %s, error message: %s.</error>',
                    $field->imageId,
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * Copy of eZ\Bundle\EzPublishCoreBundle\Imagine\IORepositoryResolver::store()
     * Original one cannot be used since original method uses eZ\Bundle\EzPublishCoreBundle\Imagine\IORepositoryResolver::getFilePath()
     * so ends-up with image stored in _aliases instead of overwritten original image.
     *
     * @param \Liip\ImagineBundle\Binary\BinaryInterface $binary
     * @param \eZ\Publish\Core\FieldType\Image\Value $image
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue
     */
    private function store(BinaryInterface $binary, Value $image)
    {
        $tmpFile = tmpfile();
        fwrite($tmpFile, $binary->getContent());
        $tmpMetadata = stream_get_meta_data($tmpFile);
        $binaryCreateStruct = $this->ioService->newBinaryCreateStructFromLocalFile($tmpMetadata['uri']);
        $binaryCreateStruct->id = $image->id;
        $this->ioService->createBinaryFile($binaryCreateStruct);
        fclose($tmpFile);
    }
}
