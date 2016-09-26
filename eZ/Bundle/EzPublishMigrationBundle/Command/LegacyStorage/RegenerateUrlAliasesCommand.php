<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishMigrationBundle\Command\LegacyStorage;

use eZ\Publish\API\Repository\Exceptions\ForbiddenException;
use eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway as UrlAliasGateway;
use eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler as UrlAliasHandler;
use eZ\Publish\Core\Persistence\Legacy\Handler as LegacyStorageEngine;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use RuntimeException;
use Exception;
use PDO;

class RegenerateUrlAliasesCommand extends ContainerAwareCommand
{
    const MIGRATION_TABLE = '__migration_ezurlalias_ml';
    const CUSTOM_ALIAS_BACKUP_TABLE = '__migration_backup_custom_alias';

    /**
     * @var \eZ\Publish\API\Repository\ContentService
     */
    protected $contentService;

    /**
     * @var \eZ\Publish\Core\Repository\Helper\NameSchemaService
     */
    protected $nameSchemaResolver;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\UrlAlias\Handler
     */
    protected $urlAliasHandler;

    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway
     */
    protected $urlAliasGateway;

    /**
     * @var \Doctrine\DBAL\Connection $connection
     */
    protected $connection;

    /**
     * @var int
     */
    protected $bulkCount;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    protected $actions = [
        'full',
        'autogenerate',
        'backup-custom',
        'restore-custom',
    ];

