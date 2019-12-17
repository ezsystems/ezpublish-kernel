<?php

/**
 * File containing the Test Setup Factory base class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests\SetupFactory;

use Doctrine\DBAL\Connection;
use eZ\Publish\API\Repository\Tests\LegacySchemaImporter;
use eZ\Publish\Core\Base\ServiceContainer;
use eZ\Publish\SPI\Tests\Persistence\Fixture;
use eZ\Publish\SPI\Tests\Persistence\FixtureImporter;
use eZ\Publish\SPI\Tests\Persistence\YamlFixture;
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
     * Cached in-memory initial database data fixture.
     *
     * @var \eZ\Publish\SPI\Tests\Persistence\Fixture
     */
    private static $initialDataFixture;

    /**
     * Cached in-memory post insert SQL statements.
     *
     * @var string[]
     */
    private static $postInsertStatements;

    protected $repositoryReference = 'ezpublish.api.repository';

    /** @var \Doctrine\DBAL\Connection */
    private $connection;

    /**
     * Creates a new setup factory.
     */
    public function __construct()
    {
        self::$dsn = getenv('DATABASE');
        if (!self::$dsn) {
            // use sqlite in-memory by default (does not need special handling for paratest as it's per process)
            self::$dsn = 'sqlite://:memory:';
        } elseif (getenv('TEST_TOKEN') !== false) {
            // Using paratest, assuming dsn ends with db name here...
            self::$dsn .= '_' . getenv('TEST_TOKEN');
        }

        if ($repositoryReference = getenv('REPOSITORY_SERVICE_ID')) {
            $this->repositoryReference = $repositoryReference;
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
        /** @var \eZ\Publish\API\Repository\Repository $repository */
        $repository = $this->getServiceContainer()->get($this->repositoryReference);

        // Set admin user as current user by default
        $repository->getPermissionResolver()->setCurrentUserReference(
            new UserReference(14)
        );

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
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function insertData(): void
    {
        $connection = $this->getDatabaseConnection();
        $this->cleanupVarDir($this->getInitialVarDir());

        $fixtureImporter = new FixtureImporter($connection);
        $fixtureImporter->import($this->getInitialDataFixture());
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
     * Returns the initial database data fixture.
     *
     * @return \eZ\Publish\SPI\Tests\Persistence\Fixture
     */
    protected function getInitialDataFixture(): Fixture
    {
        if (!isset(self::$initialDataFixture)) {
            self::$initialDataFixture = new YamlFixture(
                __DIR__ . '/../_fixtures/Legacy/data/test_data.yaml'
            );
        }

        return self::$initialDataFixture;
    }

    /**
     * Initializes the database schema.
     *
     * @throws \Doctrine\DBAL\ConnectionException
     */
    protected function initializeSchema(): void
    {
        if (!self::$schemaInitialized) {
            $schemaImporter = new LegacySchemaImporter($this->getDatabaseConnection());
            $schemaImporter->importSchema(
                dirname(__DIR__, 5) .
                '/Bundle/EzPublishCoreBundle/Resources/config/storage/legacy/schema.yaml'
            );

            self::$schemaInitialized = true;
        }
    }

    /**
     * Returns the raw database connection from the service container.
     *
     * @return \Doctrine\DBAL\Connection
     */
    private function getDatabaseConnection(): Connection
    {
        if (null === $this->connection) {
            $this->connection = $this->getServiceContainer()->get('ezpublish.persistence.connection');
        }

        return $this->connection;
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

            $containerBuilder->addCompilerPass(new Compiler\Search\FieldRegistryPass());

            // load overrides just before creating test Container
            $loader->load('tests/override.yml');

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
