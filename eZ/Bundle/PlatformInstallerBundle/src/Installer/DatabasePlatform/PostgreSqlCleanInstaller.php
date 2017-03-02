<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformInstallerBundle\Installer\DatabasePlatform;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Sequence;
use EzSystems\PlatformInstallerBundle\Installer\DbBasedInstaller;
use EzSystems\PlatformInstallerBundle\Installer\Installer;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Yaml\Yaml;

class PostgreSqlCleanInstaller extends DbBasedInstaller implements Installer
{
    /**
     * The map of ['table' => 'field'] for all sequences.
     *
     * @var array
     */
    private $sequencesTables = [];

    public function createConfiguration()
    {
    }

    public function importSchema()
    {
        $schemaManager = $this->db->getSchemaManager();
        $schemaFilePath = dirname(__DIR__) . '/../../../../../data/schema.yml';
        $schemaDescription = Yaml::parse(file_get_contents($schemaFilePath));
        $schema = new Schema();
        $this->output->writeln('Creating database schema...');
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

    public function importData()
    {
        $this->runQueriesFromFile(
            realpath(dirname(__DIR__) . '/../../../../../data/postgresql/cleandata.sql')
        );
        $this->alignSequences();
    }

    public function importBinaries()
    {
    }

    /**
     * Create table based on schema description.
     *
     * @param \Doctrine\DBAL\Schema\Schema $schema
     * @param string $tableName
     * @param array $tableDescription
     * @return \Doctrine\DBAL\Schema\Table
     */
    private function createTable(Schema $schema, $tableName, array $tableDescription)
    {
        $table = $schema->createTable($tableName);
        foreach ($tableDescription['fields'] as $fieldName => $field) {
            $options = !empty($field['options']) && is_array($field['options']) ? $field['options'] : [];
            $table->addColumn($fieldName, $field['type'], $options);
            // map sequence to table name for further use
            if (!empty($field['options']['autoincrement'])) {
                $sequenceName = $tableName . '_' . $fieldName . '_seq';
                $this->sequencesTables[$sequenceName] = [
                    'table' => $tableName,
                    'column' => $fieldName,
                ];
            }
        }
        if (!empty($tableDescription['pk'])) {
            $table->setPrimaryKey($tableDescription['pk']);
        }
        if (!empty($tableDescription['indices'])) {
            foreach ($tableDescription['indices'] as $indexName => $index) {
                if (!empty($index['unique'])) {
                    $table->addUniqueIndex($index['columns'], $indexName);
                } else {
                    $table->addIndex($index['columns'], $indexName);
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
    private function alignSequences()
    {
        $schemaManager = $this->db->getSchemaManager();
        $sequences = $schemaManager->listSequences();
        $platform = $this->db->getDatabasePlatform();
        $this->output->writeln('Restarting sequences...');
        foreach ($sequences as $sequence) {
            $sequenceTable = $this->sequencesTables[$sequence->getName()];
            $queryBuilder = $this->db->createQueryBuilder();
            $stmt = $queryBuilder
                ->select($platform->getMaxExpression($sequenceTable['column']))
                ->from($sequenceTable['table'])->execute();
            $initialValue = $stmt->fetch(\PDO::FETCH_COLUMN);
            if (!empty($initialValue)) {
                $sequence->setInitialValue($initialValue + 1);
                $sql = $this->getAlterSequenceSQL($schemaManager->getDatabasePlatform(), $sequence);
                $this->db->exec($sql);
            }
        }
        $this->output->writeln('Done.');
    }

    /**
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
     * @param \Doctrine\DBAL\Schema\Sequence $sequence
     * @return string
     */
    private function getAlterSequenceSQL(AbstractPlatform $platform, Sequence $sequence)
    {
        return sprintf(
            'ALTER SEQUENCE %s INCREMENT BY %d RESTART WITH %d',
            $sequence->getQuotedName($platform),
            $sequence->getAllocationSize(),
            $sequence->getInitialValue()
        );
    }
}
