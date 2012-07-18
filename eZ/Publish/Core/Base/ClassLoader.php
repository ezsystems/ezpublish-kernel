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
     * Construct a loader instance
     *
     * @param array $paths Containing class/namespace prefix as key and sub path as value
     * @param array $classMap
     */
    public function __construct( array $paths, array $classMap = array() )
    {
        $this->paths = $paths;
        $this->classMap = $classMap;
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

        $pearMode = stripos( $className, '_' ) !== false;
        foreach ( $this->paths as $prefix => $path )
        {
            if ( strpos( $className, $prefix ) !== 0 )
                continue;

            if ( $pearMode ) // PEAR compat code
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
            else // Strict PSR code
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
        return false;
    }
}
