<?php
/**
 * Contains: PSR-0 [Class]Loader Class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base;

/**
 * Provides PSR-0 [Class]Loader
 *
 * Use:
 * require 'eZ/Publish/Core/Base/ClassLoader.php'
 * spl_autoload_register( array( new eZ\Publish\Core\Base\ClassLoader(
 *     array(
 *         'Vendor\\Module' => 'Vendor/Module'
 *     )
 * ), 'load' ) );
 */
class ClassLoader
{
    /**
     * @var array Contains namespace/class prefix as key and sub path as value
     */
    protected $paths;

    /**
     * Hash indexed by class FQN. Value is the file path (should be absolute)
     *
     * @var array
     */
    protected $classMap;

    /**
     * @var null|string
     */
    protected $legacyDir;

    /**
     * @var array
     */
    protected $legacyClassMap;

    /**
     * Construct a loader instance
     *
     * @param array $paths Containing class/namespace prefix as key and sub path as value
     * @param array $classMap
     * @param string|null $legacyDir
     */
    public function __construct( array $paths, array $classMap = array(), $legacyDir = null )
    {
        $this->paths = $paths;
        $this->classMap = $classMap;
        $this->legacyDir = $legacyDir;
    }

    /**
     * Loads classes/interfaces following PSR-0 naming and class map
     *
     * @param string $className
     * @param boolean $returnFileName For testing, returns file name instead of loading it
     *
     * @return null|boolean|string Null if no match is found, bool if match and found/not found,
     *                             string if $returnFileName is true.
     */
    public function load( $className, $returnFileName = false )
    {
        if ( $className[0] === '\\' )
            $className = substr( $className, 1 );

        // Try to match against the class map
        if ( isset( $this->classMap[$className] ) )
        {
            if ( $returnFileName )
                return $this->classMap[$className];

            require $this->classMap[$className];
            return true;
        }

        // Try to match against PSR-0 namespace map
        $pearMode = stripos( $className, '_' ) !== false;
        foreach ( $this->paths as $prefix => $path )
        {
            if ( strpos( $className, $prefix ) !== 0 )
                continue;

            if ( $pearMode ) // PSR-0 PEAR compatibility mode
            {
                $lastNsPos = strripos( $className, '\\' );
                $fileName = $path;

                // Replacing '\' to '/' in namespace part
                $fileName .= str_replace(
                    '\\',
                    DIRECTORY_SEPARATOR,
                    substr( $className, 0, $lastNsPos )
                ) . DIRECTORY_SEPARATOR;

                // Replacing '_' to '/' in className part and append '.php'
                $fileName .= str_replace( '_', DIRECTORY_SEPARATOR, substr( $className, $lastNsPos + 1 ) ) . '.php';
            }
            else // PSR-0 NS strict mode
            {
                $fileName = $path .
                    str_replace( '\\', DIRECTORY_SEPARATOR, $className ) .
                    '.php';
            }

            if ( $returnFileName )
                return $fileName;

            if ( ( $fileName = stream_resolve_include_path( $fileName ) ) === false )
                continue;

            require $fileName;
            return true;
        }

        // If legacy dir is provided, then try to load using legacy class map
        if ( $this->legacyDir !== null )
        {
            // Lazy load legacy class map
            if ( $this->legacyClassMap === null )
            {
                $this->legacyClassMap = self::getEzpLegacyClassMap( $this->legacyDir );
            }

            // Load legacy class if it exists
            if ( isset( $this->legacyClassMap[$className] ) )
            {
                if ( $returnFileName )
                    return $this->legacyDir . '/' . $this->legacyClassMap[$className];

                require $this->legacyDir . '/' . $this->legacyClassMap[$className];
                return true;
            }
        }

        return false;
    }

    /**
     * Merges all eZ Publish 4.x autoload files and return result
     *
     * @param string $legacyDir
     *
     * @return array
     */
    protected static function getEzpLegacyClassMap( $legacyDir )
    {
        $ezpKernelClasses = require "{$legacyDir}/autoload/ezp_kernel.php";
        if ( file_exists( "{$legacyDir}/var/autoload/ezp_extension.php" ) )
            $ezpExtensionClasses = require "{$legacyDir}/var/autoload/ezp_extension.php";
        else
            $ezpExtensionClasses = array();

        if ( file_exists( "{$legacyDir}/var/autoload/ezp_tests.php" ) )
            $ezpTestClasses = require "{$legacyDir}/var/autoload/ezp_tests.php";
        else
            $ezpTestClasses = array();

        if ( file_exists( "{$legacyDir}/var/autoload/ezp_override.php" ) )
            $ezpKernelOverrideClasses = require "{$legacyDir}/var/autoload/ezp_override.php";
        else
            $ezpKernelOverrideClasses = array();

        return $ezpKernelOverrideClasses + $ezpTestClasses + $ezpExtensionClasses + $ezpKernelClasses;
    }
}
