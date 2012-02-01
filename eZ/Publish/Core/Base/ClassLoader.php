<?php
/**
 * Contains: PSR-0 ClassLoader Class
 *
 * @copyright Copyright (C) 2012 andrerom & eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Base;

/**
 * Provides PSR-0 ClassLoader
 *
 * Use:
 * require 'eZ/Publish/Core/Base/ClassLoader.php'
 * spl_autoload_register( array( new eZ\Publish\Core\Base\ClassLoader(
 *     array(
 *         'Vendor\\Module' => 'Vendor/Module'
 *     )[,
 *     eZ\Publish\Core\Base\ClassLoader::MODE_PSR_0_STRICT] // optional strict mode where underscore is ignored
 * ), 'load' ) );
 */
class ClassLoader
{
    /**
     * Mode for "PSR-0 strict", where underscore is ignored
     * @var int
     */
    const MODE_PSR_0_STRICT = 1;

    /**
     * Skip doing a file_exists() check on matching namespaces
     * @var int
     */
    const MODE_SKIP_FILE_CHECK = 2;

    /**
     * @var array
     */
    protected $repositories;

    /**
     * @var int
     */
    protected $mode;

    /**
     * Construct a autoload instance
     *
     * @param array $repositories containing namespace as key and path as value
     * @param int $mode One or more of of the MODE constance, these are opt-in to make class loader stricter
     */
    public function __construct( array $repositories, $mode = 0 )
    {
        $this->repositories = $repositories;
        $this->mode = $mode;
    }

    /**
     * Autoload classes following PSR-0 naming
     *
     * @param  $className
     * @return boolean
     */
    public function load( $className )
    {
        $className = ltrim( $className, '\\' );// PHP 5.3.1 issue
        foreach ( $this->repositories as $namespace => $subPath )
        {
            if ( stripos( $className, $namespace . '\\' ) !== 0 )
                continue;

            if ( $this->mode & self::MODE_PSR_0_STRICT )
            {
                // only replace namespace part to slash, but replace namespace with sub path if different
                if ( $namespace !== $subPath )
                    $file = $subPath . '/' . substr( strtr( $className, '\\', '/' ), strlen( $namespace ) +1 ) . '.php';
                else
                    $file = strtr( $className, '\\', '/' ) . '.php';
            }
            else // PSR-0
            {
                $classNamePos = strripos( $className, '\\' );
                $file = "$subPath/" .
                    // Replacing '\' to '/' in namespace part
                    substr( strtr( substr( $className, 0, $classNamePos ), '\\', '/' ), strlen( $namespace ) +1 ) .
                    // Appending class name + .php which corresponds to the filename
                    '/' . str_replace( '_', '/', substr( $className, $classNamePos + 1 ) ) . '.php';
            }

            if ( !($this->mode & self::MODE_SKIP_FILE_CHECK) && !file_exists( $file ) )
            {
                return false;
            }

            require $file;
            return true;
        }
    }
}

?>
