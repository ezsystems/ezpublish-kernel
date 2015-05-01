<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformInstallerBundle\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\ConnectionException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;

class InstallPlatformCommand extends Command
{
    /** @var \Doctrine\DBAL\Connection */
    private $db;

    /** @var \Symfony\Component\Console\Output\OutputInterface */
    private $output;

    /** @var \Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface */
    private $cacheClearer;

    /** @var \Symfony\Component\Filesystem\Filesystem */
    private $filesystem;

    /** @var string */
    private $cacheDir;

    /** @var \EzSystems\PlatformInstallerBundle\Installer\Installer[] */
    private $installers = array();

    const EXIT_DATABASE_NOT_FOUND_ERROR = 3;
    const EXIT_GENERAL_DATABASE_ERROR = 4;
    const EXIT_PARAMETERS_NOT_FOUND = 5;
    const EXIT_UNKNOWN_INSTALL_TYPE = 6;
    const EXIT_MISSING_PERMISSIONS = 7;

    public function __construct( Connection $db, array $installers, CacheClearerInterface $cacheClearer, Filesystem $filesystem, $cacheDir )
    {
        $this->db = $db;
        $this->installers = $installers;
        $this->cacheClearer = $cacheClearer;
        $this->filesystem = $filesystem;
        $this->cacheDir = $cacheDir;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName( 'ezplatform:install' );
        $this->addArgument(
            'type',
            InputArgument::REQUIRED,
            "The type of install. Available options: " . implode( ', ', array_keys( $this->installers ) )
        );
    }

    protected function execute( InputInterface $input, OutputInterface $output )
    {
        $this->output = $output;
        $this->checkPermissions();
        $this->checkParameters();
        $this->checkDatabase();

        $type = $input->getArgument( 'type' );
        $installer = $this->getInstaller( $type );
        if ( $installer === false )
        {
            $output->writeln( "Unknown install type '$type'" );
            exit( self::EXIT_UNKNOWN_INSTALL_TYPE );
        }

        $installer->setOutput( $output );

        $installer->createConfiguration();
        $installer->importSchema();
        $installer->importData();
        $installer->importBinaries();
        $this->cacheClear( $output );
    }

    private function checkPermissions()
    {
        if ( !is_writable( 'ezpublish/config' ) )
        {
            $this->output->writeln( "ezpublish/config is not writable" );
            exit( self::EXIT_MISSING_PERMISSIONS );
        }
    }

    private function checkParameters()
    {
        $parametersFile = 'ezpublish/config/parameters.yml';
        if ( !is_file( $parametersFile ) )
        {
            $this->output->writeln( "Required configuration file $parametersFile not found" );
            exit( self::EXIT_PARAMETERS_NOT_FOUND );
        }
    }

    /**
     * @throws \Exception if an unexpected database error occurs
     */
    private function configuredDatabaseExists()
    {
        try
        {
            $this->db->connect();
        }
        catch ( ConnectionException $e )
        {
            // @todo 1049 is MySQL's code for "database doesn't exist", refactor
            if ( $e->getPrevious()->getCode() == 1049 )
            {
                return false;
            }
            throw $e;
        }
        return true;
    }

    private function checkDatabase()
    {
        try
        {
            if ( !$this->configuredDatabaseExists() )
            {
                $this->output->writeln(
                    sprintf(
                        "The configured database '%s' does not exist",
                        $this->db->getDatabase()
                    )
                );
                exit( self::EXIT_DATABASE_NOT_FOUND_ERROR );
            }
        }
        catch ( ConnectionException $e )
        {
            $this->output->writeln( "An error occured connecting to the database:" );
            $this->output->writeln( $e->getMessage() );
            $this->output->writeln( "Please check the database configuration in parameters.yml" );
            exit( self::EXIT_GENERAL_DATABASE_ERROR );
        }
    }

    private function cacheClear( OutputInterface $output )
    {
        if ( !is_writable( $this->cacheDir ) )
        {
            throw new \RuntimeException( sprintf( 'Unable to write in the "%s" directory', $this->cacheDir ) );
        }

        $output->writeln( sprintf( 'Clearing cache for directory <info>%s</info>', $this->cacheDir ) );
        $oldCacheDir = $this->cacheDir . '_old';

        if ( $this->filesystem->exists( $oldCacheDir ) )
        {
            $this->filesystem->remove( $oldCacheDir );
        }

        $this->cacheClearer->clear( $this->cacheDir );

        $this->filesystem->rename( $this->cacheDir, $oldCacheDir );
        $this->filesystem->remove( $oldCacheDir );
    }

    /**
     * @param $type
     *
     * @return \EzSystems\PlatformInstallerBundle\Installer\Installer
     */
    private function getInstaller( $type )
    {
        if ( !isset( $this->installers[$type] ) )
        {
            return false;
        }

        return $this->installers[$type];
    }
}
