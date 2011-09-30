<?php
/**
 * Autoloader definition for eZ Publish
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base;
use ezcPhpGenerator,
    eZAutoloadGenerator;

/**
 * Provides the native autoload functionality for eZ Publish
 *
 * Use:
 * require 'ezp/Base/Autoloader.php'
 * spl_autoload_register( array( new ezp\Base\Autoloader(), 'load' ) );
 *
 * @uses ezcPhpGenerator To generate cache files.
 */
class Autoloader
{
    /**
     * @var array|null
     */
    protected $classes;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var string
     */
    const CACHE_FILE = 'var/cache/autoload.php';

    /**
     * Construct a autoload instance
     *
     * @param array $settings Misc autoload settings
     * @param array|null $ezpClasses Optional classes to autoload by hash lookup, for testing
     */
    public function __construct( array $settings, array $ezpClasses = null )
    {
        $this->classes = $ezpClasses;
        $this->settings = $settings + array(
            'ezc-path' => 'ezc/',
            'ezc-src-path' => '/',
            'ezc-loaded' => false,
            'ezc-prefixes' => array( 'base', 'persistent', 'configuration', 'php_generator' ),
            'repositories' => array( 'ezp' => 'ezp', 'ezx' => 'ezx' ),
            'development-mode' => false,
            'allow-kernel-override' => false,
        );
    }

    /**
     * Autoload eZ Publish, extension classes and lazy load ezcBase autoloader if needed
     *
     * @param  $className
     * @return bool|mixed
     */
    public function load( $className )
    {
        $className = ltrim( $className, '\\' );

        // Load class list array from cache or generate + save if it is not loaded
        if ( $this->classes === null )
        {
            if ( !$this->settings['development-mode'] && file_exists( self::CACHE_FILE ) )
            {
                $this->classes = include self::CACHE_FILE;
            }
            else
            {
                $this->classes = $this->generateClassesList();
                if ( !$this->settings['development-mode'] )
                    $this->saveClassesListCache( $this->classes );
            }
        }

        // Load class by autoload array
        if ( isset( $this->classes[$className] ) )
        {
            require $this->classes[$className];
            return true;
        }

        // Lazy load ezcBase if a ezc class is requested
        if ( !$this->settings['ezc-loaded'] && strncmp( $className, 'ezc', 3 ) === 0 )
        {
            $this->registerEzc();
            return $className === 'ezcBase';
        }

        // PSR-0 like autoloading of repositories namespaces if defined
        if ( empty( $this->settings['repositories'] ) )
            return;

        foreach ( $this->settings['repositories'] as $namespace => $subPath )
        {
            if ( strpos( $className, $namespace . '\\' ) === 0 )
            {
                $classNamePos = strripos( $className, '\\' );
                $namespacePart = str_replace(
                    array( './' . $namespace, '\\' ),
                    array( './' . $subPath, '/' ),
                    './' . substr( $className, 0, $classNamePos )
                );
                $classNamePart = str_replace( '_', '/', substr( $className, $classNamePos + 1 ) );
                $classPath = $namespacePart . '/' . $classNamePart . '.php';
                if ( !file_exists( $classPath ) )
                {
                    return false;
                }

                require $classPath;
                return true;
            }
        }
    }

