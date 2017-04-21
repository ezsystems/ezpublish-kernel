<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformInstallerBundle\Installer;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use PDO;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

class DbBasedInstaller
{
    /** @var \Doctrine\DBAL\Connection */
    protected $db;

    /** @var \Symfony\Component\Console\Output\OutputInterface */
    protected $output;

    /**
     * The map of ['table' => 'field'] for all sequences.
     *
     * @var array
     */
    private $sequencesTables = [];

    public function __construct(Connection $db)
    {
        $this->db = $db;
        $this->output = new NullOutput();
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    /**
     * Copy and override configuration file.
     *
     * @param string $source
     * @param string $target
     */
    protected function copyConfigurationFile($source, $target)
    {
        $fs = new Filesystem();
        $fs->copy($source, $target, true);

        if (!$this->output->isQuiet()) {
            $this->output->writeln("Copied $source to $target");
        }
    }

    /**
     * @param string $file file path
     */
    protected function runQueriesFromFile($file)
    {
        $queries = array_filter(preg_split('(;\\s*$)m', file_get_contents($file)));

        $this->output->writeln(
            sprintf(
                'Executing %d queries from %s on database %s',
                count($queries),
                $file,
                $this->db->getDatabase()
            )
        );

        foreach ($queries as $query) {
            $this->db->exec($query);
        }
    }

    /**
     * Create database schema from Yaml file.
     *
     * @param string $schemaFile Yaml schema description
     */
    protected function importSchemaFromYaml($schemaFile)
    {
        $schemaManager = $this->db->getSchemaManager();
        $schemaFilePath = realpath($schemaFile);
        $schemaDescription = Yaml::parse(file_get_contents($schemaFilePath));
        $schema = new Schema();
        $this->output->writeln("Creating database schema from $schemaFilePath...");
        $progressBar = new ProgressBar($this->output);
        $progressBar->start(count($schemaDescription['tables']));
        foreach ($schemaDescription['tables'] as $tableName => $tableDescription) {
            $table = $this->createTable($schema, $tableName, $tableDescription);
            $schemaManager->dropAndCreateTable($table);
            $progressBar->advance(1);
        }
        $progressBar->finish();
        $this->output->writeln('');
        $this->output->writeln('Created database schema');
    }

    /**
     * Create table based on schema description.
     *
     * @param \Doctrine\DBAL\Schema\Schema $schema
     * @param string $tableName
     * @param array $tableDescription
     * @return \Doctrine\DBAL\Schema\Table
     */
    protected function createTable(Schema $schema, $tableName, array $tableDescription)
    {
        $table = $schema->createTable($tableName);
        $primaryKeyColumns = [];
        foreach ($tableDescription['fields'] as $fieldName => $field) {
            $options = !empty($field['options']) && is_array(
                $field['options']
            ) ? $field['options'] : [];
            $table->addColumn($fieldName, $field['type'], $options);
            // map sequence to table name for further use
            if (!empty($field['options']['autoincrement'])) {
                $sequenceName = $tableName . '_' . $fieldName . '_seq';
                $this->sequencesTables[$sequenceName] = [
                    'table' => $tableName,
                    'column' => $fieldName,
                ];
            }
            if (!empty($field['pk'])) {
                $primaryKeyColumns[] = $fieldName;
            }
        }
        if (!empty($primaryKeyColumns)) {
            $table->setPrimaryKey($primaryKeyColumns);
        }
        if (!empty($tableDescription['indices'])) {
            foreach ($tableDescription['indices'] as $indexName => $index) {
                $options = [];
                if (!empty($index['length'])) {
                    $options['length'] = $index['length'];
                }
                if (!empty($index['unique'])) {
                    $table->addUniqueIndex($index['columns'], $indexName, $options);
                } else {
                    $table->addIndex($index['columns'], $indexName, [], $options);
                }
            }
        }

        return $table;
    }

    /**
     * Align sequences next values after populating tables with initial data.
     *
     * @see https://wiki.postgresql.org/wiki/Fixing_Sequences
     */
    protected function alignSequences()
    {
        $schemaManager = $this->db->getSchemaManager();
        $sequences = $schemaManager->listSequences();
        $platform = $this->db->getDatabasePlatform();
        $this->output->writeln('Restarting sequences...');
        foreach ($sequences as $sequence) {
            $sequenceName = $sequence->getName();
            if (!isset($this->sequencesTables[$sequenceName])) {
                continue;
            }

            $tableColumn = $this->sequencesTables[$sequenceName];
            $queryBuilder = $this->db->createQueryBuilder();
            $stmt = $queryBuilder
                ->select($platform->getMaxExpression($tableColumn['column']))
                ->from($tableColumn['table'], 't')
                ->execute();
            $maxValue = $stmt->fetch(PDO::FETCH_COLUMN);
            if (!empty($maxValue)) {
                $sequence->setInitialValue($maxValue + 1);
                $sql = $schemaManager->getDatabasePlatform()->getAlterSequenceSQL($sequence);
                $this->db->exec($sql);
            }
        }
        $this->output->writeln('Done.');
    }
}