    protected function configure()
    {
        $this
            ->setName('ezpublish:regenerate:legacy_storage_url_aliases')
            ->setDescription('Updates sort keys in configured Legacy Storage database')
            ->addArgument(
                'action',// regenerate
                InputArgument::REQUIRED,
                'Action to perform, one of: full, autogenerate, backup-custom, restore-custom'
            )
            ->addArgument(
                'bulk-count',
                InputArgument::OPTIONAL,
                'Number of items (Locations, URL aliases) processed at once',
                50
            )
            ->setHelp(
                <<<EOT
The command <info>%command.name%</info>

<warning>During the script execution the database should not be modified.

Since this script can potentially run for a very long time, to avoid memory
exhaustion run it in production environment using <info>--env=prod</info> switch.

EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->checkStorage();
        $this->prepareDependencies($output);

        $action = $input->getArgument('action');
        $this->bulkCount = $input->getArgument('bulk-count');

        if (!in_array($action, $this->actions)) {
            throw new RuntimeException("Action '{$action}' is not supported");
        }

        if ($action === 'full' || $action === 'backup-custom')
        {
            $this->backupCustomLocationAliases();
        }

        if ($action === 'full' || $action === 'autogenerate')
        {
            $this->generateLocationAliases();
        }

        if ($action === 'full' || $action === 'restore-custom')
        {
            $this->restoreCustomLocationAliases();
        }
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

    /**
     * Prepares dependencies used by the command.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function prepareDependencies(OutputInterface $output)
    {
        /** @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler $databaseHandler */
        $databaseHandler = $this->getContainer()->get('ezpublish.connection');
        /** @var \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway $gateway */
        $gateway = $this->getContainer()->get('ezpublish.persistence.legacy.url_alias.gateway');
        /** @var \eZ\Publish\SPI\Persistence\Handler $persistenceHandler */
        $persistenceHandler = $this->getContainer()->get('ezpublish.api.persistence_handler');
        /** @var \eZ\Publish\Core\Repository\Repository $innerRepository */
        $innerRepository = $this->getContainer()->get('ezpublish.api.inner_repository');
        /** @var \eZ\Publish\API\Repository\Repository $repository */
        $repository = $this->getContainer()->get('ezpublish.api.repository');

        $administratorUser = $repository->getUserService()->loadUser(14);
        $repository->getPermissionResolver()->setCurrentUserReference($administratorUser);

        $this->contentService = $repository->getContentService();
        $this->nameSchemaResolver = $innerRepository->getNameSchemaService();
        $this->urlAliasHandler = $persistenceHandler->urlAliasHandler();
        $this->urlAliasGateway = $gateway;
        $this->connection = $databaseHandler->getConnection();
        $this->output = $output;
    }

    /**
     * Sets storage gateway to the default table.
     *
     * @see \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway::TABLE
     */
    protected function setDefaultTable()
    {
        $this->urlAliasGateway->setTable(UrlAliasGateway::TABLE);
    }

    /**
     * Sets storage gateway to the migration table.
     *
     * @see \eZ\Bundle\EzPublishMigrationBundle\Command\LegacyStorage\RegenerateUrlAliasesCommand::MIGRATION_TABLE
     */
    protected function setMigrationTable()
    {
        $this->urlAliasGateway->setTable(static::MIGRATION_TABLE);
    }

    /**
     * Backups custom URL aliases the custom URL alias backup table.
     */
    protected function backupCustomLocationAliases()
    {
        if (!$this->tableExists(static::CUSTOM_ALIAS_BACKUP_TABLE)) {
            $this->createCustomAliasBackupTable();
        }

        if (!$this->isTableEmpty(static::CUSTOM_ALIAS_BACKUP_TABLE)) {
            throw new RuntimeException(
                'Table ' . static::CUSTOM_ALIAS_BACKUP_TABLE . ' contains data. ' .
                "Ensure it's empty or non-existent (it will be automatically created)."
            );
        }

        $this->doBackupCustomLocationAliases();
    }

    /**
     * Internal method for backing up custom URL aliases.
     *
     * @see \eZ\Bundle\EzPublishMigrationBundle\Command\LegacyStorage\RegenerateUrlAliasesCommand::backupCustomLocationAliases()
     */
    protected function doBackupCustomLocationAliases()
    {
        $totalCount = $this->getTotalLocationCount();
        $passCount = ceil($totalCount / $this->bulkCount);
        $customAliasCount = 0;
        $customAliasPathCount = 0;

        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select('node_id', 'parent_node_id', 'contentobject_id')
            ->from('ezcontentobject_tree')
            ->where($queryBuilder->expr()->neq('node_id', 1))
            ->orderBy('depth', 'ASC')
            ->orderBy('node_id', 'ASC');

        $this->output->writeln("Backing up custom URL aliases for {$totalCount} Location(s).");

        $progressBar = $this->getProgressBar($totalCount);
        $progressBar->start();

        for ($pass = 0; $pass <= $passCount; ++$pass) {
            $rows = $this->loadLocationData($queryBuilder, $pass);

            foreach ($rows as $row) {
                $customAliases = $this->urlAliasHandler->listURLAliasesForLocation(
                    $row['node_id'],
                    true
                );

                $customAliasCount += count($customAliases);
                $customAliasPathCount += $this->storeCustomAliases($customAliases);

            }

            $progressBar->advance(count($rows));
        }

        $progressBar->finish();

        $this->output->writeln('');
        $this->output->writeln(
            "Done. Backed up {$customAliasCount} custom URL alias(es) " .
            "with {$customAliasPathCount} path(s)."
        );
        $this->output->writeln('');
    }

    /**
     * Restores custom URL aliases from the backup table.
     */
    protected function restoreCustomLocationAliases()
    {
        if (!$this->tableExists(static::MIGRATION_TABLE)) {
            throw new RuntimeException(
                'Could not find custom URL alias backup table ' . static::MIGRATION_TABLE . '. ' .
                'Ensure that table is created by backup process.'
            );
        }

        $this->doRestoreCustomLocationAliases();
    }

    /**
     * Restores custom URL aliases from the backup table.
     *
     * @see \eZ\Bundle\EzPublishMigrationBundle\Command\LegacyStorage\RegenerateUrlAliasesCommand::restoreCustomLocationAliases()
     */
    protected function doRestoreCustomLocationAliases()
    {
        $totalCount = $this->getTotalCustomUrlAliasBackupCount();
        $passCount = ceil($totalCount / $this->bulkCount);
        $createdAliasCount = 0;
        $conflictCount = 0;

        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select('*')
            ->from(static::CUSTOM_ALIAS_BACKUP_TABLE)
            ->orderBy('id', 'ASC');

        $this->output->writeln("Restoring {$totalCount} custom URL alias(es).");

        $progressBar = $this->getProgressBar($totalCount);
        $progressBar->start();

        for ($pass = 0; $pass <= $passCount; ++$pass) {
            $rows = $this->loadCustomUrlAliasData($queryBuilder, $pass);

            foreach ($rows as $row) {
                try {
                    $this->setMigrationTable();
                    $this->urlAliasHandler->createCustomUrlAlias(
                        $row['location_id'],
                        $row['path'],
                        (bool)$row['forwarding'],
                        $row['language_code'],
                        (bool)$row['always_available']
                    );
                    $createdAliasCount += 1;
                    $this->setDefaultTable();
                } catch (ForbiddenException $e) {
                    $conflictCount += 1;
                } catch (Exception $e) {
                    $this->setDefaultTable();
                    throw $e;
                }
            }

            $progressBar->advance(count($rows));
        }

        $progressBar->finish();

        $this->output->writeln('');
        $this->output->writeln(
            "Done. Restored {$createdAliasCount} custom URL alias(es) " .
            "with {$conflictCount} conflict(s)."
        );
        $this->output->writeln('');
    }

    /**
     * Loads Location data for the given $pass.
     *
     * @param \Doctrine\DBAL\Query\QueryBuilder $queryBuilder
     * @param int $pass
     *
     * @return array
     */
    protected function loadCustomUrlAliasData(QueryBuilder $queryBuilder, $pass)
    {
        $queryBuilder->setFirstResult($pass * $this->bulkCount);
        $queryBuilder->setMaxResults($this->bulkCount);

        $statement = $queryBuilder->execute();

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $rows;
    }

    /**
     * Stores given custom $aliases to the custom alias backup table.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\UrlAlias[] $aliases
     *
     * @return int
     */
    protected function storeCustomAliases(array $aliases)
    {
        $pathCount = 0;

        foreach ($aliases as $alias) {
            $paths = $this->combinePaths($alias->pathData);
            $pathCount += count($paths);

            foreach ($paths as $path) {
                $this->storeCustomAliasPath(
                    $alias->destination,
                    $path,
                    reset($alias->languageCodes),
                    $alias->alwaysAvailable,
                    $alias->forward
                );
            }
        }

        return $pathCount;
    }

    /**
     * Stores custom URL alias data for $path to the backup table.
     *
     * @param int $locationId
     * @param string $path
     * @param string $languageCode
     * @param boolean $alwaysAvailable
     * @param boolean $forwarding
     */
    protected function storeCustomAliasPath($locationId, $path, $languageCode, $alwaysAvailable, $forwarding)
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder->insert(static::CUSTOM_ALIAS_BACKUP_TABLE);
        $queryBuilder->values(
            [
                'id' => '?',
                'location_id' => '?',
                'path' => '?',
                'language_code' => '?',
                'always_available' => '?',
                'forwarding' => '?',
            ]
        );
        $queryBuilder->setParameter(0, 0);
        $queryBuilder->setParameter(1, $locationId);
        $queryBuilder->setParameter(2, $path);
        $queryBuilder->setParameter(3, $languageCode);
        $queryBuilder->setParameter(4, (int)$alwaysAvailable);
        $queryBuilder->setParameter(5, (int)$forwarding);

        $queryBuilder->execute();
    }

