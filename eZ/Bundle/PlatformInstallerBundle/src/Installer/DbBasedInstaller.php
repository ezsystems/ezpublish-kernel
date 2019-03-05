<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformInstallerBundle\Installer;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Schema;
use EzSystems\DoctrineSchema\API\Builder\SchemaBuilder;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Filesystem\Filesystem;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

class DbBasedInstaller
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $db;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * @var string
     */
    protected $baseDataDir;

    public function __construct(Connection $db, SchemaBuilder $schemaBuilder)
    {
        $this->db = $db;
        $this->schemaBuilder = $schemaBuilder;

        // parametrized so other installer implementations can override this
        $this->baseDataDir = __DIR__ . '/../../../../../data';
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    /**
     * Import Schema using event-driven Schema Builder API from eZ Systems DoctrineSchema Bundle.
     *
     * If you wish to extend schema, implement your own EventSubscriber
     *
     * @see \EzSystems\DoctrineSchema\API\Builder\SchemaBuilderEvent
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
                $this->db->getDatabasePlatform()->getName()
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

    protected function runQueriesFromFile($file)
    {
        $queries = array_filter(preg_split('(;\\s*$)m', file_get_contents($file)));

        if (!$this->output->isQuiet()) {
            $this->output->writeln(
                sprintf(
                    'Executing %d queries from %s on database %s',
                    count($queries),
                    $file,
                    $this->db->getDatabase()
                )
            );
        }

        foreach ($queries as $query) {
            $this->db->exec($query);
        }
    }

    /**
     * Get DBMS-specific SQL data file path.
     *
     * @param string $relativeFilePath SQL file path relative to {baseDir}/{dbms} directory
     *
     * @return string absolute existing file path
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @since 6.13
     */
    final protected function getKernelSQLFileForDBMS($relativeFilePath)
    {
        $databasePlatform = $this->db->getDatabasePlatform()->getName();
        $filePath = "{$this->baseDataDir}/{$databasePlatform}/{$relativeFilePath}";

        if (!is_readable($filePath)) {
            throw new InvalidArgumentException(
                '$relativeFilePath',
                sprintf(
                    'DBMS-specific file for %s database platform does not exist or is not readable: %s',
                    $databasePlatform,
                    $filePath
                )
            );
        }

        // apply realpath for more user-friendly Console output
        return realpath($filePath);
    }
}
