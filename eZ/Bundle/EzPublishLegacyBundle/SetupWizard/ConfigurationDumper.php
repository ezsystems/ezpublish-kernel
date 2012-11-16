<?php
/**
 * File containing the legacy ConfigurationDumper class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\SetupWizard;

use eZ\Publish\Core\MVC\Symfony\ConfigDumperInterface,
    Symfony\Component\Yaml\Yaml,
    Symfony\Component\Filesystem\Filesystem;

class ConfigurationDumper implements ConfigDumperInterface
{
    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $fs;

    /**
     * Path to root dir (kernel.root_dir)
     *
     * @var string
     */
    protected $rootDir;

    /**
     * Path to cache dir (kernel.cache_dir)
     *
     * @var string
     */
    protected $cacheDir;

    /**
     * @var array Environments to pre-generate config file for.
     */
    protected $envs;

    public function __construct( Filesystem $fs, array $envs, $rootDir, $cacheDir )
    {
        $this->fs = $fs;
        $this->rootDir = $rootDir;
        $this->cacheDir = $cacheDir;
        $this->envs = $envs;
    }

    /**
     * Adds an environment to dump a configuration file for.
     *
     * @param string $env
     */
    public function addEnv( $env )
    {
        $this->envs[] = $env;
    }

    /**
     * Dumps settings contained in $configArray in ezpublish.yml
     *
     * @param array $configArray Hash of settings.
     * @param int $options A binary combination of options. See class OPT_* class constants in {@link \eZ\Publish\Core\MVC\Symfony\ConfigDumperInterface}
     *
     * @return void
     */
    public function dump( array $configArray, $options = 0 )
    {
        $configPath = "$this->rootDir/config";
        $mainConfigFile = "$configPath/ezpublish.yml";
        if ( $this->fs->exists( $mainConfigFile ) )
        {
            if ( $options & static::OPT_BACKUP_CONFIG )
            {
                $this->backupConfigFile( $mainConfigFile );
            }

            if ( $options & static::OPT_MERGE_CONFIG )
            {
                $configArray = $this->mergeConfig( $configArray, $mainConfigFile );
            }
        }

        file_put_contents( $mainConfigFile, Yaml::dump( $configArray, 7 ) );

        // Now generates environment config files
        foreach ( array_unique( $this->envs ) as $env )
        {
            $configFile = "$configPath/ezpublish_{$env}.yml";
            // Add the import statement for the root YAML file
            $envConfigArray = array(
                'imports' => array( array( 'resource' => 'ezpublish.yml' ) )
            );

            // File already exists, handle possible options
            if ( $this->fs->exists( $configFile ) )
            {
                if ( $options & static::OPT_BACKUP_CONFIG )
                {
                    $this->backupConfigFile( $configFile );
                }

                if ( $options & static::OPT_MERGE_CONFIG )
                {
                    $envConfigArrayStale = Yaml::parse( $configFile );
                    $hasImport = false;

                    // If previous config file already has an import section, check if we already have the right one.
                    if ( isset( $envConfigArrayStale['imports'] ) )
                    {
                        foreach ( $envConfigArrayStale['imports'] as $import )
                        {
                            if ( isset( $import['resource'] ) && $import['resource'] === 'ezpublish.yml' )
                            {
                                $hasImport = true;
                                break;
                            }
                        }
                    }

                    if ( !$hasImport )
                    {
                        $envConfigArray = $this->mergeConfig( $envConfigArrayStale, $envConfigArray );
                    }
                    else
                    {
                        $envConfigArray = $envConfigArrayStale;
                    }
                }
            }

            file_put_contents( $configFile, Yaml::dump( $envConfigArray, 7 ) );
        }

        $this->clearCache();
    }

    /**
     * Makes a backup copy of $configFile.
     *
     * @param $configFile
     */
    protected function backupConfigFile( $configFile )
    {
        if ( $this->fs->exists( $configFile ) )
            $this->fs->copy( $configFile, $configFile . '-' . date('Y-m-d_H-i-s') );
    }

    /**
     * Merges $configArray with settings from $configFile.
     *
     * @param array $configArray
     * @param string $configFile Path to config file to merge settings into. The file must be a valid YAML file.
     * @return array
     */
    protected function mergeConfig( array $configArray, $configFile )
    {
        $existingConfig = Yaml::parse( $configFile );
        if ( is_array( $existingConfig ) )
        {
            return array_merge_recursive( Yaml::parse( $configFile ), $configArray );
        }

        return $configArray;
    }

    /**
     * Clears the configuration cache.
     */
    protected function clearCache()
    {
        $oldCacheDirName = "{$this->cacheDir}_old";
        $this->fs->rename( $this->cacheDir, $oldCacheDirName );
        $this->fs->remove( $oldCacheDirName );
    }
}
