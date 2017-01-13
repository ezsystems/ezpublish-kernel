<?php

/**
 * File containing the Test Setup Factory base class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests\SetupFactory;

use eZ\Publish\Core\Base\ServiceContainer;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use eZ\Publish\API\Repository\Tests\SetupFactory;
use eZ\Publish\API\Repository\Tests\IdManager;
use eZ\Publish\Core\Persistence\Legacy\Content\Type\MemoryCachingHandler as CachingContentTypeHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler as CachingLanguageHandler;
use Exception;
use eZ\Publish\Core\Repository\Values\User\UserReference;
use Symfony\Component\Filesystem\Filesystem;
use eZ\Publish\Core\Base\Container\Compiler;

/**
 * A Test Factory is used to setup the infrastructure for a tests, based on a
 * specific repository implementation to test.
 */
class Legacy extends SetupFactory
{
    /**
     * Data source name.
     *
     * @var string
     */
    protected static $dsn;

    /**
     * Root dir for IO operations.
     *
     * @var string
     */
    protected static $ioRootDir;

    /**
     * Database type (sqlite, mysql, ...).
     *
     * @var string
     */
    protected static $db;

    /**
     * Service container.
     *
     * @var \eZ\Publish\Core\Base\ServiceContainer
     */
    protected static $serviceContainer;

    /**
     * If the DB schema has already been initialized.
     *
     * @var bool
     */
    protected static $schemaInitialized = false;

    /**
     * Initial database data.
     *
     * @var array
     */
    protected static $initialData;

    protected $repositoryReference = 'ezpublish.api.repository';

    /**
     * Creates a new setup factory.
     */
    public function __construct()
    {
        self::$dsn = getenv('DATABASE');
        if (!self::$dsn) {
            self::$dsn = 'sqlite://:memory:';
        }

        self::$db = preg_replace('(^([a-z]+).*)', '\\1', self::$dsn);

        if (!isset(self::$ioRootDir)) {
            self::$ioRootDir = $this->createTemporaryDirectory();
        }
    }

    /**
     * Creates a temporary directory and returns it.
     *
     * @return string
     * @throw \RuntimeException If the root directory can't be created
     */
    private function createTemporaryDirectory()
    {
        $tmpFile = tempnam(
            sys_get_temp_dir(),
            'ez_legacy_tests_' . time()
        );
        unlink($tmpFile);

        $fs = new Filesystem();
        $fs->mkdir($tmpFile);

        $varDir = $tmpFile . '/var';
        if ($fs->exists($varDir)) {
            $fs->remove($varDir);
        }
        $fs->mkdir($varDir);

        return $tmpFile;
    }

    /**
     * Returns a configured repository for testing.
     *
     * @param bool $initializeFromScratch if the back end should be initialized
     *                                    from scratch or re-used
     *
     * @return \eZ\Publish\API\Repository\Repository
     */
    public function getRepository($initializeFromScratch = true)
    {
        if ($initializeFromScratch || !self::$schemaInitialized) {
            $this->initializeSchema();
            $this->insertData();
        }

        $this->clearInternalCaches();
        $repository = $this->getServiceContainer()->get($this->repositoryReference);

        // Set admin user as current user by default
        $repository->setCurrentUser(new UserReference(14));

        return $repository;
    }

    /**
     * Returns a config value for $configKey.
     *
     * @param string $configKey
     *
     * @throws Exception if $configKey could not be found.
     *
     * @return mixed
     */
    public function getConfigValue($configKey)
    {
        return $this->getServiceContainer()->getParameter($configKey);
    }

    /**
     * Returns a repository specific ID manager.
     *
     * @return \eZ\Publish\API\Repository\Tests\IdManager
     */
    public function getIdManager()
    {
        return new IdManager\Php();
    }