    /**
     * Combines path data to an array of URL alias paths.
     *
     * Explanation:
     *
     * Custom URL aliases can generate NOP entries, which can be taken over by the autogenerated
     * aliases. When multiple languages exists for the Location that took over, multiple entries
     * with the same link will exist on the same level. In that case it will not be possible to
     * reliably reconstruct what was the path for the original custom alias. For that reason we
     * combine path data to get all possible path combinations.
     *
     * Note: it could happen that original NOP entry was historized after being taken over by the
     * autogenerated alias. So to be complete this would have to take into account history entries
     * as well, but at the moment we lack API to do that.
     *
     * Proper solution of this problem would be introducing separate database table to store custom
     * URL alias data.
     *
     * @see https://jira.ez.no/browse/EZP-20777
     *
     * @param array $pathData
     *
     * @return string[]
     */
    protected function combinePaths(array $pathData)
    {
        $paths = [];
        $levelData = array_shift($pathData);
        $levelElements = $this->extractPathElements($levelData);

        if (!empty($pathData)) {
            $nextElements = $this->combinePaths($pathData);

            foreach ($levelElements as $element1) {
                foreach ($nextElements as $element2) {
                    $paths[] = $element1 . '/' . $element2;
                }
            }

            return $paths;
        }

        return $levelElements;
    }

