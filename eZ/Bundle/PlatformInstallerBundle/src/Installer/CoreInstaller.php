<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\PlatformInstallerBundle\Installer;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Schema;
use EzSystems\DoctrineSchema\API\Builder\SchemaBuilder;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * New variant of CleanInstaller, which uses SchemaBuilder.
 */
class CoreInstaller extends DbBasedInstaller implements Installer
{
    /** @var \EzSystems\DoctrineSchema\API\Builder\SchemaBuilder */
    protected $schemaBuilder;

    /**
     * @param \Doctrine\DBAL\Connection $db
     * @param \EzSystems\DoctrineSchema\API\Builder\SchemaBuilder $schemaBuilder
     */
    public function __construct(Connection $db, SchemaBuilder $schemaBuilder)
    {
        parent::__construct($db);

        $this->schemaBuilder = $schemaBuilder;
    }

    /**
     * Import Schema using event-driven Schema Builder API from eZ Systems DoctrineSchema Bundle.
     *
     * If you wish to extend schema, implement your own EventSubscriber
     *
     * @see \EzSystems\DoctrineSchema\API\Event\SchemaBuilderEvent
     * @see \EzSystems\PlatformInstallerBundle\Event\Subscriber\BuildSchemaSubscriber
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function importSchema()
    {
        // note: schema is built using Schema Builder event-driven API
        $schema = $this->schemaBuilder->buildSchema();
        $databasePlatform = $this->db->getDatabasePlatform();
        $queries = array_merge(
            $this->getDropSqlStatementsForExistingSchema($schema, $databasePlatform),
            // generate schema DDL queries
            $schema->toSql($databasePlatform)
        );

        $queriesCount = count($queries);
        $this->output->writeln(
            sprintf(
                '<info>Executing %d queries on database <comment>%s</comment> (<comment>%s</comment>)</info>',
                $queriesCount,
                $this->db->getDatabase(),
                $databasePlatform->getName()
            )
        );
        $progressBar = new ProgressBar($this->output);
        $progressBar->start($queriesCount);

        try {
            $this->db->beginTransaction();
            foreach ($queries as $query) {
                $this->db->exec($query);
                $progressBar->advance(1);
            }
            $this->db->commit();
        } catch (DBALException $e) {
            $this->db->rollBack();
            throw $e;
        }

        $progressBar->finish();
        // go to the next line after ProgressBar::finish
        $this->output->writeln('');
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function importData()
    {
        $this->runQueriesFromFile($this->getKernelSQLFileForDBMS('cleandata.sql'));
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $newSchema
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $databasePlatform
     *
     * @return string[]
     */
    protected function getDropSqlStatementsForExistingSchema(
        Schema $newSchema,
        AbstractPlatform $databasePlatform
    ): array {
        $existingSchema = $this->db->getSchemaManager()->createSchema();
        $statements = [];
        // reverse table order for clean-up (due to FKs)
        $tables = array_reverse($newSchema->getTables());
        // cleanup pre-existing database
        foreach ($tables as $table) {
            if ($existingSchema->hasTable($table->getName())) {
                $statements[] = $databasePlatform->getDropTableSQL($table);
            }
        }

        return $statements;
    }

    /**
     * Handle optional import of binary files to var folder.
     */
    public function importBinaries()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function createConfiguration()
    {
    }
}
