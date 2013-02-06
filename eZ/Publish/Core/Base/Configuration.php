<?php
/**
 * File containing the Configuration class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 * @uses ezcPhpGenerator To generate INI cache
 */

namespace eZ\Publish\Core\Base;

use eZ\Publish\Core\Base\Configuration\Parser;
use eZ\Publish\Core\Base\Exceptions\BadConfiguration;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
use ezcPhpGenerator;

/**
 * Configuration instance class
 *
 * A configuration class with override setting support that uses parsers to deal with
 * files so you can support ini/yaml/xml/json given it is defined when setting up the class.
 *
 * By default values are cached to a raw php files and files are not read again unless
 * development mode is on and some file has been removed or modified since cache was created.
 *
 * @uses \ezcPhpGenerator When generating cache files.
 */
class Configuration
{
    /**
     * Constant path to directory for configuration cache
     *
     * @var string
     */
    const CONFIG_CACHE_DIR = 'var/cache/ini/';

    /**
     * Constant string used as a temporary unset variable during ini parsing
     *
     * @var string
     */
    const TEMP_INI_UNSET_VAR = '__UNSET__';

    /**
     * Constant integer to check against configuration cache format revision
     *
     * @var int
     */
    const CONFIG_CACHE_REV = 3;

    /**
     * The instance path array, scoped in the order they should be parsed
     *
     * @var array
     */
    private $paths = array();

    /**
     * The instance configuration path array md5 hash, for use in cache names.
     * Empty if it needs to be regenerated
     *
     * @var string
     */
    private $pathsHash = '';

    /**
     * The instance module name, set by {@link __construct()}
     *
     * @var string
     */
    protected $moduleName = null;

    /**
     * The in memory representation of the current raw configuration data.
     *
     * @var null|array
     */
    protected $raw = null;

    /**
     * Global settings (see config.php-RECOMMENDED)
     *
     * @var array
     */
    protected $globalConfiguration;

    /**
     * @var int
     */
    protected $filePermission = 0644;

    /**
     * @var int
     */
    protected $dirPermission = 0755;

    /**
     * Create instance of Configuration
     *
     * @param string $moduleName The name of the module (and in case of ini files, same as ini filename w/o suffix)
     * @param array $paths Paths to look for settings in.
     * @param array $globalConfiguration Global settings for module
     */
    public function __construct( $moduleName, array $paths, array $globalConfiguration )
    {
        $this->moduleName = $moduleName;
        $this->paths = $paths;
        $this->globalConfiguration = $globalConfiguration;

        if ( isset( $globalConfiguration['base']['Configuration']['CacheFilePermission'] ) )
            $this->filePermission = $globalConfiguration['base']['Configuration']['CacheFilePermission'];

        if ( isset( $globalConfiguration['base']['Configuration']['CacheDirPermission'] ) )
            $this->dirPermission = $globalConfiguration['base']['Configuration']['CacheDirPermission'];
    }

    /**
     * Get raw instance override path list data.
     *
     * @throws InvalidArgumentValue If scope has wrong value
     * @param string $scope See {@link $globalPaths} for scope values (first level keys)
     *
     * @return array
     */
    public function getDirs( $scope = null )
    {
        if ( $scope === null )
            return $this->paths;
        if ( !isset( $this->paths[$scope] ) )
            throw new InvalidArgumentValue( 'scope', $scope, get_class( $this ) );

        return $this->paths[$scope];
    }

    /**
     * Get cache hash based on override dirs
     *
     * @return string md5 hash
     */
    protected function pathsHash()
    {
        if ( $this->pathsHash === '' )
        {
            $this->pathsHash = md5( serialize( $this->paths ) );
        }
        return $this->pathsHash;
    }

    /**
     * Reload cache data conditionally if path hash has changed on current instance
     */
    public function reload()
    {
        if ( !isset( $this->raw['hash'] ) || $this->raw['hash'] !== $this->pathsHash() )
            $this->load();
    }

