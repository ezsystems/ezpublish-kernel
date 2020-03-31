<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests;

use Doctrine\Common\EventManager as DoctrineEventManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\API\Repository\Tests\LegacySchemaImporter;
use eZ\Publish\Core\Persistence\Doctrine\ConnectionHandler;
use eZ\Publish\Core\Persistence\Legacy\SharedGateway;
use eZ\Publish\Core\Persistence\Tests\DatabaseConnectionFactory;
use eZ\Publish\SPI\Tests\Persistence\FileFixtureFactory;
use eZ\Publish\SPI\Tests\Persistence\FixtureImporter;
use eZ\Publish\SPI\Tests\Persistence\YamlFixture;
use EzSystems\DoctrineSchema\Database\DbPlatform\SqliteDbPlatform;
use InvalidArgumentException;
use PDOException;
use PHPUnit\Framework\TestCase as BaseTestCase;
use ReflectionObject;
use ReflectionProperty;

/**
 * Base test case for database related tests.
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * DSN used for the DB backend.
     *
     * @var string
     */
    protected $dsn;

    /**
     * Name of the DB, extracted from DSN.
     *
     * @var string
     */
    protected $db;

    /**
     * Database handler -- to not be constructed twice for one test.
     *
     * @internal
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     */
    protected $handler;

    /**
     * Doctrine Database connection -- to not be constructed twice for one test.
     *
     * @internal
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /** @var \eZ\Publish\Core\Persistence\Legacy\SharedGateway\Gateway */
    private $sharedGateway;

    /**
     * Get data source name.
     *
     * The database connection string is read from an optional environment
     * variable "DATABASE" and defaults to an in-memory SQLite database.
     *
     * @return string
     */
    protected function getDsn()
    {
        if (!$this->dsn) {
            $this->dsn = getenv('DATABASE');
            if (!$this->dsn) {
                $this->dsn = 'sqlite://:memory:';
            }
            $this->db = preg_replace('(^([a-z]+).*)', '\\1', $this->dsn);
        }

        return $this->dsn;
    }

    /**
     * Get a eZ Doctrine database connection handler.
     *
     * Get a ConnectionHandler, which can be used to interact with the configured
     * database. The database connection string is read from an optional
     * environment variable "DATABASE" and defaults to an in-memory SQLite
     * database.
     *
     * @return \eZ\Publish\Core\Persistence\Doctrine\ConnectionHandler
     */
    final public function getDatabaseHandler()
    {
        if (!$this->handler) {
            $this->handler = ConnectionHandler::createFromConnection($this->getDatabaseConnection());
            $this->db = $this->handler->getName();
        }

        return $this->handler;
    }

    /**
     * Get native Doctrine database connection.
     */
    final public function getDatabaseConnection(): Connection
    {
        if (!$this->connection) {
            $eventManager = new DoctrineEventManager();
            $connectionFactory = new DatabaseConnectionFactory(
                [new SqliteDbPlatform()],
                $eventManager
            );

            try {
                $this->connection = $connectionFactory->createConnection($this->getDsn());
            } catch (DBALException $e) {
                self::fail('Connection failed: ' . $e->getMessage());
            }
        }

        return $this->connection;
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    final public function getSharedGateway(): SharedGateway\Gateway
    {
        if (!$this->sharedGateway) {
            $connection = $this->getDatabaseConnection();
            $factory = new SharedGateway\GatewayFactory(
                new SharedGateway\DatabasePlatform\FallbackGateway($connection),
                [
                    'sqlite' => new SharedGateway\DatabasePlatform\SqliteGateway($connection),
                ]
            );

            $this->sharedGateway = $factory->buildSharedGateway($connection);
        }

        return $this->sharedGateway;
    }

    /**
     * Resets the database on test setup, so we always operate on a clean
     * database.
     */
    protected function setUp(): void
    {
        try {
            $schemaImporter = new LegacySchemaImporter($this->getDatabaseConnection());
            $schemaImporter->importSchema(
                dirname(__DIR__, 5) .
                '/Bundle/EzPublishCoreBundle/Resources/config/storage/legacy/schema.yaml'
            );
        } catch (PDOException | DBALException | ConnectionException $e) {
            self::fail(
                sprintf(
                    'PDO session could not be created: %s: %s',
                    get_class($e),
                    $e->getMessage()
                )
            );
        }
    }

    protected function tearDown(): void
    {
        unset($this->handler);
        unset($this->connection);
    }

    /**
     * Get a text representation of a result set.
     *
     * @param array $result
     *
     * @return string
     */
    protected static function getResultTextRepresentation(array $result)
    {
        return implode(
            "\n",
            array_map(
                function ($row) {
                    return implode(', ', $row);
                },
                $result
            )
        );
    }

    /**
     * Insert a database fixture from the given file.
     */
    protected function insertDatabaseFixture(string $file): void
    {
        try {
            $fixtureImporter = new FixtureImporter($this->getDatabaseConnection());
            $fixtureImporter->import((new FileFixtureFactory())->buildFixture($file));
        } catch (DBALException $e) {
            self::fail('Database fixture import failed: ' . $e->getMessage());
        }
    }

    /**
     * Insert test_data.yaml fixture, common for many test cases.
     *
     * See: eZ/Publish/API/Repository/Tests/_fixtures/Legacy/data/test_data.yaml
     */
    protected function insertSharedDatabaseFixture(): void
    {
        try {
            $fixtureImporter = new FixtureImporter($this->getDatabaseConnection());
            $fixtureImporter->import(
                new YamlFixture(
                    __DIR__ . '/../../../../API/Repository/Tests/_fixtures/Legacy/data/test_data.yaml'
                )
            );
        } catch (DBALException $e) {
            self::fail('Database fixture import failed: ' . $e->getMessage());
        }
    }

    /**
     * Assert query result as correct.
     *
     * Builds text representations of the asserted and fetched query result,
     * based on a eZ\Publish\Core\Persistence\Database\SelectQuery object. Compares them using classic diff for
     * maximum readability of the differences between expectations and real
     * results.
     *
     * The expectation MUST be passed as a two dimensional array containing
     * rows of columns.
     *
     * @param array $expectation expected raw database rows
     */
    public static function assertQueryResult(
        array $expectation,
        QueryBuilder $query,
        string $message = ''
    ): void {
        $result = $query->execute()->fetchAll(FetchMode::ASSOCIATIVE);

        self::assertEquals(
            self::getResultTextRepresentation($expectation),
            self::getResultTextRepresentation($result),
            $message
        );
    }

    /**
     * Asserts correct property values on $object.
     *
     * Asserts that for all keys in $properties a corresponding property
     * exists in $object with the *same* value as in $properties.
     *
     * @param array $properties
     * @param object $object
     */
    protected function assertPropertiesCorrect(array $properties, $object)
    {
        if (!is_object($object)) {
            throw new InvalidArgumentException(
                'Received ' . gettype($object) . ' instead of object as second parameter'
            );
        }
        foreach ($properties as $propName => $propVal) {
            $this->assertSame(
                $propVal,
                $object->$propName,
                "Incorrect value for \${$propName}"
            );
        }
    }

    /**
     * Asserts $expStruct equals $actStruct in at least $propertyNames.
     *
     * Asserts that properties of $actStruct equal properties of $expStruct (not
     * vice versa!). If $propertyNames is null, all properties are checked.
     * Otherwise, $propertyNames provides a white list.
     *
     * @param object $expStruct
     * @param object $actStruct
     * @param array $propertyNames
     */
    protected function assertStructsEqual(
        $expStruct,
        $actStruct,
        array $propertyNames = null
    ) {
        if ($propertyNames === null) {
            $propertyNames = $this->getPublicPropertyNames($expStruct);
        }
        foreach ($propertyNames as $propName) {
            $this->assertEquals(
                $expStruct->$propName,
                $actStruct->$propName,
                "Properties \${$propName} not same"
            );
        }
    }

    /**
     * Returns public property names in $object.
     *
     * @param object $object
     *
     * @return array
     */
    protected function getPublicPropertyNames($object)
    {
        $refl = new ReflectionObject($object);

        return array_map(
            function ($prop) {
                return $prop->getName();
            },
            $refl->getProperties(ReflectionProperty::IS_PUBLIC)
        );
    }

    /**
     * @return string
     */
    protected static function getInstallationDir()
    {
        static $installDir = null;
        if ($installDir === null) {
            $config = require 'config.php';
            $installDir = $config['install_dir'];
        }

        return $installDir;
    }
}