    /**
     * Returns all path element strings found for the given path $levelData.
     *
     * @param array $levelData
     *
     * @return string[]
     */
    protected function extractPathElements(array $levelData)
    {
        $elements = [];

        if (isset($levelData['translations']['always-available'])) {
            // NOP entry
            $elements[] = $levelData['translations']['always-available'];
        } else {
            // Language(s) entry
            $elements = array_values($levelData['translations']);
        }

        return $elements;
    }

    /**
     * Generates URL aliases from the Location and Content data to the migration table.
     */
    protected function generateLocationAliases()
    {
        $tableName = static::MIGRATION_TABLE;

        if (!$this->tableExists($tableName)) {
            throw new RuntimeException("Table '{$tableName}' does not exist.");
        }

        if (!$this->isTableEmpty($tableName)) {
            throw new RuntimeException("Table '{$tableName}' contains data.");
        }

        $this->doGenerateLocationAliases();
    }

    /**
     * Internal method for generating URL aliases.
     *
     * @see \eZ\Bundle\EzPublishMigrationBundle\Command\LegacyStorage\RegenerateUrlAliasesCommand::generateLocationAliases()
     */
    protected function doGenerateLocationAliases()
    {
        $totalLocationCount = $this->getTotalLocationCount();
        $totalContentCount = $this->getTotalLocationContentCount();
        $passCount = ceil($totalLocationCount / $this->bulkCount);
        $publishedAliasCount = 0;

        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select('node_id', 'parent_node_id', 'contentobject_id')
            ->from('ezcontentobject_tree')
            ->where($queryBuilder->expr()->neq('node_id', 1))
            ->orderBy('depth', 'ASC')
            ->orderBy('node_id', 'ASC');

        $this->output->writeln(
            "Publishing URL aliases for {$totalLocationCount} Location(s) " .
            "with {$totalContentCount} Content item(s) in all languages."
        );

        $progressBar = $this->getProgressBar($totalLocationCount);
        $progressBar->start();

        for ($pass = 0; $pass <= $passCount; ++$pass) {
            $rows = $this->loadLocationData($queryBuilder, $pass);

            foreach ($rows as $row) {
                $publishedAliasCount += $this->publishAliases(
                    $row['node_id'],
                    $row['parent_node_id'],
                    $row['contentobject_id']
                );
            }

            $progressBar->advance(count($rows));
        }

        $progressBar->finish();

        $this->output->writeln('');
        $this->output->writeln("Done. Published {$publishedAliasCount} URL alias(es).");
        $this->output->writeln('');
    }

    /**
     * Publishes URL aliases in all languages for the given parameters.
     *
     * @throws \Exception
     *
     * @param int|string $locationId
     * @param int|string $parentLocationId
     * @param int|string $contentId
     *
     * @return int
     */
    protected function publishAliases($locationId, $parentLocationId, $contentId)
    {
        $content = $this->contentService->loadContent($contentId);

        $urlAliasNames = $this->nameSchemaResolver->resolveUrlAliasSchema($content);

        foreach ($urlAliasNames as $languageCode => $name) {
            try {
                $this->setMigrationTable();
                $this->urlAliasHandler->publishUrlAliasForLocation(
                    $locationId,
                    $parentLocationId,
                    $name,
                    $languageCode,
                    $content->contentInfo->alwaysAvailable
                );
                $this->setDefaultTable();
            } catch (Exception $e) {
                $this->setDefaultTable();
                throw $e;
            }
        }

        return count($urlAliasNames);
    }

