<?php

/**
 * File containing the TestInitDbCommand class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Exception;

class TestInitDbCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('ezpublish:test:init_db')
            ->setHidden(true)
            ->setDescription('Inits the configured database for test use based on existing fixtures for eZ Demo install (4.7 atm)')
            ->setHelp(
                <<<EOT
The command <info>%command.name%</info> initializes current configured database with existing fixture data.

WARNING: This command will delete all data in the configured database before filling it with fixture data.
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $database = $this->getContainer()->get('ezpublish.connection')->getConnection()->getParams();
        if (is_array($database)) {
            $driverMap = [
                'pdo_mysql' => 'mysql',
                'pdo_pgsql' => 'pgsql',
                'pdo_sqlite' => 'sqlite',
            ];

            $dbType = $driverMap[$database['driver']];
            $database = $database['dbname'];
        } else {
            $dbType = preg_replace('(^([a-z]+).*)', '\\1', $database);
        }

        if (
            $input->isInteractive() &&
            !$this->getHelperSet()->get('dialog')->askConfirmation(
                $output,
                "<question>Are you sure you want to delete all data in '{$database}' database?</question>",
                false
            )
        ) {
            return;
        }

        $output->writeln('<info>Database is now being emptied and re filled with fixture data.</info>');

        // @TODO Reuse API Integration tests SetupFactory when it has been refactored to be able to use Symfony DIC
        $this->applyStatements($this->getSchemaStatements($dbType));
        $this->insertData($dbType);
    }

    /**
     * Insert the database data.
     *
     * @param string $dbType Name of Database type (mysql, sqlite, pgsql, ..)
     */
    public function insertData($dbType)
    {
        // Get Initial fixture data and union with some tables that must be present but sometimes aren't
        $data = $this->getInitialData() + [
            'ezcontentobject_trash' => [],
            'ezurlwildcard' => [],
            'ezmedia' => [],
            'ezkeyword' => [],
        ];
        $handler = $this->getDatabaseHandler();
        foreach ($data as $table => $rows) {
            // Cleanup before inserting
            $deleteQuery = $handler->createDeleteQuery();
            $deleteQuery->deleteFrom($handler->quoteIdentifier($table));
            $stmt = $deleteQuery->prepare();
            $stmt->execute();

            // Check that at least one row exists
            if (!isset($rows[0])) {
                continue;
            }

            $q = $handler->createInsertQuery();
            $q->insertInto($handler->quoteIdentifier($table));

            // Contains the bound parameters
            $values = [];

            // Binding the parameters
            foreach ($rows[0] as $col => $val) {
                $q->set(
                    $handler->quoteIdentifier($col),
                    $q->bindParam($values[$col])
                );
            }

            $stmt = $q->prepare();

            foreach ($rows as $row) {
                try {
                    // This CANNOT be replaced by:
                    // $values = $row
                    // each $values[$col] is a PHP reference which should be
                    // kept for parameters binding to work
                    foreach ($row as $col => $val) {
                        $values[$col] = $val;
                    }

                    $stmt->execute();
                } catch (Exception $e) {
                    echo "$table ( ", implode(', ', $row), " )\n";
                    throw $e;
                }
            }
        }

        $this->applyStatements($this->getPostInsertStatements($dbType));
    }

    /**
     * Returns statements to be executed after data insert.
     *
     * @param string $dbType Name of Database type (mysql, sqlite, pgsql, ..)
     *
     * @return string[]
     */
    protected function getPostInsertStatements($dbType)
    {
        if ($dbType === 'pgsql') {
            $setvalPath = __DIR__ . '/../../../Publish/Core/Persistence/Legacy/Tests/_fixtures/setval.pgsql.sql';

            return array_filter(preg_split('(;\\s*$)m', file_get_contents($setvalPath)));
        }

        return [];
    }

    /**
     * Returns the initial database data.
     *
     * @return array
     */
    protected function getInitialData()
    {
        return include __DIR__ . '/../../../../data/demo_data.php';
    }

    /**
     * Applies the given SQL $statements to the database in use.
     *
     * @param array $statements
     */
    protected function applyStatements(array $statements)
    {
        $handler = $this->getDatabaseHandler();
        foreach ($statements as $statement) {
            $handler->exec($statement);
        }
    }

    /**
     * Returns the database schema as an array of SQL statements.
     *
     * @param string $dbType Name of Database type (mysql, sqlite, pgsql, ..)
     *
     * @return string[]
     */
    protected function getSchemaStatements($dbType)
    {
        $schemaPath = __DIR__ . "/../../../../data/{$dbType}/schema.sql";

        return array_filter(preg_split('(;\\s*$)m', file_get_contents($schemaPath)));
    }

    /**
     * Returns the database handler from the service container.
     *
     * @return \eZ\Publish\Core\Persistence\Doctrine\ConnectionHandler
     */
    protected function getDatabaseHandler()
    {
        return $this->getContainer()->get('ezpublish.api.storage_engine.legacy.dbhandler');
    }
}
