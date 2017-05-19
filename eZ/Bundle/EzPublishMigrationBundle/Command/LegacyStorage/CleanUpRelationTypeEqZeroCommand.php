<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishMigrationBundle\Command\LegacyStorage;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use eZ\Publish\Core\Persistence\Legacy\Handler as LegacyStorageEngine;
use RuntimeException;

class CleanUpRelationTypeEqZeroCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('ezpublish:update:legacy_storage_clean_up_relation_type_eq_zero')
            ->setDescription('Clean up invalid relation type in Legacy Storage database')
            ->addArgument('action', InputArgument::REQUIRED, 'Action: ' . implode(', ', $this->getAvailableActions()))
            ->setHelp(
                <<<EOT
The command <info>%command.name%</info> cleans up corrupted relations in Legacy Storage database. See: https://jira.ez.no/browse/EZP-27254

<warning>During the script execution the database should not be modified.

To avoid surprises you are advised to create a backup or execute a dry run:
 
    %command.name% dry-run
    
before proceeding with actual update.</warning>

Since this script can potentially run for a very long time, to avoid memory
exhaustion run it in production environment using the <info>--env=prod</info> switch.
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $action = $input->getArgument('action');

        if (!$this->isValidAction($action)) {
            throw new RuntimeException(
                "Action '{$action}' is not supported, use one of: " .
                implode(', ', $this->getAvailableActions())
            );
        }

        $this->checkStorage();

        $totalCount = $this->getTotalCount();
        $output->writeln('Found total relations to delete: ' . $totalCount);
        if ($totalCount == 0) {
            $output->writeln('Nothing to process, exiting.');

            return;
        }

        if ($this->confirmExecution($input, $output)) {
            switch ($action) {
                case 'fix':
                    $this->executeFix();
                    break;
                case 'list':
                case 'dry-run':
                    $this->executeList($output, $totalCount);
                    break;
            }
        }

        $output->writeln('');
    }

    /**
     * Checks that configured storage engine is Legacy Storage Engine.
     */
    protected function checkStorage()
    {
        $storageEngine = $this->getContainer()->get('ezpublish.api.storage_engine');

        if (!$storageEngine instanceof LegacyStorageEngine) {
            throw new RuntimeException(
                'Expected to find Legacy Storage Engine but found something else.'
            );
        }
    }

    protected function confirmExecution(InputInterface $input, OutputInterface $output)
    {
        $question = new ConfirmationQuestion('<question>Are you sure you want to proceed?</question> ', false);

        return $this
            ->getHelper('question')
            ->ask($input, $output, $question);
    }

    protected function executeFix()
    {
        /** @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler $conn */
        $conn = $this->getContainer()->get('ezpublish.connection');
        $conn->exec('DELETE FROM ezcontentobject_link WHERE relation_type = 0');
    }

    protected function executeList(OutputInterface $output, $totalCount)
    {
        $table = new Table($output);
        $table->setHeaders([
            'ID',
            'FROM_CONTENTOBEJCT_ID',
            'FROM_CONTENTOBJECT_VERSION',
            'TO_CONTENTOBJECT_ID',
            'RELATION_TYPE',
        ]);
        $table->addRows($this->getCorruptedRelations());
        $table->render();
    }

    protected function getCorruptedRelations()
    {
        $sql = 'SELECT id, from_contentobject_id, from_contentobject_version, to_contentobject_id,'
                    . 'relation_type FROM ezcontentobject_link WHERE relation_type = 0';

        /** @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler $conn */
        $conn = $this->getContainer()->get('ezpublish.connection');

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_NUM);
    }

    protected function getTotalCount()
    {
        /** @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler $conn */
        $conn = $this->getContainer()->get('ezpublish.connection');

        $stmt = $conn->prepare('SELECT COUNT(id) FROM ezcontentobject_link WHERE relation_type = 0');
        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    protected function getAvailableActions()
    {
        return ['fix', 'list', 'dry-run'];
    }

    protected function isValidAction($action)
    {
        return in_array($action, $this->getAvailableActions());
    }
}