    /**
     * Loads Location data for the given $pass.
     *
     * @param \Doctrine\DBAL\Query\QueryBuilder $queryBuilder
     * @param int $pass
     *
     * @return array
     */
    protected function loadLocationData(QueryBuilder $queryBuilder, $pass)
    {
        $queryBuilder->setFirstResult($pass * $this->bulkCount);
        $queryBuilder->setMaxResults($this->bulkCount);

        $statement = $queryBuilder->execute();

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $rows;
    }

    /**
     * Returns total number of Locations in the database.
     *
     * The number excludes absolute root Location, which does not have an URL alias.
     */
    protected function getTotalLocationCount()
    {
        $platform = $this->connection->getDatabasePlatform();

        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select($platform->getCountExpression('node_id'))
            ->from('ezcontentobject_tree')
            ->where(
                $queryBuilder->expr()->neq(
                    'node_id',
                    UrlAliasHandler::ROOT_LOCATION_ID
                )
            );

        return $queryBuilder->execute()->fetchColumn();
    }

    /**
     * Returns total number of Content objects having a Location in the database.
     *
     * The number excludes absolute root Location, which does not have an URL alias.
     */
    protected function getTotalLocationContentCount()
    {
        $platform = $this->connection->getDatabasePlatform();

        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select($platform->getCountExpression('DISTINCT contentobject_id'))
            ->from('ezcontentobject_tree')
            ->where(
                $queryBuilder->expr()->neq(
                    'node_id',
                    UrlAliasHandler::ROOT_LOCATION_ID
                )
            );

        return $queryBuilder->execute()->fetchColumn();
    }

    /**
     * Returns total number of Content objects having a Location in the database.
     *
     * The number excludes absolute root Location, which does not have an URL alias.
     */
    protected function getTotalCustomUrlAliasBackupCount()
    {
        $platform = $this->connection->getDatabasePlatform();

        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select($platform->getCountExpression('id'))
            ->from(static::CUSTOM_ALIAS_BACKUP_TABLE);

        return $queryBuilder->execute()->fetchColumn();
    }

    /**
     * Creates database table for custom URL alias backup.
     */
    protected function createCustomAliasBackupTable()
    {
        $schema = new Schema();

        $table = $schema->createTable(static::CUSTOM_ALIAS_BACKUP_TABLE);

        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('location_id', 'integer');
        $table->addColumn('path', 'text');
        $table->addColumn('language_code', 'string');
        $table->addColumn('always_available', 'integer');
        $table->addColumn('forwarding', 'integer');
        $table->setPrimaryKey(['id']);

        $queries = $schema->toSql($this->connection->getDatabasePlatform());

        foreach ($queries as $query) {
            $this->connection->exec($query);
        }
    }

    /**
     * Checks if database table $name exists.
     *
     * @param string $name
     *
     * @return bool
     */
    protected function tableExists($name)
    {
        return $this->connection->getSchemaManager()->tablesExist([$name]);
    }

    /**
     * Checks if database table $name is empty.
     *
     * @param string $name
     *
     * @return bool
     */
    protected function isTableEmpty($name)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder
            ->select($this->connection->getDatabasePlatform()->getCountExpression('*'))
            ->from($name);

        $count = $queryBuilder->execute()->fetchColumn();

        return $count == 0;
    }

    /**
     * Returns configured progress bar helper.
     *
     * @param int $maxSteps
     *
     * @return \Symfony\Component\Console\Helper\ProgressBar
     */
    protected function getProgressBar($maxSteps)
    {
        $progressBar = new ProgressBar($this->output, $maxSteps);
        $progressBar->setFormat(
            ' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%'
        );

        return $progressBar;
    }
}
