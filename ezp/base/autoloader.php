<?php
/**
 * Autoloader definition for eZ Publish
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage base
 */

/**
 * Provides the native autoload functionality for eZ Publish
 *
 * Use:
 * require 'ezp/base/autoloader.php'
 * spl_autoload_register( array( new ezp\base\Autoloader(), 'load' ) );
 *
 * @package ezp
 * @subpackage base
 * @uses \ezcPhpGenerator To generate cache files.
 */
namespace ezp\base;
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
     * @var bool|null Null if not loaded, true if loaded and false if tried to load but failed
     */
    protected static $ezcLoaded = null;

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
            'repositories' => array( 'ezp' => 'ezp',
                                     'ezx' => 'ezx' ),
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
        // Lazy load ezcBase if a ezc class is requested (needs to be first to avoid recursion)
        if ( !self::$ezcLoaded && strncmp( $className, 'ezc', 3 ) === 0 )
        {
            return self::$ezcLoaded = self::registerEzc();
        }

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
            return require( $this->classes[$className] );
        }

        // Fallback to load by convention if class name starts with ezp\ or ezx\ namespace
         if ( strncmp( $className, 'ezp\\', 4 ) !== 0 && strncmp( $className, 'ezx\\', 4 ) !== 0  )
             return false;

        // Transform camel case to underscore style for text, e.g. CamelCase => camel_case.php
        static $toLowerCaseWithUnderscore = array(
                'A' => '_a',    'B' => '_b',    'C' => '_c',    'D' => '_d',    'E' => '_e',    'F' => '_f',
                'G' => '_g',    'H' => '_h',    'I' => '_i',    'J' => '_j',    'K' => '_k',    'L' => '_l',
                'M' => '_m',    'N' => '_n',    'O' => '_o',    'P' => '_p',    'Q' => '_q',    'R' => '_r',
                'S' => '_s',    'T' => '_t',    'U' => '_u',    'V' => '_v',    'W' => '_w',    'X' => '_x',
                'Y' => '_y',    'Z' => '_z'
                );
        $fileName = str_replace( array( '\\', '/_' ), '/', strtr( $className, $toLowerCaseWithUnderscore ) ) . '.php';

        /**
         * Particular cases : Interfaces and Exceptions
         * If class name ends with 'Interface' or 'Exception' file must be placed in interfaces/ or exceptions/
         * directory for current namespace and file name must not contain "interface" or "exception".
         * e.g. ezp\Content\DomainObjectInterface => ezp/content/interfaces/domain_object.php
         */
        if ( strrpos( $className, 'Interface' ) === strlen( $className ) - 9 )
        {
            $fileName = dirname( $fileName ) . '/interfaces/' . str_replace( '_interface', '', basename( $fileName ));
        }
        else if ( strrpos( $className, 'Exception' ) === strlen( $className ) - 9 )
        {
            $fileName = dirname( $fileName ) . '/exceptions/' . str_replace( '_exception', '', basename( $fileName ));
        }
        require $fileName;
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

        if ( file_exists( 'autoload/ezp_kernel.php' ) )// @todo Temporary, should be forced as the file should be there
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

        return $ezpKernelOverrideClasses + $ezpTestClasses + $ezpExtensionClasses + $ezpClasses;
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
            $generator = new \ezcPhpGenerator( self::CACHE_FILE );
            $generator->appendComment( "This is auto generated hash of autoload override classes!" );
            $generator->appendValueAssignment( 'classes', $classes );
            $generator->appendCustomCode( 'return $classes;' );
            $generator->finish();
        }
        catch ( Exception $e )
        {
            // constructor     : ezcBaseFileNotFoundException or ezcBaseFilePermissionException
            // all other calls : ezcPhpGeneratorException
            trigger_error( 'Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine(),
                           E_USER_WARNING );
            return false;
        }
        return true;
    }

    /**
     * Register ezcBase autoloader based on optional constants defined in config.php
     */
    protected function registerEzc()
    {

        if ( self::$ezcLoaded !== null )
            return self::$ezcLoaded;

        if ( class_exists( 'ezcBase', false ) )
            return true;

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
        $autoloadGenerator = new \eZAutoloadGenerator();
        try
        {
            $autoloadGenerator->buildAutoloadArrays();
            $this->reset();
        }
        catch ( Exception $e )
        {
            trigger_error( 'Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine(),
                           E_USER_WARNING );
        }
    }
}

?>