    /**
     * Insert the database data.
     */
    public function insertData()
    {
        $data = $this->getInitialData();
        $handler = $this->getDatabaseHandler();
        $connection = $handler->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();
        $this->cleanupVarDir($this->getInitialVarDir());

        // @todo FIXME: Needs to be in fixture
        $data['ezcontentobject_trash'] = array();
        $data['ezurlwildcard'] = array();
        $data['ezmedia'] = array();
        $data['ezkeyword'] = array();

        foreach ($data as $table => $rows) {
            // Cleanup before inserting (using TRUNCATE for speed, however not possible to rollback)
            $q = $dbPlatform->getTruncateTableSql($handler->quoteIdentifier($table));
            $connection->executeUpdate($q);

            // Check that at least one row exists
            if (!isset($rows[0])) {
                continue;
            }

            $q = $handler->createInsertQuery();
            $q->insertInto($handler->quoteIdentifier($table));

            // Contains the bound parameters
            $values = array();

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

        $this->applyStatements($this->getPostInsertStatements());
    }

    protected function getInitialVarDir()
    {
        return __DIR__ . '/../../../../../../var';
    }

    protected function cleanupVarDir($sourceDir)
    {
        $fs = new Filesystem();
        $varDir = self::$ioRootDir . '/var';
        if ($fs->exists($varDir)) {
            $fs->remove($varDir);
        }
        $fs->mkdir($varDir);
        $fs->mirror($sourceDir, $varDir);
    }

    /**
     * CLears internal in memory caches after inserting data circumventing the
     * API.
     */
    protected function clearInternalCaches()
    {
        /** @var $handler \eZ\Publish\Core\Persistence\Legacy\Handler */
        $handler = $this->getServiceContainer()->get('ezpublish.spi.persistence.legacy');

        $contentLanguageHandler = $handler->contentLanguageHandler();
        if ($contentLanguageHandler instanceof CachingLanguageHandler) {
            $contentLanguageHandler->clearCache();
        }

        $contentTypeHandler = $handler->contentTypeHandler();
        if ($contentTypeHandler instanceof CachingContentTypeHandler) {
            $contentTypeHandler->clearCache();
        }

        /** @var $cachePool \Psr\Cache\CacheItemPoolInterface */
        $cachePool = $this->getServiceContainer()->get('ezpublish.cache_pool');

        $cachePool->clear();
    }

    /**
     * Returns statements to be executed after data insert.
     *
     * @return string[]
     */
    protected function getPostInsertStatements()
    {
        if (self::$db === 'pgsql') {
            $setvalPath = __DIR__ . '/../../../../Core/Persistence/Legacy/Tests/_fixtures/setval.pgsql.sql';

            return array_filter(preg_split('(;\\s*$)m', file_get_contents($setvalPath)));
        }

        return array();
    }

    /**
     * Returns the initial database data.
     *
     * @return array
     */
    protected function getInitialData()
    {
        if (!isset(self::$initialData)) {
            self::$initialData = include __DIR__ . '/../../../../Core/Repository/Tests/Service/Integration/Legacy/_fixtures/clean_ezdemo_47_dump.php';
            // self::$initialData = include __DIR__ . '/../../../../Core/Repository/Tests/Service/Legacy/_fixtures/full_dump.php';
        }

        return self::$initialData;
    }

    /**
     * Initializes the database schema.
     */
    protected function initializeSchema()
    {
        if (!self::$schemaInitialized) {
            $statements = $this->getSchemaStatements();

            $this->applyStatements($statements);

            self::$schemaInitialized = true;
        }
    }

    /**
     * Applies the given SQL $statements to the database in use.
     *
     * @param array $statements
     */
    protected function applyStatements(array $statements)
    {
        foreach ($statements as $statement) {
            $this->getDatabaseHandler()->exec($statement);
        }
    }

    // ************* Setup copied and refactored from common.php ************

    /**
     * Returns the database schema as an array of SQL statements.
     *
     * @return string[]
     */
    protected function getSchemaStatements()
    {
        $schemaPath = __DIR__ . '/../../../../Core/Persistence/Legacy/Tests/_fixtures/schema.' . self::$db . '.sql';

        return array_filter(preg_split('(;\\s*$)m', file_get_contents($schemaPath)));
    }

    /**
     * Returns the database handler from the service container.
     *
     * @return \eZ\Publish\Core\Persistence\Doctrine\ConnectionHandler
     */
    protected function getDatabaseHandler()
    {
        return $this->getServiceContainer()->get('ezpublish.api.storage_engine.legacy.dbhandler');
    }

    /**
     * Returns the service container used for initialization of the repository.
     *
     * @return \eZ\Publish\Core\Base\ServiceContainer
     */
    public function getServiceContainer()
    {
        if (!isset(self::$serviceContainer)) {
            $config = include __DIR__ . '/../../../../../../config.php';
            $installDir = $config['install_dir'];

            /** @var \Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder */
            $containerBuilder = include $config['container_builder_path'];

            /* @var \Symfony\Component\DependencyInjection\Loader\YamlFileLoader $loader */
            $loader->load('search_engines/legacy.yml');
            $loader->load('tests/integration_legacy.yml');

            $this->externalBuildContainer($containerBuilder);

            $containerBuilder->setParameter(
                'legacy_dsn',
                self::$dsn
            );

            $containerBuilder->setParameter(
                'io_root_dir',
                self::$ioRootDir . '/' . $containerBuilder->getParameter('storage_dir')
            );

            $containerBuilder->addCompilerPass(new Compiler\Search\SearchEngineSignalSlotPass('legacy'));
            $containerBuilder->addCompilerPass(new Compiler\Search\FieldRegistryPass());

            self::$serviceContainer = new ServiceContainer(
                $containerBuilder,
                $installDir,
                $config['cache_dir'],
                true,
                true
            );
        }

        return self::$serviceContainer;
    }

    /**
     * This is intended to be used from external repository in order to
     * enable container customization.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder
     */
    protected function externalBuildContainer(ContainerBuilder $containerBuilder)
    {
        // Does nothing by default
    }

    /**
     * Get the Database name.
     *
     * @return string
     */
    public function getDB()
    {
        return self::$db;
    }
}
