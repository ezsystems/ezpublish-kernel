<?php

/**
 * File containing the UpdateSortKeysCommand class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishMigrationBundle\Command\LegacyStorage;

use eZ\Publish\Core\Persistence\Database\SelectQuery;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use PDO;

class UpdateSortKeysCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('ezpublish:update:legacy_storage_update_sort_keys')
            ->setDescription('Updates sort keys in configured Legacy Storage database')
            ->addArgument('fieldtype_identifier', InputArgument::REQUIRED, 'Field type identifier')
            ->addArgument('bulk_count', InputArgument::OPTIONAL, 'Number of Content versions processed at once', 100)
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Execute a dry run')
            ->setHelp(
                <<<EOT
The command <info>%command.name%</info> updates field
sort keys in configured Legacy Storage database for a given field type.

Fields will be processed per Content version and updated only if newly generated
sort key does not match stored one. In this case all field data will be updated.

<warning>During the script execution the database should not be modified.

To avoid surprises (particularly if using custom field types) you are advised to
create a backup or execute a dry run before proceeding with actual update.</warning>

Since this script can potentially run for a very long time, to avoid memory
exhaustion run it in production environment using <info>--env=prod</info> switch.

EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fieldTypeIdentifier = $input->getArgument('fieldtype_identifier');
        $bulkCount = $input->getArgument('bulk_count');
        $dryRun = $input->getOption('dry-run');

        /** @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler $databaseHandler */
        $databaseHandler = $this->getContainer()->get('ezpublish.connection');

        /** @var \eZ\Publish\API\Repository\Repository $repository */
        $repository = $this->getContainer()->get('ezpublish.api.repository');
        $repository->setCurrentUser(
            $repository->getUserService()->loadUser(14)
        );

        if (!$this->getContainer()->has("ezpublish.fieldtype.{$fieldTypeIdentifier}")) {
            $output->writeln("<error>Field type '{$fieldTypeIdentifier}' was not found.</error> ");

            return;
        }

        $warningStyle = new OutputFormatterStyle('red');
        $output->getFormatter()->setStyle('warning', $warningStyle);
        $output->writeln($this->getProcessedHelp());

        $query = $databaseHandler->createSelectQuery();
        $query
            ->select(
                $query->expr->count(
                    'DISTINCT ' . $query->expr->concat(
                        $databaseHandler->quoteColumn('contentobject_id'),
                        $query->bindValue('-', null, PDO::PARAM_STR),
                        $databaseHandler->quoteColumn('version')
                    )
                )
            )
            ->from('ezcontentobject_attribute')
            ->where(
                $query->expr->eq(
                    $databaseHandler->quoteColumn('data_type_string'),
                    $query->bindValue($fieldTypeIdentifier)
                )
            );

        $stmt = $query->prepare();
        $stmt->execute();
        $totalCount = $stmt->fetchColumn();

        $output->writeln(
            [
                'Found total Content versions to update: ' . $totalCount,
                '',
            ]
        );

        if ($totalCount == 0) {
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

        $query = $databaseHandler->createSelectQuery();
        $query
            ->selectDistinct('contentobject_id, version')
            ->from('ezcontentobject_attribute')
            ->where(
                $query->expr->eq(
                    $databaseHandler->quoteColumn('data_type_string'),
                    $query->bindValue($fieldTypeIdentifier)
                )
            );

        $passCount = ceil($totalCount / $bulkCount);

        $progress = new ProgressBar($output, $totalCount);
        $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');

        $output->writeln('');
        $progress->start();

        for ($pass = 0; $pass <= $passCount; ++$pass) {
            $rows = $this->loadData($query, $bulkCount, $pass);

            foreach ($rows as $row) {
                $this->updateField(
                    $output,
                    $progress,
                    $row['contentobject_id'],
                    $row['version'],
                    $fieldTypeIdentifier,
                    $dryRun
                );

                $progress->advance();
            }
        }

        $progress->finish();

        $output->writeln('');
    }

    /**
     * Loads field data for given $pass.
     *
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param int $bulkCount
     * @param int $pass
     *
     * @return array
     */
    protected function loadData(SelectQuery $query, $bulkCount, $pass)
    {
        $query->limit($bulkCount, $pass * $bulkCount);

        $stmt = $query->prepare();
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * For given $contentId in $versionNo updates fields of $fieldTypeIdentifier type.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfony\Component\Console\Helper\ProgressBar $progress
     * @param int|string $contentId
     * @param int $versionNo
     * @param string $fieldTypeIdentifier
     * @param bool $dryRun
     */
    protected function updateField(
        OutputInterface $output,
        ProgressBar $progress,
        $contentId,
        $versionNo,
        $fieldTypeIdentifier,
        $dryRun
    ) {
        $container = $this->getContainer();

        /** @var \eZ\Publish\SPI\FieldType\FieldType $fieldType */
        /* @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter $converter */
        /* @var \eZ\Publish\Core\Persistence\Legacy\Content\Gateway $gateway */
        /* @var \eZ\Publish\SPI\Persistence\Content\Handler $contentHandler */
        /* @var \eZ\Publish\API\Repository\ContentService $contentService */
        /* @var \eZ\Publish\API\Repository\ContentTypeService $contentTypeService */
        $fieldType = $container->get("ezpublish.fieldType.{$fieldTypeIdentifier}");
        $converter = $container->get("ezpublish.fieldType.{$fieldTypeIdentifier}.converter");
        $gateway = $container->get('ezpublish.persistence.legacy.content.gateway');
        $contentHandler = $container->get('ezpublish.spi.persistence.legacy.content.handler');
        $contentService = $container->get('ezpublish.api.service.content');
        $contentTypeService = $container->get('ezpublish.api.service.content_type');

        $content = $contentService->loadContent($contentId, null, $versionNo);
        $spiContent = $contentHandler->load($contentId, $versionNo);
        $contentType = $contentTypeService->loadContentType($content->contentInfo->contentTypeId);

        foreach ($content->getFields() as $field) {
            $fieldDefinition = $contentType->getFieldDefinition($field->fieldDefIdentifier);

            // Match API field by field type
            if ($fieldDefinition->fieldTypeIdentifier != $fieldTypeIdentifier) {
                continue;
            }

            foreach ($spiContent->fields as $spiField) {
                // Match SPI field with API field by ID
                if ($spiField->id != $field->id) {
                    continue;
                }

                // Cast API field value to persistence value
                $persistenceValue = $fieldType->toPersistenceValue($field->value);

                if ($persistenceValue->sortKey == $spiField->value->sortKey) {
                    // Assume stored value is correct
                    break;
                }

                if (!$dryRun) {
                    // Create and fill Legacy Storage field value
                    $storageValue = new StorageFieldValue();
                    $converter->toStorageValue($persistenceValue, $storageValue);

                    $gateway->updateField($spiField, $storageValue);
                }

                $progress->clear();

                $output->write("\r");
                $output->writeln(
                    'Updated Content ' . $content->id . ', version ' . $content->versionInfo->versionNo .
                    ', field ' . $spiField->id . ": '" . $spiField->value->sortKey . "' => '" .
                    $persistenceValue->sortKey . "'"
                );
                $output->write("\r");

                $progress->display();

                // Continue outer loop
                break;
            }
        }
    }
}
