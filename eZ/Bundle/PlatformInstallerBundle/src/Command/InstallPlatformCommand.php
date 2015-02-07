<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformInstallerBundle\Command;

use Doctrine\DBAL\Exception\ConnectionException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class InstallPlatformCommand extends ContainerAwareCommand
{
    /** @var \Doctrine\DBAL\Connection */
    private $db;

    /** @var \Symfony\Component\Console\Output\OutputInterface */
    private $output;

    const EXIT_DATABASE_NOT_FOUND_ERROR = 3;
    const EXIT_GENERAL_DATABASE_ERROR = 4;
    const EXIT_PARAMETERS_NOT_FOUND = 5;

    protected function configure()
    {
        $this->setName( 'ezplatform:install' );
    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        $this->output = $output;

        $this->checkParameters();
        $this->checkDatabase();
        $this->importSchema();
        $this->importCleanData();
        $this->createConfiguration();
    }

    private function createConfiguration()
    {
        $this->copyConfigurationFile(
            __DIR__ . '/../Resources/config_templates/ezpublish.yml.clean',
            'ezpublish/config/ezpublish.yml'
        );

        $this->copyConfigurationFile(
            __DIR__ . '/../Resources/config_templates/ezpublish_dev.yml',
            'ezpublish/config/ezpublish_dev.yml'
        );

        $this->copyConfigurationFile(
            __DIR__ . '/../Resources/config_templates/ezpublish_prod.yml',
            'ezpublish/config/ezpublish_prod.yml'
        );
    }

    private function copyConfigurationFile($source, $target)
    {
        $fs = new Filesystem();
        $fs->copy($source, $target);

        if (!$this->output->isQuiet()) {
            $this->output->writeln( "Copied $source to $target");
        }
    }

    private function checkParameters()
    {
        $parametersFile = 'ezpublish/config/parameters.yml';
        if (!is_file( $parametersFile )) {
            $this->output->writeln("Required configuration file $parametersFile not found");
            exit(self::EXIT_PARAMETERS_NOT_FOUND);
        }
    }

    private function importSchema()
    {
        // @todo Should come from a different place
        // @todo Should be part of the LSE
        $this->runQueriesFromFile(
            'vendor/ezsystems/ezpublish-kernel/data/mysql/schema.sql'
        );
    }

    private function importCleanData()
    {
        // @todo Should come from a different place
        // @todo Should be part of the LSE
        $this->runQueriesFromFile(
            'vendor/ezsystems/ezpublish-kernel/data/cleandata.sql'
        );
    }

    private function runQueriesFromFile( $file )
    {
        $queries = array_filter( preg_split( '(;\\s*$)m', file_get_contents( $file ) ) );

        if (!$this->output->isQuiet())
        {
            $this->output->writeln(
                sprintf(
                    "Executing %d queries from %s on database %s",
                    count($queries),
                    $file,
                    $this->db->getDatabase()
                )
            );
        }

        foreach ($queries as $query)
        {
            $this->db->exec($query);
        }
    }

    private function configuredDatabaseExists()
    {
        $this->db = $this->getContainer()->get( 'database_connection' );
        try {
            $this->db->connect();
        } catch ( ConnectionException $e ) {
            // 1049 is MySQL's code, enhance
            if ( $e->getPrevious()->getCode() == 1049 )
            {
                return false;
            }
            throw $e;
        }
        return true;
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    private function checkDatabase()
    {
        try {
            if (!$this->configuredDatabaseExists()) {
                $this->output->writeln(
                    sprintf(
                        "The configured database '%s' does not exist",
                        $this->db->getDatabase()
                    )
                );
                exit(self::EXIT_DATABASE_NOT_FOUND_ERROR);
            }
        } catch ( ConnectionException $e ) {
            $this->output->writeln( "An error occured connecting to the database:" );
            $this->output->writeln( $e->getMessage() );
            $this->output->writeln( "Please check the database configuration in parameters.yml" );
            exit(self::EXIT_GENERAL_DATABASE_ERROR);
        }
    }
}
