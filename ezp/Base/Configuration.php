<?php
/**
 * File containing the Configuration class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 * @uses ezcPhpGenerator To generate INI cache
 */

namespace ezp\Base;
use ezp\Base\Exception\BadConfiguration,
    ezcPhpGenerator;

/**
 * Configuration class with focus on performance
 *
 * A configuration class with override setting support that uses parsers to deal with
 * files so you can support ini/yaml/xml/json given it is defined when setting up the class.
 *
 * Class is quite static and uses multi-singletons for instances pr module.
 *
 * By default values are cached to a raw php files and files are not read again unless
 * development mode is on and some file has been removed or modified since cache was created.
 *
 *
 * Setup:
 *
 *     // Setup global configuration that needs to be defined before loading setting files
 *     Configuration::setGlobalConfigurationData( array(
 *         'base' => array(
 *             'autoload' => array(
 *                 // Optional bool value for global development mode setting
 *                 // Default: false, checks modified time and if files exist on every request if true
 *                 'development-mode' => true,
 *             ),
 *             'configuration' => array(
 *                 // Optional bool value to specify if cache files should be used
 *                 // Default: true, parses files on every request if false
 *                 'use-cache' => false,
 *                 // Required list of parser classes where key is file suffix
 *                 'parsers' => array(
 *                     '.ini' => 'ezp\\Base\\Configuration\\Parser\\Ini',
 *                     '.ini.append.php' => 'ezp\\Base\\Configuration\\Parser\\Ini',
 *                 ),
 *             ),
 *         )
 *     ) );
 *     // Specify additional locations that might contain settings (hence you don't have to check if folder exist)
 *     Configuration::setGlobalDirs( array( 'ezp/Base/settings/' ), 'modules' );
 *
 *
 * Usage:
 *
 *     // Default value on $module is 'base', so this will parse base.ini[.append.php] files given setup above
 *     $bool = Configuration::getInstance()->get( 'autoload', 'development-mode' );
 *
 *
 * Usage2:
 *
 *     $array = Configuration::getInstance( 'content' )->get( 'fields', 'Type' );
 *
 *
 * @uses \ezcPhpGenerator When generating cache files.
 */