    /**
     * Loads the configuration from cache or from source (if $useCache is false or there is no cache)
     *
     * @param boolean|null $hasCache Lets you specify if there is a cache file, will check if null and $useCache is true
     * @param boolean $useCache Will skip using cached config files (slow), when null depends on [ini]\use-cache setting
     */
    public function load( $hasCache = null, $useCache = null )
    {
        $cacheName = $this->createCacheName( $this->pathsHash() );
        if ( $useCache === null )
        {
            $useCache =
                isset( $this->globalConfiguration['base']['Configuration']['UseCache'] )
                ? $this->globalConfiguration['base']['Configuration']['UseCache']
                : false;
        }

        if ( $hasCache === null && $useCache )
        {
            $hasCache = $this->hasCache( $cacheName );
        }

        if ( $hasCache && $useCache )
        {
            $this->raw = $this->readCache( $cacheName );
            $hasCache = $this->raw !== null;
        }

        if ( !$hasCache )
        {
            $sourceFiles = array();
            $configurationData = $this->parse( $this->getDirs(), $sourceFiles );
            $this->raw = $this->generateRawData( $this->pathsHash(), $configurationData, $sourceFiles, $this->getDirs() );

            if ( $useCache )
            {
                $this->storeCache( $cacheName, $this->raw );
            }
        }

        // Merge global settings (not cached as they are runtime settings)
        if ( !isset( $this->globalConfiguration[ $this->moduleName ] ) )
            return;

        foreach ( $this->globalConfiguration[ $this->moduleName ] as $section => $globalSettings )
        {
            if ( !isset( $this->raw['data'][$section] ) )
            {
                $this->raw['data'][$section] = $globalSettings;
                continue;
            }

            $this->setGlobalConfig( $globalSettings, $this->raw['data'][$section] );
        }
    }

    /**
     * Recursively set global configuration
     *
     * @param array $globalSettings
     * @param array $conf
     */
    protected function setGlobalConfig( array $globalSettings, array &$conf )
    {
        foreach ( $globalSettings as $key => $globalSetting )
        {
            if ( !empty( $conf[$key] ) && !is_numeric( $key ) && is_array( $conf[$key] ) && is_array( $globalSetting ) )
                $this->setGlobalConfig( $globalSetting, $conf[$key] );
            else
                $conf[$key] = $globalSetting;
        }
    }

    /**
     * Create cache name.
     *
     * @param string $configurationPathsHash
     *
     * @return string
     */
    protected function createCacheName( $configurationPathsHash )
    {
        return $this->moduleName . '-' . $configurationPathsHash;
    }

    /**
     * Check if cache file exists.
     *
     * @param string $cacheName As generated by {@link createCacheName()}
     *
     * @return boolean
     */
    protected function hasCache( $cacheName )
    {
        return is_file( self::CONFIG_CACHE_DIR . $cacheName . '.php' );
    }

    /**
     * Loads cache file, use {@link hasCache()} to make sure it exists first!
     *
     * @param string $cacheName As generated by {@link createCacheName()}
     *
     * @return array|null
     */
    protected function readCache( $cacheName )
    {
        $cacheData = include self::CONFIG_CACHE_DIR . $cacheName . '.php';

        // Check that cache has
        if ( !isset( $cacheData['data'] ) || $cacheData['rev'] !== self::CONFIG_CACHE_REV )
        {
            return null;
        }

        // Check modified time if dev mode
        if ( isset( $this->globalConfiguration['base']['Configuration']['DevelopmentMode'] )
          && $this->globalConfiguration['base']['Configuration']['DevelopmentMode'] )
        {
            $currentTime = time();
            foreach ( $cacheData['files'] as $inputFile )
            {
                $fileTime = is_file( $inputFile ) ? filemtime( $inputFile ) : false;
                // Refresh cache & input files if file is gone
                if ( $fileTime === false )
                {
                    return null;
                }
                if ( $fileTime > $currentTime )
                {
                    trigger_error( __METHOD__ . ': Input file "' . $inputFile . '" has a timestamp higher then current time, ignoring to avoid infinite recursion!', E_USER_WARNING );
                }
                // Refresh cache if file has been changed
                else if ( $fileTime > $cacheData['created'] )
                {
                    return null;
                }
            }
        }
        return $cacheData;
    }

    /**
     * Generate raw data for use in cache
     *
     * @param string $configurationPathsHash
     * @param array $configurationData
     * @param array $sourceFiles Optional, stored in cache to be able to check modified time in future devMode
     * @param array $sourcePaths Optional, stored in cache to be able to debug it more easily
     *
     * @return array
     */
    protected function generateRawData( $configurationPathsHash, array $configurationData, array $sourceFiles = array(), array $sourcePaths = array() )
    {
        return array(
            'hash' => $configurationPathsHash,
            'paths' => $sourcePaths,
            'files' => $sourceFiles,
            'data' => $configurationData,
            'created' => time(),
            'rev' => self::CONFIG_CACHE_REV,
        );
    }

