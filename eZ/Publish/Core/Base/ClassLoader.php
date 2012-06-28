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
 *     )[,
 *     eZ\Publish\Core\Base\ClassLoader::PSR_0_STRICT_MODE] // PSR-0 Strict mode (no PEAR compat)
 * ), 'load' ) );
 */
class ClassLoader
{
    /**
     * Mode for disabling PEAR autoloader compatibility (and PSR-0 compat)
     *
     * @var int
     */
    const PSR_0_STRICT_MODE = 1;

    /**
     * Mode to not check if file exists before loading class name that matches prefix
     *
     * @var int
     */
    const PSR_0_NO_FILECHECK = 2;

    /**
     * @var array Contains namespace/class prefix as key and sub path as value
     */
    protected $paths;

    /**
     * @var int
     */
    protected $mode;

    /**
     * @var array
     */
    protected $lazyClassLoaders;

    /**
     * Hash indexed by class FQN. Value is the file path (should be absolute)
     *
     * @var array
     */
    protected $classMap;

    /**
     * Construct a loader instance
     *
     * @param array $paths Containing class/namespace prefix as key and sub path as value
     * @param int $mode One or more of of the MODE constants, these are opt-in
     * @param \Closure[] $lazyClassLoaders Hash with class name prefix as key and callback as function to setup loader
     *          Example:
     *          array(
     *              'ezc' => function( $className ){
     *                  require 'ezc/Base/base.php';
     *                  spl_autoload_register( array( 'ezcBase', 'autoload' ) );
     *                  return true;
     *              }
     *          )
     *          Return true signals that autoloader was successfully registered and can be removed from $loders.
     */
    public function __construct( array $paths, $mode = 0, array $lazyClassLoaders = array() )
    {
        $this->paths = $paths;
        $this->mode = $mode;
        $this->lazyClassLoaders = $lazyClassLoaders;
        $this->classMap = array();
    }

    /**
     * Registers a class map for autoloading.
     * Key is the class FQN, value is the corresponding file path (should be absolute).
     *
     * @param array $classMap
     */
    public function registerClassMap( array $classMap )
    {
        $this->classMap += $classMap;
    }

    /**
     * Load classes/interfaces following PSR-0 naming
     *
     * @param string $className
     * @param bool $returnFileName For testing, returns file name instead of loading it
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
            require $this->classMap[$className];
            return true;
        }

        foreach ( $this->paths as $prefix => $subPath )
        {
            if ( strpos( $className, $prefix ) !== 0 )
                continue;

            if ( !( $this->mode & self::PSR_0_STRICT_MODE ) ) // Normal PSR-0 PEAR compat
            {
                $lastNsPos = strripos( $className, '\\' );
                $prefixLen = strlen( $prefix ) + 1;
                $fileName = $subPath . DIRECTORY_SEPARATOR;

                if ( $lastNsPos > $prefixLen )
                {
                    // Replacing '\' to '/' in namespace part
                    $fileName .= str_replace(
                        '\\',
                        DIRECTORY_SEPARATOR,
                        substr( $className, $prefixLen, $lastNsPos - $prefixLen )
                    ) . DIRECTORY_SEPARATOR;
                }

                // Replacing '_' to '/' in className part and append '.php'
                $fileName .= str_replace( '_', DIRECTORY_SEPARATOR, substr( $className, $lastNsPos + 1 ) ) . '.php';
            }
            else // Strict PSR mode
            {
                $fileName = $subPath . DIRECTORY_SEPARATOR .
                    str_replace( '\\', DIRECTORY_SEPARATOR, substr( $className , strlen( $prefix ) +1 ) ) .
                    '.php';
            }

            if ( !( $this->mode & self::PSR_0_NO_FILECHECK ) &&
                 ( $fileName = stream_resolve_include_path( $fileName ) ) === false )
                return false;


            if ( $returnFileName )
                return $fileName;

            require $fileName;
            return true;
        }

        if ( empty( $this->lazyClassLoaders ) )
            return null;

        // No match where found, see if we have any lazy loaded closures that should register other autoloaders
        foreach ( $this->lazyClassLoaders as $prefix => $callable )
        {
            if ( strpos( $className, $prefix ) !== 0 )
                continue;

            if ( $callable( $className ) )
                unset( $this->lazyClassLoaders[$prefix] );

            return true;
        }
    }
}
