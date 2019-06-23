<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishMigrationBundle\Command\LegacyStorage;

use eZ\Publish\Core\Persistence\Database\SelectQuery;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use PDO;

class UpdateFieldAlwaysAvailableFlagCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('ezpublish:update:legacy_storage_fix_fields_always_available_flag')
            ->setDescription('Fixes always available flag on Content fields')
            ->addArgument(
                'bulk_count',
                InputArgument::OPTIONAL,
                'Number of Content processed at once',
                100
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Execute a dry run'
            )
            ->setHelp(
                <<<EOT
The command <info>%command.name%</info> fixes always available flag on Content
fields in Legacy Storage Engine database.

This will process only fields of the current version of the always available Content
where found that fields in multiple languages have always available flag set.

Provided the patch that fixes the cause of the problem has been applied, this
command can be executed on a live database.

<warning>To avoid surprises you are advised to create a backup or execute a dry
run before proceeding with the actual update.</warning>

Since this command can potentially run for a very long time, to avoid memory
exhaustion run it in production environment using <info>--env=prod</info> switch.

EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $bulkCount = $input->getArgument('bulk_count');
        $dryRun = $input->getOption('dry-run');

        /** @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler $databaseHandler */
        $databaseHandler = $this->getContainer()->get('ezpublish.connection');

        $warningStyle = new OutputFormatterStyle('red');
        $output->getFormatter()->setStyle('warning', $warningStyle);
        $output->writeln($this->getProcessedHelp());

        $query = $databaseHandler->createSelectQuery();
        $subQuery = $query->subSelect();
        $this->initSelectQuery($subQuery);
        $query
            ->select($query->expr->count('*'))
            ->from($query->alias($subQuery, 't1'));
        $stmt = $query->prepare();
        $stmt->execute();
        $totalCount = $stmt->fetchColumn();

        $output->writeln(
            [
                'Found total of Content objects for update: ' . $totalCount,
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
        $this->initSelectQuery($query);

        $passCount = ceil($totalCount / $bulkCount);

        $progress = new ProgressBar($output, $totalCount);
        $progress->setFormat(
            ' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%'
        );

        $output->writeln('');
        $progress->start();

        for ($pass = 0; $pass <= $passCount; ++$pass) {
            $rows = $this->loadData($query, $bulkCount, $pass);

            foreach ($rows as $row) {
                $this->updateAlwaysAvailableFlag(
                    $output,
                    $progress,
                    $row['id'],
                    $row['current_version'],
                    $row['initial_language_id'],
                    $dryRun
                );

                $progress->advance();
            }
        }

        $progress->finish();

        $output->writeln('');
    }

    /**
     * Loads data for the given $pass.
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
     * Initializes main selection $query.
     *
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     */
    protected function initSelectQuery(SelectQuery $query)
    {
        /** @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler $databaseHandler */
        $databaseHandler = $this->getContainer()->get('ezpublish.connection');

        $query
            ->select(
                $databaseHandler->quoteColumn('id', 'ezcontentobject'),
                $databaseHandler->quoteColumn('current_version', 'ezcontentobject'),
                $databaseHandler->quoteColumn('initial_language_id', 'ezcontentobject'),
                $query->expr->count('distinct ezcontentobject_attribute.language_code')
            )
            ->from('ezcontentobject')
            ->innerJoin(
                $databaseHandler->quoteTable('ezcontentobject_attribute'),
                $query->expr->lAnd(
                    $query->expr->eq(
                        $databaseHandler->quoteColumn('id', 'ezcontentobject'),
                        $databaseHandler->quoteColumn(
                            'contentobject_id',
                            'ezcontentobject_attribute'
                        )
                    ),
                    $query->expr->eq(
                        $databaseHandler->quoteColumn('current_version', 'ezcontentobject'),
                        $databaseHandler->quoteColumn('version', 'ezcontentobject_attribute')
                    ),
                    // Join only with fields marked as always available
                    $query->expr->gt(
                        $query->expr->bitAnd(
                            $databaseHandler->quoteColumn(
                                'language_id',
                                'ezcontentobject_attribute'
                            ),
                            $query->bindValue(1, null, PDO::PARAM_INT)
                        ),
                        $query->bindValue(0, null, PDO::PARAM_INT)
                    )
                )
            )
            ->where(
                $query->expr->lAnd(
                    // Content is always available
                    $query->expr->gt(
                        $query->expr->bitAnd(
                            $databaseHandler->quoteColumn('language_mask', 'ezcontentobject'),
                            $query->bindValue(1, null, PDO::PARAM_INT)
                        ),
                        $query->bindValue(0, null, PDO::PARAM_INT)
                    ),
                    // Content exists in more than one language
                    $query->expr->neq(
                        $query->expr->bitAnd(
                            $query->expr->bitAnd(
                                $databaseHandler->quoteColumn('language_mask', 'ezcontentobject'),
                                $query->bindValue(-2, null, PDO::PARAM_INT)
                            ),
                            $query->expr->sub(
                                $query->expr->bitAnd(
                                    $databaseHandler->quoteColumn(
                                        'language_mask',
                                        'ezcontentobject'
                                    ),
                                    $query->bindValue(-2, null, PDO::PARAM_INT)
                                ),
                                $query->bindValue(1, null, PDO::PARAM_INT)
                            )
                        ),
                        $query->bindValue(0, null, PDO::PARAM_INT)
                    )
                )
            )
            ->groupBy($databaseHandler->quoteColumn('id', 'ezcontentobject'))
            // Remove Content with fields marked as always available in only one language
            ->having(
                $query->expr->gt(
                    $query->expr->count('distinct ezcontentobject_attribute.language_code'),
                    $query->bindValue(1, null, PDO::PARAM_INT)
                )
            );
    }

    /**
     * Fixes always available flag on fields of Content $contentId in $versionNo.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Symfony\Component\Console\Helper\ProgressBar $progress
     * @param int $contentId
     * @param int $versionNo
     * @param int $mainLanguageId
     * @param bool $dryRun
     */
    protected function updateAlwaysAvailableFlag(
        OutputInterface $output,
        ProgressBar $progress,
        $contentId,
        $versionNo,
        $mainLanguageId,
        $dryRun
    ) {
        if ($dryRun) {
            goto output;
        }

        /** @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler $databaseHandler */
        $databaseHandler = $this->getContainer()->get('ezpublish.connection');

        // Remove always available flag from all fields not in main language
        /** @var $query \eZ\Publish\Core\Persistence\Database\UpdateQuery */
        $query = $databaseHandler->createUpdateQuery();
        $query
            ->update($databaseHandler->quoteTable('ezcontentobject_attribute'))
            ->set(
                $databaseHandler->quoteColumn('language_id'),
                $query->expr->bitAnd($databaseHandler->quoteColumn('language_id'), -2)
            )
            ->where(
                $query->expr->lAnd(
                    $query->expr->eq(
                        $databaseHandler->quoteColumn('contentobject_id'),
                        $query->bindValue($contentId, null, PDO::PARAM_INT)
                    ),
                    $query->expr->eq(
                        $databaseHandler->quoteColumn('version'),
                        $query->bindValue($versionNo, null, PDO::PARAM_INT)
                    ),
                    $query->expr->eq(
                        $query->expr->bitAnd(
                            $databaseHandler->quoteColumn('language_id'),
                            $query->bindValue($mainLanguageId, null, PDO::PARAM_INT)
                        ),
                        $query->bindValue(0, null, PDO::PARAM_INT)
                    )
                )
            )
            ->prepare()
            ->execute();

        // Set always available flag on fields in main language
        $query = $databaseHandler->createUpdateQuery();
        $query
            ->update($databaseHandler->quoteTable('ezcontentobject_attribute'))
            ->set(
                $databaseHandler->quoteColumn('language_id'),
                $query->expr->bitOr($databaseHandler->quoteColumn('language_id'), 1)
            )
            ->where(
                $query->expr->lAnd(
                    $query->expr->eq(
                        $databaseHandler->quoteColumn('contentobject_id'),
                        $query->bindValue($contentId, null, PDO::PARAM_INT)
                    ),
                    $query->expr->eq(
                        $databaseHandler->quoteColumn('version'),
                        $query->bindValue($versionNo, null, PDO::PARAM_INT)
                    ),
                    $query->expr->gt(
                        $query->expr->bitAnd(
                            $databaseHandler->quoteColumn('language_id'),
                            $query->bindValue($mainLanguageId, null, PDO::PARAM_INT)
                        ),
                        $query->bindValue(0, null, PDO::PARAM_INT)
                    )
                )
            )
            ->prepare()
            ->execute();

        output:

        $progress->clear();

        $output->write("\r");
        $output->writeln(
            "Updated fields for Content '{$contentId}' in version '{$versionNo}'"
        );
        $output->write("\r");

        $progress->display();
    }
}