    /**
     * Parse configuration files
     *
     * Uses configured parsers to do the file parsing pr file, and then merges the result from them and:
     * - Handles array clearing
     * - Handles section extends ( "Section:Base" extends "Base" )
     *
     * @param array $configurationPaths
     * @param array $sourceFiles ByRef value or source files that has been/is going to be parsed
     *                           files you pass in will not be checked if they exists.
     * @throws \eZ\Publish\Core\Base\Exceptions\BadConfiguration If no parser have been defined
     *
     * @return array Data structure for parsed ini files
     */
    protected function parse( array $configurationPaths, array &$sourceFiles )
    {
        if ( empty( $this->globalConfiguration['base']['Configuration']['Parsers'] ) )
        {
            throw new BadConfiguration( 'base\[Configuration]\Parsers', 'Could not parse configuration files' );
        }
        $parsers = $this->globalConfiguration['base']['Configuration']['Parsers'];
        foreach ( $configurationPaths as $scopeArray )
        {
            foreach ( $scopeArray as $settingsDir )
            {
                foreach ( $parsers as $suffix => $parser )
                {
                    $fileName = $settingsDir . $this->moduleName . $suffix;
                    if ( !isset( $sourceFiles[$fileName] ) && is_file( $fileName ) )
                    {
                        $sourceFiles[$fileName] = $suffix;
                    }
                }
            }
        }

        // No source files, no configuration
        if ( empty( $sourceFiles ) )
        {
            return array();
        }

        $configurationData = array();
        $configurationFileData = array();
        foreach ( $sourceFiles as $fileName => $suffix )
        {
            if ( !$parsers[$suffix] instanceof Parser )
                $parsers[$suffix] = new $parsers[$suffix]( $this->globalConfiguration['base']['Configuration'] );

            $configurationFileData[$fileName] = $parsers[$suffix]->parse( $fileName, file_get_contents( $fileName ) );
        }

        // Post processing to unset array self::TEMP_INI_UNSET_VAR values as set by parser to indicate array clearing
        // and to merge configuration data from all configuration files
        $extendedConfigurationFileData = array();
        foreach ( $configurationFileData as $fileName => $data )
        {
            foreach ( $data as $section => $sectionArray )
            {
                // Leave settings that extend others for second pass, key by depth
                if ( ( $count = substr_count( $section, ':' ) ) !== 0 )
                {
                    $extendedConfigurationFileData[$count][$fileName][$section] = $sectionArray;
                    continue;
                }

                if ( !isset( $configurationData[$section] ) )
                    $configurationData[$section] = array();

                $this->recursiveArrayClearing( $sectionArray, $configurationData[$section] );
            }
        }

        // Second pass post processing dealing with settings that extends others
        ksort( $extendedConfigurationFileData, SORT_NUMERIC );
        foreach ( $extendedConfigurationFileData as $configurationFileData )
        {
            foreach ( $configurationFileData as $data )
            {
                foreach ( $data as $section => $sectionArray )
                {
                    if ( !isset( $configurationData[$section] ) )
                    {
                        $parent = substr( $section, stripos( $section, ':' ) + 1 );
                        if ( isset( $configurationData[$parent] ) )
                            $configurationData[$section] = $configurationData[$parent];
                        else
                            $configurationData[$section] = array();
                    }

                    $this->recursiveArrayClearing( $sectionArray, $configurationData[$section] );
                }
            }
        }

        return $configurationData;
    }

    /**
     * Recursively clear array values
     *
     * @param array $iniArray
     * @param array|null $configurationPiece
     */
    protected function recursiveArrayClearing( array $iniArray, &$configurationPiece )
    {
        foreach ( $iniArray as $setting => $settingValue )
        {
            if ( isset( $settingValue[0] ) && $settingValue[0] === self::TEMP_INI_UNSET_VAR )
            {
                array_shift( $settingValue );
                $configurationPiece[$setting] = $settingValue;
            }
            else if ( is_array( $settingValue ) )
            {
                $this->recursiveArrayClearing( $settingValue, $configurationPiece[$setting] );
            }
            else
            {
                $configurationPiece[$setting] = $settingValue;
            }
        }

    }