class Configuration extends Override
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
     * File permissions for ini cache files
     *
     * @var int
     */
    public static $filePermission = 0644;

    /**
     * Directory permissions for ini cache files
     *
     * @var int
     */
    public static $dirPermission = 0755;

    /**
     * The global configuration path array, scoped in the order they should be parsed
     *
     * @var array
     */
    protected static $globalPaths = array(
        'base' => array( 'settings/' ),
        'modules' => array(),
        'siteaccess' => array(),
        'global' => array( 'settings/override/' ),
    );

    /**
     * The global configuration data (overrides all other configuration)
     *
     * @var array
     */
    protected static $globalConfigurationData = array();

    /**
     * List of instances pr settings type (array key).
     *
     * @var Configuration[]
     */
    protected static $instance = array();

    /**
     * The instance module name, set by {@link self::__construct()}
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
     * Constructor, please use {@link self::getInstance()} unless you have special needs as this does not reuse existing instance
     * and does not automatically load configuration data from source / cache.
     *
     * @param string $moduleName The name of the module (and in case of ini files, same as ini filename w/o suffix)
     * @param bool $referenceGlobalPaths Tells construct to assign global paths by reference or not, if true then changes to global paths will affect
     *             paths on this object directly (default: false, in most cases you should use getInstance if you want this to be enabled)
     */
    public function __construct( $moduleName = 'base', $referenceGlobalPaths = false )
    {
        $this->initPaths( $referenceGlobalPaths );
        $this->moduleName = $moduleName;
    }

    /**
     * Get configuration instance and load it automatically
     *
     * @uses load() Used the first time an instance is created
     * @param string $moduleName The name of the module (w/o .ini suffix as we would want to support other formats in the future)
     * @return Configuration
     */
    public static function getInstance( $moduleName = 'base' )
    {
        if ( !isset( self::$instance[ $moduleName ] ) )
        {
            self::$instance[ $moduleName ] = new self( $moduleName, true );
            self::$instance[ $moduleName ]->load();
        }
        return self::$instance[ $moduleName ];
    }

    /**
     * Tells if (global) development is turned on, using [autoload]\development-mode if set
     *
     * @return bool
     */
    protected static function developmentMode()
    {
        if ( isset( self::$globalConfigurationData['base']['autoload']['development-mode'] ) )
            return self::$globalConfigurationData['base']['autoload']['development-mode'];
        return false;
    }

    /**
     * Reset instance list, in most cases it should be enough to call reloadAll
     *
     * @param string|null $moduleName Reset a specific instance if string
     */
    public static function reset( $moduleName = null )
    {
        if ( $moduleName === null )
        {
            self::$instance = array();
            self::$globalPathsHash = '';
        }
        else
            unset( self::$instance[ $moduleName ] );
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
     * Reload cache data conditionally if path hash has changed on all global instances
     */
    public static function reloadAll()
    {
        foreach ( self::$instance as $instance )
        {
            $instance->reload();
        }
    }

    /**
     * Get global configuration data.
     *
     * @return array
     */
    public static function getGlobalConfigurationData()
    {
        return self::$globalConfigurationData;
    }

    /**
     * Set global configuration data.
     *
     * @param array $configurationData
     */
    public static function setGlobalConfigurationData( array $configurationData )
    {
        self::$globalConfigurationData = $configurationData;
    }

    /**
     * Load the configuration from cache or from source (if $useCache is false or there is no cache)
     *
     * @param bool|null $hasCache Lets you specify if there is a cache file, will check if null and $useCache is true
     * @param bool $useCache Will skip using cached config files (slow), when null depends on [ini]\use-cache setting
     */
    public function load( $hasCache = null, $useCache = null )
    {
        $cacheName = self::createCacheName( $this->moduleName, $this->pathsHash() );
        if ( $useCache === null )
        {
            $useCache =
                isset( self::$globalConfigurationData['base']['configuration']['use-cache'] )
                ? self::$globalConfigurationData['base']['configuration']['use-cache']
                : true;
        }

        if ( $hasCache === null && $useCache )
        {
            $hasCache = self::hasCache( $cacheName );
        }

        if ( $hasCache && $useCache )
        {
            $this->raw = self::readCache( $cacheName );
            $hasCache = $this->raw !== null;
        }

        if ( !$hasCache )
        {
            $sourceFiles = array();
            $configurationData = self::parse( $this->moduleName, $this->getDirs(), $sourceFiles );
            $this->raw = self::generateRawData( $this->pathsHash(), $configurationData, $sourceFiles, $this->getDirs() );

            if ( $useCache )
            {
                self::storeCache( $this->moduleName, $cacheName, $this->raw );
            }
        }

        // Merge global settings (not cached as they are runtime settings)
        if ( isset( self::$globalConfigurationData[ $this->moduleName ] ) )
        {
            foreach ( self::$globalConfigurationData[ $this->moduleName ] as $section => $settings )
            {
                if ( !isset( $this->raw['data'][$section] ) )
                {
                    $this->raw['data'][$section] = $settings;
                    continue;
                }

                foreach ( $settings as $setting => $value )
                {
                    $this->raw['data'][$section][$setting] = $value;
                }
            }
        }
    }

    /**
     * Create cache name.
     *
     * @param string $moduleName
     * @param string $configurationPathsHash
     * @return string
     */
    protected static function createCacheName( $moduleName, $configurationPathsHash )
    {
        return $moduleName . '-' . $configurationPathsHash;
    }

    /**
     * Check if cache file exists.
     *
     * @param string $cacheName As generated by {@link self::createCacheName()}
     * @return bool
     */
    protected static function hasCache( $cacheName )
    {
        return file_exists( self::CONFIG_CACHE_DIR . $cacheName . '.php' );
    }

    /**
     * Load cache file, use {@link self::hasCache()} to make sure it exists first!
     *
     * @param string $cacheName As generated by {@link self::createCacheName()}
     * @return array|null
     */
    protected static function readCache( $cacheName )
    {
        $cacheData = include self::CONFIG_CACHE_DIR . $cacheName . '.php';

        // Check that cache has
        if ( !isset( $cacheData['data'] ) || $cacheData['rev'] !== self::CONFIG_CACHE_REV )
        {
            return null;
        }

        // Check modified time if dev mode
        if ( self::developmentMode() )
        {
            $currentTime = time();
            foreach ( $cacheData['files'] as $inputFile )
            {
                $fileTime = file_exists( $inputFile ) ? filemtime( $inputFile ) : false;
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
     */
    protected static function generateRawData( $configurationPathsHash, array $configurationData, array $sourceFiles = array(), array $sourcePaths = array() )
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
     * @param string $moduleName
     * @param array $configurationPaths
     * @param array $sourceFiles ByRef value or source files that has been/is going to be parsed
     *                           files you pass in will not be checked if they exists.
     * @return array Data structure for parsed ini files
     * @throws ezp\Base\Exception\BadConfiguration If no parser have been defined
     */
    protected static function parse( $moduleName, array $configurationPaths, array &$sourceFiles )
    {
        if ( empty( self::$globalConfigurationData['base']['configuration']['parsers'] ) )
        {
            throw new BadConfiguration( 'base\[configuration]\parsers', 'Could not parse configuration files' );
        }
        $parsers = self::$globalConfigurationData['base']['configuration']['parsers'];
        foreach ( $configurationPaths as $scopeArray )
        {
            foreach ( $scopeArray as $settingsDir )
            {
                foreach ( $parsers as $suffix => $parser )
                {
                    $fileName = $settingsDir . $moduleName . $suffix;
                    if ( !isset( $sourceFiles[$fileName] ) && file_exists( $fileName ) )
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
            $parser = new $parsers[$suffix]( $fileName );
            $configurationFileData[$fileName] = $parser->parse( file_get_contents( $fileName ) );
        }

        // Post processing to unset array self::TEMP_INI_UNSET_VAR values as set by parser to indicate array clearing
        // and to merge configuration data from all configuration files
        foreach ( $configurationFileData as $file => $data )
        {
            foreach ( $data as $section => $sectionArray )
            {
                if ( !isset( $configurationData[$section] ) )
                    $configurationData[$section] = array();

                foreach ( $sectionArray as $setting => $settingValue )
                {
                    if ( isset( $settingValue[0] ) && $settingValue[0] === self::TEMP_INI_UNSET_VAR )
                    {
                        array_shift( $settingValue );
                        $configurationData[$section][$setting] = $settingValue;
                    }
                    else if ( isset( $configurationData[$section][$setting] ) && is_array( $settingValue ) )
                    {
                        $configurationData[$section][$setting] = array_merge( $configurationData[$section][$setting], $settingValue );
                    }
                    else
                    {
                        $configurationData[$section][$setting] = $settingValue;
                    }
                }
            }
        }

        return $configurationData;
    }

    /**
     * Store cache file, overwrites any existing file
     *
     * @param string $moduleName
     * @param string $cacheName As generated by {@link self::createCacheName()}
     * @param array $rawData As generated by {@link self::generateRawData()}
     */
    protected static function storeCache( $moduleName, $cacheName, array $rawData )
    {
        try
        {
            // Create ini dir if it does not exist
            if ( !file_exists( self::CONFIG_CACHE_DIR ) )
                mkdir( self::CONFIG_CACHE_DIR, self::$dirPermission, true );

            // Create cache hash
            $cachedFile = self::CONFIG_CACHE_DIR . $cacheName . '.php';

            // Store cache
            $generator = new ezcPhpGenerator( $cachedFile );
            $generator->appendComment( "This file is auto generated based on configuration files for '$moduleName' module. Do not edit!" );
            $generator->appendComment( "Time created (server time): " . date( DATE_RFC822, $rawData['created'] ) );
            $generator->appendEmptyLines();

            $generator->appendValueAssignment( 'cacheData', $rawData );
            $generator->appendCustomCode( 'return $cacheData;' );

            $generator->finish();

            // make sure file has correct file permissions
            chmod( $cachedFile, self::$filePermission );
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
     * Gets a configuration value, or null if not set.
     *
     * @param string $section The configuration section to get value for
     * @param string $key The configuration key to get value for
     * @param mixed $value value to return if setting is not defined.
     * @return bool Return true if section existed and was overwritten
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
     * @return bool Return true if setting exist
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
     * @return bool Return true if setting exist and has value
     */
    public function hasValue( $section, $key )
    {
        return !empty( $this->raw['data'][$section][$key] );
    }
}
