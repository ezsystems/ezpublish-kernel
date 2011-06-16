<?php
namespace ezp
{
    /**
     * Autoloader for
     * Enter description here ...
     * @author lolautruche
     *
     */
    class Autoloader
    {
        public static function register()
        {
            spl_autoload_register( array( new self, 'autoload' ) );
        }

        public function autoload( $className )
        {
            if ( strncmp( $className, 'ezp\\', 4 ) )
            {
                return false;
            }

            // Transform came case to underscore style for files
            // e.g. CamelCase => camel_case.php
            $fileName = strtolower( preg_replace( "/(?<=\\w)(?=[A-Z])/", "_$1", str_replace( '\\', DIRECTORY_SEPARATOR, $className ) ) ) . ".php";
            $fileName = str_replace( '\\', DIRECTORY_SEPARATOR, $fileName );

            /*
             * Particular cases : Interfaces and Exceptions
             * Class name must end with 'Interface' or 'Exception'
             * File must be placed in interfaces/ or exceptions/ directory for current namespace
             * File name must not contain "interface" or "exception"
             * e.g. ezp\Content\DomainObjectInterface => ezp/content/interfaces/domain_object.php
             */
            if ( self::classNameEndsWith( $className, "Interface" ) )
            {
                $fileName = dirname( $fileName ) . DIRECTORY_SEPARATOR . "interfaces" . DIRECTORY_SEPARATOR . str_replace( "_interface", "", basename( $fileName ));
            }
            else if ( self::classNameEndsWith( $className, "Exception" ) )
            {
                $fileName = dirname( $fileName ) . DIRECTORY_SEPARATOR . "exceptions" . DIRECTORY_SEPARATOR . str_replace( "_exception", "", basename( $fileName ));
            }

            return require $fileName;
        }

        /**
         * Checks if provided $className ends with $suffix
         * @param string $className
         * @param string  $suffix
         */
        final private function classNameEndsWith( $className, $suffix )
        {
            return strrpos( $className, $suffix ) === strlen( $className ) - strlen( $suffix );
        }
    }
}

namespace
{
    /**
     * simple PSR-0 like autoload function
     * Note: Avoid underscode in namespaces, this code threats them as DIRECTORY_SEPARATOR
     */

    function psrAutoload( $class )
    {
        if ( strncmp( $class, 'ezx\\', 4 ) && strncmp( $class, 'ezp\\base\\', 9 ) )
        {
            return false;
        }

        $fileName = './' . str_replace( array('\\', '_'), DIRECTORY_SEPARATOR, $class ) . '.php';
        return require( $fileName );
    }

    // Autoload for zeta components
    $useBundledComponents = defined( 'EZP_USE_BUNDLED_COMPONENTS' ) ? EZP_USE_BUNDLED_COMPONENTS === true : file_exists( 'lib/ezc' );
    if ( $useBundledComponents )
    {
        set_include_path( '.' . PATH_SEPARATOR . './lib/ezc' . PATH_SEPARATOR . get_include_path() );
        require 'Base/src/base.php';
        $baseEnabled = true;
    }
    else if ( defined( 'EZC_BASE_PATH' ) )
    {
        require EZC_BASE_PATH;
        $baseEnabled = true;
    }
    else
    {
        $baseEnabled = @include 'ezc/Base/base.php';
        if ( !$baseEnabled )
        {
            $baseEnabled = @include 'Base/src/base.php';
        }
    }

    define( 'EZCBASE_ENABLED', $baseEnabled );

    // Register autoloads
    spl_autoload_register( 'psrAutoload' );
    ezp\Autoloader::register();
    if ( EZCBASE_ENABLED )
    {
        spl_autoload_register( array( 'ezcBase', 'autoload' ) );
    }
}