    /**
     * Store cache file, overwrites any existing file
     *
     * @param string $cacheName As generated by {@link createCacheName()}
     * @param array $rawData As generated by {@link generateRawData()}
     */
    protected function storeCache( $cacheName, array $rawData )
    {
        try
        {
            // Create ini dir if it does not exist
            if ( !is_dir( self::CONFIG_CACHE_DIR ) )
            {
                mkdir( self::CONFIG_CACHE_DIR, $this->dirPermission, true );
            }

            // Create cache hash
            $cachedFile = self::CONFIG_CACHE_DIR . $cacheName . '.php';

            // Store cache
            $generator = new ezcPhpGenerator( $cachedFile );
            $generator->appendComment( "This file is auto generated based on configuration files for '{$this->moduleName}' module. Do not edit!" );
            $generator->appendComment( "Time created (server time): " . date( DATE_RFC822, $rawData['created'] ) );
            $generator->appendEmptyLines();

            $generator->appendValueAssignment( 'cacheData', $rawData );
            $generator->appendCustomCode( 'return $cacheData;' );

            $generator->finish();

            // make sure file has correct file permissions
            chmod( $cachedFile, $this->filePermission );
        }
        catch ( Exception $e )
        {
            // constructor     : ezcBaseFileNotFoundException or ezcBaseFilePermissionException
            // all other calls : ezcPhpGeneratorException
            trigger_error( __METHOD__ . ': '. $e->getMessage(), E_USER_WARNING );
        }
    }

    /**
     * Gets a configuration value, or $fallBackValue if undefined
     * Triggers warning if key is not set and $fallBackValue is null
     *
     * @param string $section The configuration section to get value for
     * @param string $key The configuration key to get value for
     * @param mixed $fallBackValue value to return if setting is undefined.
     *
     * @return mixed|null (null if key is undefined and no $fallBackValue is provided)
     */
    public function get( $section, $key, $fallBackValue = null )
    {
        if ( isset( $this->raw['data'][$section][$key] ) )
        {
            return $this->raw['data'][$section][$key];
        }
        if ( $fallBackValue === null )
        {
            trigger_error( __METHOD__ . " could not find {$this->moduleName}.ini\[{$section}]$key setting", E_USER_WARNING );
        }
        return $fallBackValue;
    }

    /**
     * Gets a configuration values for a section or $fallBackValue if undefined
     * Triggers warning if section is not set and $fallBackValue is null
     *
     * @param string $section The configuration section to get value for
     * @param mixed $fallBackValue value to return if section is undefined.
     *
     * @return array|null (null if key is undefined and no $fallBackValue is provided)
     */
    public function getSection( $section, $fallBackValue = null )
    {
        if ( isset( $this->raw['data'][$section] ) )
        {
            return $this->raw['data'][$section];
        }
        if ( $fallBackValue === null )
        {
            trigger_error( __METHOD__ . " could not find {$this->moduleName}.ini\[{$section}]setting", E_USER_WARNING );
        }
        return $fallBackValue;
    }

    /**
     * Gets all section and configuration value
     *
     * @return array
     */
    public function getAll()
    {
        return $this->raw['data'];
    }

    /**
     * Gets a configuration value, or null if not set.
     *
     * @param string $section The configuration section to get value for
     * @param string $key The configuration key to get value for
     * @param mixed $value value to return if setting is not defined.
     *
     * @return boolean Return true if section existed and was overwritten
     */
    public function set( $section, $key, $value = null )
    {
        if ( isset( $this->raw['data'][$section] ) )
        {
            $this->raw['data'][$section][$key] = $value;
            return true;
        }

        $this->raw['data'][$section] = array( $key => $value );
        return false;
    }

    /**
     * Checks if a configuration section and optionally key is set.
     *
     * @param string $section
     * @param string $key Optional, only checks if section exists if null
     *
     * @return boolean Return true if setting exist
     */
    public function has( $section, $key = null )
    {
        if ( $key === null )
            return isset( $this->raw['data'][$section] );

        return isset( $this->raw['data'][$section][$key] );
    }

    /**
     * Checks if a configuration section & key is set and has a value.
     * (ie. a check using !empty())
     *
     * @param string $section
     * @param string $key
     *
     * @return boolean Return true if setting exist and has value
     */
    public function hasValue( $section, $key )
    {
        return !empty( $this->raw['data'][$section][$key] );
    }
}