    /**
     * Merges all autoload files and return result
     *
     * @return array
     */
    public function generateClassesList()
    {
        $ezpClasses = array();
        $ezpTestClasses = array();
        $ezpExtensionClasses = array();
        $ezpKernelOverrideClasses = array();

        // Load eZ Publish autoload files
        // @todo Temporary, should be forced as the file should be there
        if ( file_exists( 'autoload/ezp_kernel.php' ) )
        {
            $ezpClasses = require 'autoload/ezp_kernel.php';
        }

        if ( file_exists( 'var/autoload/ezp_extension.php' ) )
        {
            $ezpExtensionClasses = require 'var/autoload/ezp_extension.php';
        }

        if ( file_exists( 'var/autoload/ezp_tests.php' ) )
        {
            $ezpTestClasses = require 'var/autoload/ezp_tests.php';
        }

        if ( $this->settings['allow-kernel-override'] && file_exists( 'var/autoload/ezp_override.php' ) )
        {
            $ezpKernelOverrideClasses = require 'var/autoload/ezp_override.php';
        }

        $ezpClasses = $ezpKernelOverrideClasses + $ezpTestClasses + $ezpExtensionClasses + $ezpClasses;

        // Load eZ Component autoload files that are used often
        foreach ( $this->settings['ezc-prefixes'] as $prefix )
        {
            $tempOverrideClasses = include "{$this->settings['ezc-path']}autoload/{$prefix}_autoload.php";
            if ( $tempOverrideClasses )
            {
                $ezpClasses = $this->expandEzcClassList( $tempOverrideClasses ) + $ezpClasses;
            }
        }

        // Load API module autoload files
        foreach ( $this->settings['repositories'] as $subPath )
        {
            // @todo: Use configuration so class list only include activated extensions.
            // But then this loading will have to happen after configuration and siteaccess is loaded!
            foreach ( glob( "$subPath/*", GLOB_ONLYDIR ) as $path )
            {
                if ( !file_exists( "$path/autoload.php" ) )
                    continue;

                foreach ( include "$path/autoload.php" as $class => $relativePath )
                {
                    $ezpClasses[$class] = "$path/" . $relativePath;
                }
            }
        }
        return $ezpClasses;
    }

    /**
     * Expand an array of ezc class paths using $settings
     *
     * @param array $classes
     * @return array
     */
    protected function expandEzcClassList( array $classes )
    {
        foreach ( $classes as $class => $path )
        {
            list( $first, $second ) = explode( '/', $path, 2 );
            $classes[$class] = $this->settings['ezc-path'] . $first . $this->settings['ezc-src-path'] . $second;
        }
        return $classes;
    }

    /**
     * Save autoload cache file for override classes.
     *
     * @param array $classes
     * @return bool
     */
    protected function saveClassesListCache( $classes )
    {
        try
        {
            $generator = new ezcPhpGenerator( self::CACHE_FILE );
            $generator->appendComment( "This is auto generated hash of autoload override classes!" );
            $generator->appendValueAssignment( 'classes', $classes );
            $generator->appendCustomCode( 'return $classes;' );
            $generator->finish();
        }
        catch ( Exception $e )
        {
            // constructor     : ezcBaseFileNotFoundException or ezcBaseFilePermissionException
            // all other calls : ezcPhpGeneratorException
            trigger_error(
                'Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine(),
                E_USER_WARNING
            );
            return false;
        }
        return true;
    }

    /**
     * Register ezcBase autoloader based on optional constants defined in config.php
     */
    protected function registerEzc()
    {
        $this->settings['ezc-loaded'] = true;
        if ( class_exists( 'ezcBase', false ) )
        {
            if ( !in_array( array( 'ezcBase', 'autoload' ), spl_autoload_functions(), true ) )
                spl_autoload_register( array( 'ezcBase', 'autoload' ) );
            return true;
        }

        require $this->settings['ezc-path'] . 'Base' . $this->settings['ezc-src-path'] . 'base.php';
        spl_autoload_register( array( 'ezcBase', 'autoload' ) );
        return true;
    }

    /**
     * Delete autoload cache file for override classes.
     *
     * @return bool
     */
    public static function deleteClassesCache()
    {
        if ( file_exists( self::CACHE_FILE ) )
        {
            return unlink( self::CACHE_FILE );
        }
        return false;
    }

    /**
     * Resets the local, in-memory autoload cache.
     *
     * If the autoload arrays are extended during a requests lifetime, this
     * method must be called, to make them available.
     *
     * @param bool $clearFileCache Also clear on disk autoload file cache.
     * @return void
     */
    public function reset( $clearFileCache = true )
    {
        $this->classes = null;
        if ( $clearFileCache )
        {
            self::deleteClassesCache();
        }
    }

    /**
     * Shortcut to regenerate autoload files, also takes care of refreshing autoload cache
     */
    public function updateExtensionAutoloadArray()
    {
        $autoloadGenerator = new eZAutoloadGenerator();
        try
        {
            $autoloadGenerator->buildAutoloadArrays();
            $this->reset();
        }
        catch ( Exception $e )
        {
            trigger_error(
                'Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine(),
                E_USER_WARNING
            );
        }
    }
}

?>
