<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformInstallerBundle\Command;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception\ConnectionException;
use EzSystems\PlatformInstallerBundle\src\Installer\Installer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class InstallPlatformCommand extends ContainerAwareCommand
{
    /** @var \Doctrine\DBAL\Connection */
    private $db;

    /** @var \Symfony\Component\Console\Output\OutputInterface */
    private $output;

    /** @var \EzSystems\PlatformInstallerBundle\Installer\Installer */
    private $installer;

    const EXIT_DATABASE_NOT_FOUND_ERROR = 3;
    const EXIT_GENERAL_DATABASE_ERROR = 4;
    const EXIT_PARAMETERS_NOT_FOUND = 5;
    const EXIT_UNKNOWN_INSTALL_TYPE = 6;

    protected function configure()
    {
        $this->setName( 'ezplatform:install' );
        $this->addOption( 'type', null, InputArgument::OPTIONAL, "The type of install to execute, one of: clean, demo-clean", 'clean' );
    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        $this->checkParameters();
        $this->checkDatabase();

        switch ( $type = $input->getOption( 'type' ) )
        {
            case 'clean':
                $installer = $this->getContainer()->get( 'ezplatform.installer.clean_installer' );
                break;
            case 'demo-clean':
                $installer = $this->getContainer()->get( 'ezplatform.installer.democlean_installer' );
                break;
            default:
                $output->writeln( "Unknown install type '$type''" );
                exit( 6 );
        }

        $installer->createConfiguration();
        $installer->importSchema();
        $installer->importData();
    }

    private function checkParameters()
    {
        $parametersFile = 'ezpublish/config/parameters.yml';
        if (!is_file( $parametersFile )) {
            $this->output->writeln("Required configuration file $parametersFile not found");
            exit(self::EXIT_PARAMETERS_NOT_FOUND);
        }
    }

    /**
     * @throws \Exception if an unexpected database error occurs
     */
    private function configuredDatabaseExists()
    {
        $this->db = $this->getContainer()->get( 'database_connection' );
        try {
            $this->db->connect();
        } catch ( ConnectionException $e ) {
            // @todo 1049 is MySQL's code for "database doesn't exist", refactor
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
