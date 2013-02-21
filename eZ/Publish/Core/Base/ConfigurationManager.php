<?php
/**
 * File containing the ConfigurationManager class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 * @uses ezcPhpGenerator To generate INI cache
 */

namespace eZ\Publish\Core\Base;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;

/**
 * Configuration manager class
 *
 *
 * Setup:
 *
 *     // Setup global configuration that needs to be defined before loading setting files
 *     $manager = new ConfigurationManager( include 'config.php' );
 *
 * Usage:
 *
 *     // Default value on $module is 'base', so this will parse base.ini[.append.php] files given setup above
 *     $bool = $manager->getConfiguration( 'base' )->get( 'ClassLoader', 'Repositories' );
 *
 *
 * Usage2:
 *
 *     $array = $manager->getConfiguration( 'content' )->get( 'Fields', 'Type' );
 *
 *
 * @uses \ezcPhpGenerator When generating cache files.
 */
class ConfigurationManager
{
    /**
     * The global configuration path array, scoped in the order they should be parsed
     *
     * Usually something like:
     * array(
     *    'base' => array( 'settings/' ),
     *    'modules' => array(),
     *    'access' => array(),
     *    'global' => array( 'settings/override/' ),
     *  )
     * @var array
     */
    protected $globalPaths;

    /**
     * The global configuration data (overrides all other configuration)
     *
     * @var array
     */
    protected $globalConfiguration = array();

    /**
     * List of instances pr settings type (array key).
     *
     * @var Configuration[]
     */
    protected $instances = array();

    /**
     * Create a instance of Configuration Manager
     *
     * @param array $globalConfiguration
     */
    public function __construct(
        array $globalConfiguration,
        array $globalPaths = array(
            'base' => array( 'settings/' ),
            'modules' => array( 'eZ/Publish/Core/settings/' ),
            'access' => array(),
            'global' => array( 'settings/override/' ),
        )
    )
    {
        $this->globalConfiguration = $globalConfiguration;
        $this->globalPaths = $globalPaths;
    }

    /**
     * Get configuration instance and load it automatically
     *
     * @uses load() Used the first time an instance is created
     * @param string $moduleName The name of the module (w/o .ini suffix as we would want to support other formats in the future)
     *
     * @return \eZ\Publish\Core\Base\Configuration
     */
    public function getConfiguration( $moduleName = 'base' )
    {
        if ( !isset( $this->instances[ $moduleName ] ) )
        {
            $this->instances[ $moduleName ] = new Configuration(
                $moduleName,
                $this->globalPaths,
                $this->globalConfiguration
            );
            $this->instances[ $moduleName ]->load();
        }
        return $this->instances[ $moduleName ];
    }

    /**
     * Get global configuration data.
     *
     * @return array
     */
    public function getGlobalConfiguration()
    {
        return $this->globalConfiguration;
    }

    /**
     * Get raw global override path list data.
     *
     * @throws InvalidArgumentValue If scope has wrong value
     * @param string $scope See {@link $globalPaths} for scope values (first level keys)
     *
     * @return array
     */
    public function getGlobalDirs( $scope = null )
    {
        if ( $scope === null )
            return $this->globalPaths;
        if ( !isset( $this->globalPaths[$scope] ) )
            throw new InvalidArgumentValue( 'scope', $scope, __CLASS__ );

        return $this->globalPaths[$scope];
    }

    /**
     * Set raw global override path list data.
     *
     * Note: Full reset of Configuration instances are done when this function is called.
     *
     * @throws InvalidArgumentValue If scope has wrong value
     * @param array $paths
     * @param string $scope See {@link $globalPaths} for scope values (first level keys)
     *
     * @return boolean Return true if paths actually changed, and thus instances where reset.
     */
    public function setGlobalDirs( array $paths, $scope = null )
    {
        if ( $scope === null )
        {
            if ( $this->globalPaths === $paths )
                return false;
            $this->globalPaths = $paths;
        }
        else if ( !isset( $this->globalPaths[$scope] ) )
        {
            throw new InvalidArgumentValue( 'scope', $scope, get_class( $this ) );
        }
        else if ( $this->globalPaths[$scope] === $paths )
        {
            return false;
        }
        else
        {
            $this->globalPaths[$scope] = $paths;
        }

        $this->reset();
        return true;
    }

    /**
     * Reset instance list, in most cases it should be enough to call reloadAll
     *
     * @param string|null $moduleName Optionally Reset a specific instance if string
     */
    public function reset( $moduleName = null )
    {
        if ( $moduleName === null )
        {
            $this->instances = array();
        }
        else
            unset( $this->instances[ $moduleName ] );
    }
}
