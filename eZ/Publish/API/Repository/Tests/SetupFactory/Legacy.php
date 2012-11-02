<?php
/**
 * File containing the Test Setup Factory base class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\SetupFactory;
use eZ\Publish\API\Repository\Tests\SetupFactory;
use eZ\Publish\API\Repository\Tests\IdManager;

use eZ\Publish\Core\Base\ConfigurationManager;
use eZ\Publish\Core\Base\ServiceContainer;

/**
 * A Test Factory is used to setup the infrastructure for a tests, based on a
 * specific repository implementation to test.
 */
class Legacy extends SetupFactory
{
    /**
     * Data source name
     *
     * @var string
     */
    protected static $dsn;

    /**
     * Database type (sqlite, mysql, ...)
     *
     * @var string
     */
    protected static $db;

    /**
     * Service container
     *
     * @var ServiceContainer
     */
    protected static $serviceContainer;

    /**
     * Global settings of eZ Publish setup
     *
     * @var mixed
     * @todo This might change, if ezp-next starts using anothe DI mechanism
     */
    protected static $globalSettings;

    /**
     * Configuration manager
     */
    protected static $configurationManager;

    /**
     * If the DB schema has already been initialized
     *
     * @var bool
     */
    protected static $schemaInitialized = false;

    /**
     * Initial database data
     *
     * @var array
     */
    protected static $initialData;

    /**
     * Creates a new setup factory
     *
     * @return void
     */
    public function __construct()
    {
        self::$dsn = ( isset( $_ENV['DATABASE'] ) && $_ENV['DATABASE'] ) ? $_ENV['DATABASE'] : 'sqlite://:memory:';
        self::$db = preg_replace( '(^([a-z]+).*)', '\\1', self::$dsn );
    }

    /**
     * Returns a configured repository for testing.
     *
     * @param bool $initializeFromScratch if the back end should be initialized
     *                                    from scratch or re-used
     * @return \eZ\Publish\API\Repository\Repository
     */
    public function getRepository( $initializeFromScratch = true )
    {
        if ( $initializeFromScratch )
        {
            $this->initializeSchema();
            $this->insertData();
        }

        $repository = $this->getServiceContainer()->get( 'inner_repository' );
        $repository->setCurrentUser(
            $repository->getUserService()->loadUser( 14 )
        );
        return $repository;
    }

    /**
     * Returns a config value for $configKey.
     *
     * @param string $configKey
     * @return mixed
     * @throws Exception if $configKey could not be found.
     */
    public function getConfigValue( $configKey )
    {
        return $this->getServiceContainer()->getVariable( $configKey );
    }

    /**
     * Returns a repository specific ID manager.
     *
     * @return \eZ\Publish\API\Repository\Tests\IdManager
     */
    public function getIdManager()
    {
        return new IdManager\Php;
    }

    /**
     * Insert the database data
     *
     * @return void
     */
    public function insertData()
    {
        $data = $this->getInitialData();
        $handler = $this->getDatabaseHandler();

        // FIXME: Needs to be in fixture
        $data['ezcontentobject_trash'] = array();
        $data['ezurlwildcard'] = array();

        foreach ( $data as $table => $rows )
        {
            // Cleanup before inserting
            $deleteQuery = $handler->createDeleteQuery();
            $deleteQuery->deleteFrom( $handler->quoteIdentifier( $table ) );
            $stmt = $deleteQuery->prepare();
            $stmt->execute();

            // Check that at least one row exists
            if ( !isset( $rows[0] ) )
            {
                continue;
            }

            $q = $handler->createInsertQuery();
            $q->insertInto( $handler->quoteIdentifier( $table ) );

            // Contains the bound parameters
            $values = array();

            // Binding the parameters
            foreach ( $rows[0] as $col => $val )
            {
                $q->set(
                    $handler->quoteIdentifier( $col ),
                    $q->bindParam( $values[$col] )
                );
            }

            $stmt = $q->prepare();

            foreach ( $rows as $row )
            {
                try
                {
                    // This CANNOT be replaced by:
                    // $values = $row
                    // each $values[$col] is a PHP reference which should be
                    // kept for parameters binding to work
                    foreach ( $row as $col => $val )
                    {
                        $values[$col] = $val;
                    }

                    $stmt->execute();
                }
                catch ( \Exception $e )
                {
                    echo "$table ( ", implode( ', ', $row ), " )\n";
                    throw $e;
                }
            }
        }

        $this->applyStatements( $this->getPostInsertStatements() );
    }

