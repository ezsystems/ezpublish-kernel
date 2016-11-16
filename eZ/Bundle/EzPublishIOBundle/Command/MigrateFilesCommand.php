<?php

/**
 * File containing the MigrateFilesCommand class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\Command;

use eZ\Bundle\EzPublishIOBundle\Migration\MigrationHandlerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class MigrateFilesCommand extends Command
{
    private $configuredMetadataHandlers;

    private $configuredBinarydataHandlers;

    /** @var \eZ\Bundle\EzPublishIOBundle\Migration\MigrationHandlerInterface */
    private $migrationHandler;

    public function __construct(
        $configuredMetadataHandlers,
        $configuredBinarydataHandlers,
        MigrationHandlerInterface $migrationHandler
    ) {
        $this->configuredMetadataHandlers = $configuredMetadataHandlers;
        $this->configuredBinarydataHandlers = $configuredBinarydataHandlers;
        $this->configuredMetadataHandlers['default'] = [];
        $this->configuredBinarydataHandlers['default'] = [];

        $this->migrationHandler = $migrationHandler;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('ezplatform:io:migrate-files')
            ->setDescription('Migrates files from one IO repository to another')
            ->addOption('from', null, InputOption::VALUE_REQUIRED, 'Migrate from <from_metadata_handler>,<from_binarydata_handler>')
            ->addOption('to', null, InputOption::VALUE_REQUIRED, 'Migrate to <to_metadata_handler>,<to_binarydata_handler>')
            ->addOption('list-io-configs', null, InputOption::VALUE_NONE, 'List available IO configurations')
            ->addOption('remove-files', null, InputOption::VALUE_NONE, 'Remove source files after copying')
            ->addOption('bulk-count', null, InputOption::VALUE_REQUIRED, 'Number of files processed at once', 100)
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Execute a dry run')
            ->setHelp(
                <<<EOT
The command <info>%command.name%</info> migrates files from one IO repository
to another.

It can for example be used to migrate local files from the default IO
configuration to a new IO configuration, like a clustered setup.

The <info>--from</info> and <info>--to</info> values must be specified as <info><metadata_handler>,<binarydata_handler></info>.
If <info>--from</info> is omitted, the default IO configuration will be used.
If <info>--to</info> is omitted, the first non-default IO configuration will be used.

<fg=red>During the script execution the files should not be modified. To avoid
surprises you are advised to create a backup and/or execute a dry run before
proceeding with actual update.</>

Since this script can potentially run for a very long time, to avoid memory
exhaustion run it in production environment using <info>--env=prod</info> switch.

EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('list-io-configs')) {
            $this->outputConfiguredHandlers($output);

            return;
        }

        $output->writeln($this->getProcessedHelp());

        $fromHandlers = $input->getOption('from') ? explode(',', $input->getOption('from')) : null;
        $toHandlers = $input->getOption('to') ? explode(',', $input->getOption('to')) : null;
        if (!$this->areHandlerOptionsValid($fromHandlers, $toHandlers, $output)) {
            return;
        }

        if (!$fromHandlers) {
            $fromHandlers = ['default', 'default'];
        }
        if (!$toHandlers) {
            $toHandlers = [
                array_keys($this->configuredMetadataHandlers)[0],
                array_keys($this->configuredBinarydataHandlers)[0],
            ];
        }

        $output->writeln([
            "Migrating from '$fromHandlers[0],$fromHandlers[1]' to '$toHandlers[0],$toHandlers[1]'",
            '',
        ]);
        $this->migrationHandler->setIODataHandlersByIdentifiers(
            $fromHandlers[0],
            $fromHandlers[1],
            $toHandlers[0],
            $toHandlers[1]
        );

        $totalCount = $this->migrationHandler->countFiles();
        $output->writeln([
            'Found total files to update: ' . $totalCount,
            '',
        ]);

        if ($totalCount === 0) {
            $output->writeln('Nothing to process.');

            return;
        }

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            '<question>Are you sure you want to proceed?</question> ',
            false
        );

        if (!$helper->ask($input, $output, $question)) {
            $output->writeln('Aborting.');

            return;
        }

        $bulkCount = $input->getOption('bulk-count');
        $dryRun = $input->getOption('dry-run');

        $this->migrateFiles(
            $totalCount,
            $bulkCount,
            $dryRun,
            $output
        );
    }

    /**
     * Output the configured meta/binary data handlers.
     *
     * @param OutputInterface $output
     */
    protected function outputConfiguredHandlers(OutputInterface $output)
    {
        $output->writeln(
            'Configured meta data handlers: ' . implode(', ', array_keys($this->configuredMetadataHandlers))
        );
        $output->writeln(
            'Configured binary data handlers: ' . implode(', ', array_keys($this->configuredBinarydataHandlers))
        );
    }

    /**
     * Verify that the handler options have been set to meaningful values.
     *
     * @param mixed $fromHandlers
     * @param mixed $toHandlers
     * @param OutputInterface $output
     * @return bool
     */
    protected function areHandlerOptionsValid(
        $fromHandlers,
        $toHandlers,
        OutputInterface $output
    ) {
        if ($fromHandlers) {
            if (count($fromHandlers) !== 2) {
                $output->writeln('Enter two comma separated values for the --from option: <from_metadata_handler>,<from_binarydata_handler>');

                return false;
            }

            if (!in_array($fromHandlers[0], array_keys($this->configuredMetadataHandlers))) {
                $output->writeln("From meta data handler '$fromHandlers[0]' is not configured.");
                $this->outputConfiguredHandlers($output);

                return false;
            }

            if (!in_array($fromHandlers[1], array_keys($this->configuredBinarydataHandlers))) {
                $output->writeln("From binary data handler '$fromHandlers[1]' is not configured.");
                $this->outputConfiguredHandlers($output);

                return false;
            }
        }

        if ($toHandlers) {
            if (count($toHandlers) !== 2) {
                $output->writeln('Enter two comma separated values for the --to option: <to_metadata_handler>,<to_binarydata_handler>');

                return false;
            }

            if (!in_array($toHandlers[0], array_keys($this->configuredMetadataHandlers))) {
                $output->writeln("To meta data handler '$toHandlers[0]' is not configured.");
                $this->outputConfiguredHandlers($output);

                return false;
            }

            if (!in_array($toHandlers[1], array_keys($this->configuredBinarydataHandlers))) {
                $output->writeln("To binary data handler '$toHandlers[1]' is not configured.");
                $this->outputConfiguredHandlers($output);

                return false;
            }
        }

        if ($fromHandlers && $toHandlers && $fromHandlers === $toHandlers) {
            $output->writeln('From and to handlers are the same. Nothing to do.');

            return false;
        }

        return true;
    }

    /**
     * Migrate files.
     *
     * @param int $fileCount
     * @param int $bulkCount
     * @param bool $dryRun
     * @param OutputInterface $output
     */
    protected function migrateFiles(
        $fileCount,
        $bulkCount,
        $dryRun,
        OutputInterface $output
    ) {
        $passCount = ceil($fileCount / $bulkCount);

        $progress = new ProgressBar($output, $fileCount);
        $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');

        $output->writeln('');
        $progress->start();

        for ($pass = 0; $pass <= $passCount; ++$pass) {
            $metadataList = $this->migrationHandler->loadMetadataList($bulkCount, $pass * $bulkCount);

            foreach ($metadataList as $metadata) {
                if (!$dryRun) {
                    $this->migrationHandler->migrateFile($metadata);
                }

                $progress->clear();

                $output->write("\r");
                $output->writeln('Updated file ' . $metadata->id);
                $output->write("\r");

                $progress->display();

                $progress->advance();
            }
        }

        $progress->finish();

        $output->writeln('');
    }
}
