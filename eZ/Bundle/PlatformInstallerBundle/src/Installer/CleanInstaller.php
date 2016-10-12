<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformInstallerBundle\Installer;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Sequence;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Yaml\Yaml;

class CleanInstaller extends DbBasedInstaller implements Installer
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
        $schemaDescription = Yaml::parse(file_get_contents(realpath(dirname(__DIR__) . '/../../../../data/schema.yml')));
        $supportsSequences = $this->databasePlatformSupportsSequences();
        $schema = new Schema();
        $this->output->writeln('Creating database schema...');
        $progressBar = new ProgressBar($this->output);
        $progressBar->start(count($schemaDescription['tables']));
        // store sequences details to reset them after inserting data
        $this->sequencesTables = [];
        foreach ($schemaDescription['tables'] as $tableName => $tableDescription) {
            $table = $schema->createTable($tableName);
            foreach ($tableDescription['fields'] as $fieldName => $field) {
                if ($supportsSequences && !empty($field['options']['autoincrement'])) {
                    // due to BC sequences have to be created manually
                    $sequenceName = $tableName . '_s';
                    $field['options']['default'] = "NEXTVAL('$sequenceName')";
                    $sequence = $schema->createSequence($sequenceName);
                    $schemaManager->dropAndCreateSequence($sequence);
                    $this->sequencesTables[$sequenceName] = ['table' => $tableName, 'column' => $fieldName];
                    // avoid creating SERIAL type
                    unset($field['options']['autoincrement']);
                }
                $table->addColumn($fieldName, $field['type'], !empty($field['options']) && is_array($field['options']) ? $field['options'] : []);
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
            // Added only if Database Platform supports it
            $table->addOption('engine', 'InnoDB');
            $table->addOption('DEFAULT CHARSET', 'UTF8');
            $schemaManager->dropAndCreateTable($table);
            $progressBar->advance(1);
        }
        $progressBar->finish();
        $this->output->writeln('');
        $this->runCustomSchemaScript();
        $this->output->writeln('Created database schema');
    }

    public function importData()
    {
        $this->runQueriesFromFile(
            realpath(dirname(__DIR__) . sprintf('/../../../../data/%s/cleandata.sql', $this->db->getDatabasePlatform()->getName()))
        );
        if (!empty($this->sequencesTables)) {
            $this->fixSequences();
        }
    }

    public function importBinaries()
    {
    }

    /**
     * @return bool TRUE if Db Platform supports SEQUENCE
     */
    protected function databasePlatformSupportsSequences()
    {
        return $this->db->getDatabasePlatform() instanceof PostgreSqlPlatform;
    }

    protected function runCustomSchemaScript()
    {
        $fileName = realpath(dirname(__DIR__) . sprintf('/../../../../data/%s/custom.sql', $this->db->getDatabasePlatform()->getName()));
        if (!empty($fileName)) {
            $this->runQueriesFromFile($fileName);
        }
    }

    /**
     * Returns the SQL to change a sequence.
     *
     * @param AbstractPlatform $platform
     * @param \Doctrine\DBAL\Schema\Sequence $sequence
     * @return string
     */
    protected function getAlterSequenceSQL(AbstractPlatform $platform, Sequence $sequence)
    {
        return 'ALTER SEQUENCE ' . $sequence->getQuotedName($platform) .
        ' INCREMENT BY ' . $sequence->getAllocationSize() .
        ' RESTART WITH ' . $sequence->getInitialValue();
    }

    /**
     * Fix sequences after populating tables with initial data.
     *
     * @see https://wiki.postgresql.org/wiki/Fixing_Sequences
     */
    private function fixSequences()
    {
        $schemaManager = $this->db->getSchemaManager();
        $sequences = $schemaManager->listSequences();
        $platform = $this->db->getDatabasePlatform();
        $this->output->writeln('Fixing sequences...');
        foreach ($sequences as $sequence) {
            $sequenceTable = $this->sequencesTables[$sequence->getName()];
            $queryBuilder = $this->db->createQueryBuilder();
            $stmt = $queryBuilder
                ->select($platform->getMaxExpression($sequenceTable['column']))
                ->from($sequenceTable['table'])->execute();

            $initialValue = $stmt->fetch(\PDO::FETCH_COLUMN);
            if (!empty($initialValue)) {
                $sequence->setInitialValue($initialValue + 1);
                $this->db->exec($this->getAlterSequenceSQL($platform, $sequence));
            }
        }
        $this->output->writeln('Done.');
    }
}