    /**
     * Returns statements to be executed after data insert
     *
     * @return string[]
     */
    protected function getPostInsertStatements()
    {
        if ( self::$db === 'pgsql' )
        {
            $setvalPath = __DIR__ . '/../../../../Core/Persistence/Legacy/Tests/_fixtures/setval.pgsql.sql';
            return array_filter( preg_split( '(;\\s*$)m', file_get_contents( $setvalPath ) ) );
        }
        return array();
    }

    /**
     * Returns the inital database data
     *
     * @return array
     */
    protected function getInitialData()
    {
        if ( !isset( self::$initialData ) )
        {
            self::$initialData = include __DIR__ . '/../../../../Core/Repository/Tests/Service/Legacy/_fixtures/clean_ezdemo_47_dump.php';
            // self::$initialData = include __DIR__ . '/../../../../Core/Repository/Tests/Service/Legacy/_fixtures/full_dump.php';
        }
        return self::$initialData;
    }

    /**
     * Initializes the database schema
     *
     * @return void
     */
    protected function initializeSchema()
    {
        if ( !self::$schemaInitialized )
        {
            $dbHandler = $this->getDatabaseHandler();
            $statements = $this->getSchemaStatemets();

            $this->applyStatements( $statements );
        }

        self::$schemaInitialized = true;
    }

    /**
     * Applies the given SQL $statements to the database in use
     *
     * @param array $statements
     * @return void
     */
    protected function applyStatements( array $statements )
    {
        foreach ( $statements as $statement )
        {
            $this->getDatabaseHandler()->exec( $statement );
        }
    }

    // ************* Setup copied and refactored from common.php ************

    /**
     * Returns the database schema as an array of SQL statements
     *
     * @return string[]
     */
    protected function getSchemaStatemets()
    {
        $schemaPath = __DIR__ . '/../../../../Core/Persistence/Legacy/Tests/_fixtures/schema.' . self::$db . '.sql';

        return array_filter( preg_split( '(;\\s*$)m', file_get_contents( $schemaPath ) ) );
    }

    /**
     * Returns the database handler from the service container
     *
     * @return EzcDbHandler
     */
    protected function getDatabaseHandler()
    {
        return $this->getServiceContainer()->get( 'legacy_db_handler' );
    }

    /**
     * Returns the global ezp-next settings
     *
     * @return mixed
     */
    protected function getGlobalSettings()
    {
        if ( self::$globalSettings === null )
        {
            $settingsPath = __DIR__ . '/../../../../../../config.php';

            if ( !file_exists( $settingsPath ) )
            {
                throw new \RuntimeException( 'Could not find config.php, please copy config.php-DEVELOPMENT to config.php customize to your needs!' );
            }

            self::$globalSettings = include $settingsPath;
        }

        return self::$globalSettings;
    }

    /**
     * Returns the configuration manager
     *
     * @return ConfigurationManager
     */
    protected function getConfigurationManager()
    {
        if ( !isset( self::$configurationManager ) )
        {
            $settings = $this->getGlobalSettings();

            self::$configurationManager = new ConfigurationManager(
                array_merge_recursive(
                    $settings,
                    array(
                        'base' => array(
                            'Configuration' => array(
                                'UseCache' => false
                            )
                        )
                    )
                ),
                $settings['base']['Configuration']['Paths']
            );
        }

        return self::$configurationManager;
    }

    /**
     * Returns the dependency configuration
     *
     * @return array
     */
    protected function getDependencyConfiguration()
    {
        $dependencies = array();
        if ( isset( $_ENV['legacyKernel'] ) )
        {
            $dependencies['@legacyKernel'] = $_ENV['legacyKernel'];
        }
        return $dependencies;
    }

    /**
     * Returns the service container used for initialization of the repository
     *
     * @return ServiceContainer
     * @todo Getting service container statically, too, would be nice
     */
    protected function getServiceContainer()
    {
        if ( !isset( self::$serviceContainer ) )
        {
            $configManager = $this->getConfigurationManager();

            $serviceSettings = $configManager->getConfiguration('service')->getAll();

            $serviceSettings['inner_repository']['arguments']['persistence_handler'] = '@persistence_handler_legacy';
            $serviceSettings['inner_repository']['arguments']['io_handler'] = '@io_handler_legacy';

            // Needed for URLAliasService tests
            $serviceSettings['inner_repository']['arguments']['service_settings']['language']['languages'][] = 'eng-US';
            $serviceSettings['inner_repository']['arguments']['service_settings']['language']['languages'][] = 'eng-GB';

            $serviceSettings['persistence_handler_legacy']['arguments']['config']['dsn'] = self::$dsn;
            $serviceSettings['legacy_db_handler']['arguments']['dsn'] = self::$dsn;

            self::$serviceContainer = new ServiceContainer(
                $serviceSettings,
                $this->getDependencyConfiguration()
            );
        }

        return self::$serviceContainer;
    }
}
