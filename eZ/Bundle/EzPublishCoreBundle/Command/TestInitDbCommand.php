<?php
/**
 * File containing the TestInitDbCommand class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Exception;

class TestInitDbCommand extends ContainerAwareCommand
{
    const DEFAULT_FIXTURE = "demoempty.php";
    const DEFAULT_FIXTURE_FOLDER = "/../../../../data/";

    protected function configure()
    {
        // present the existing fixtures on the default location
        $fixturesHelp = "";
        foreach ( $this->getDefaultDirectoryFixtures() as $key )
        {
            $fixturesHelp .= "  - $key\n";
        }

        // command configurations
        $this
            ->setName( 'ezpublish:test:init_db' )
            ->addOption(
                'no-database',
                null,
                InputOption::VALUE_NONE,
                'Do not init the database'
            )
            ->addOption(
                'fixture',
                null,
                InputOption::VALUE_REQUIRED,
                'Choose what fixture to add to database'
            )
            ->setDescription( 'Inits the configured database for test use based on existing fixtures' )
            ->setHelp(
                <<<EOT
The command <info>%command.name%</info> initializes current configured database with
existing fixture data.

<info>Possible fixtures</info>:
$fixturesHelp
<error>WARNING:</error>
  This command will delete all data in the configured database before filling it
  with fixture data.
EOT
            );
    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        $database = $this->getContainer()->get( 'ezpublish.config.resolver' )->getParameter( 'database.params' );
        if ( is_array( $database ) )
        {
            $driverMap = array(
                'pdo_mysql' => 'mysql',
                'pdo_pgsql' => 'pgsql',
                'pdo_sqlite' => 'sqlite',
            );

            $dbType = $driverMap[$database['driver']];
            $database = $database['dbname'];
        }
        else
        {
            $dbType = preg_replace( '(^([a-z]+).*)', '\\1', $database );
        }

        if (
            $input->isInteractive() &&
            !$this->getHelperSet()->get( 'dialog' )->askConfirmation(
                $output,
                "<question>Are you sure you want to delete all data in '{$database}' database?</question>",
                false
            )
        )
        {
            return;
        }

        $output->writeln( "<info>Starting to take care of:</info>" );

        // @TODO Reuse API Integration tests SetupFactory when it has been refactored to be able to use Symfony DIC
        // take care of database initialization
        $output->write( "<info>Database: </info>" );
        if ( !$input->getOption( 'no-database' ) )
        {
            $this->applyStatements( $this->getSchemaStatements( $dbType ) );
            $output->writeln( "done!" );
        }
        else
        {
            $output->writeln( "skipped!" );
        }

        // get fixture name or set default one
        $fixtureOptionValue = $input->getOption( 'fixture' );
        $fixture = ( empty( $fixtureOptionValue ) ) ?
            self::DEFAULT_FIXTURE :
            $fixtureOptionValue;

        // take care of initial data for the database
        $output->write( "<info>Fixture: </info>'$fixture' " );
        $this->insertData( $dbType, $fixture );
        $output->writeln( "done!" );
    }

    /**
     * Insert the database data
     *
     * @param string $dbType Name of Database type (mysql, sqlite, pgsql, ..)
     * @return void
     */
    public function insertData( $dbType, $fixture )
    {
        // Get Initial fixture data and union with some tables that must be present but sometimes aren't
        $data = $this->getInitialData( $fixture ) + array(
            'ezcontentobject_trash' => array(),
            'ezurlwildcard' => array(),
            'ezmedia' => array(),
            'ezkeyword' => array()
        );
        $handler = $this->getDatabaseHandler();
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
                catch ( Exception $e )
                {
                    echo "$table ( ", implode( ', ', $row ), " )\n";
                    throw $e;
                }
            }
        }

        $this->applyStatements( $this->getPostInsertStatements( $dbType ) );
    }

    /**
     * Returns statements to be executed after data insert
     *
     * @param string $dbType Name of Database type (mysql, sqlite, pgsql, ..)
     * @return string[]
     */
    protected function getPostInsertStatements( $dbType )
    {
        if ( $dbType === 'pgsql' )
        {
            $setvalPath = __DIR__ . '/../../../Publish/Core/Persistence/Legacy/Tests/_fixtures/setval.pgsql.sql';
            return array_filter( preg_split( '(;\\s*$)m', file_get_contents( $setvalPath ) ) );
        }
        return array();
    }

    /**
     * Returns the initial database data
     *
     * @param string $fixture Name or path to fixture file
     * @return array
     *
     * @throws InvalidArgumentException If the $fixture doesn't point to a file
     */
    protected function getInitialData( $fixture )
    {
        // verify if $fixture is a complete path to a file
        $file = ( file_exists( $fixture ) ) ? $fixture : false;

        // this is needed since it's possible to pass a complete path to a file
        if ( !$file )
        {
            $file = __DIR__ . self::DEFAULT_FIXTURE_FOLDER . $fixture;
        }

        // if file doesn't exist throw invalid argument exception
        if ( !file_exists( $file ) || !is_readable( $file ) || is_dir( $file ) )
        {
            throw new \InvalidArgumentException( "Fixture '$fixture' wasn't be found" );
        }

        // include file and verify if it is an array
        $fixtureData = include $file;
        if ( !is_array( $fixtureData ) )
        {
            throw new \InvalidArgumentException( "Fixture file '$fixture' is invalid" );
        }

        return $fixtureData;
    }

    /**
     * Applies the given SQL $statements to the database in use
     *
     * @param array $statements
     *
     * @return void
     */
    protected function applyStatements( array $statements )
    {
        $handler = $this->getDatabaseHandler();
        foreach ( $statements as $statement )
        {
            $handler->exec( $statement );
        }
    }

    /**
     * Returns the database schema as an array of SQL statements
     *
     * @param string $dbType Name of Database type (mysql, sqlite, pgsql, ..)
     * @return string[]
     */
    protected function getSchemaStatements( $dbType )
    {
        $schemaPath = __DIR__ . "/../../../../data/{$dbType}/schema.sql";
        return array_filter( preg_split( '(;\\s*$)m', file_get_contents( $schemaPath ) ) );
    }

    /**
     * Returns the database handler from the service container
     *
     * @return \eZ\Publish\Core\Persistence\Doctrine\ConnectionHandler
     */
    protected function getDatabaseHandler()
    {
        return $this->getContainer()->get( 'ezpublish.api.storage_engine.legacy.dbhandler' );
    }

    /**
     * Reads files in the default directory and return a filtered list with them
     * excluding all non PHP (.php) files
     *
     * @return array
     */
    protected function getDefaultDirectoryFixtures()
    {
        $files = scandir( __DIR__ . self::DEFAULT_FIXTURE_FOLDER );
        $return = array();
        for ( $i = 0; !empty( $files[$i] ); $i++ )
        {
            // check if the file has php extension
            if ( strpos( $files[$i], ".php" ) === strlen( $files[$i] ) - 4 )
            {
                $return[] = $files[$i];
            }
        }

        return $return;
    }
}
