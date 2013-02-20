<?php
/**
 * File containing the CLIHandler class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Kernel;

use ezpKernelHandler;
use eZScript;
use eZINI;
use ezpSessionHandlerSymfony;
use eZSession;
use RuntimeException;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CLIHandler implements ezpKernelHandler
{
    /**
     * @var \eZScript
     */
    protected $script;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    protected $sessionSettings;

    /**
     * Path to legacy script to run.
     * e.g. bin/php/eztc.php
     *
     * @var string
     */
    protected $embeddedScriptPath;

    /**
     * Constructor
     *
     * Additional valid settings for $settings :
     * - injected-settings : INI settings override
     *
     * @param array $settings Settings to pass to eZScript constructor.
     * @param \eZ\Publish\Core\MVC\Symfony\SiteAccess $siteAccess
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct( array $settings = array(), SiteAccess $siteAccess = null, ContainerInterface $container = null )
    {
        $this->container = $container;
        if ( isset( $settings['injected-settings'] ) )
        {
            $injectedSettings = array();
            foreach ( $settings["injected-settings"] as $keySetting => $injectedSetting )
            {
                list( $file, $section, $setting ) = explode( "/", $keySetting );
                $injectedSettings[$file][$section][$setting] = $injectedSetting;
            }
            // those settings override anything else in local .ini files and
            // their overrides
            eZINI::injectSettings( $injectedSettings );
            unset( $settings['injected-settings'] );
        }

        if ( isset( $settings['session'] ) )
        {
            $this->sessionSettings = $settings['session'];
        }

        $this->script = eZScript::instance( $settings );
        $this->script->startup();
        if ( isset( $siteAccess ) )
            $this->script->setUseSiteAccess( $siteAccess->name );
    }

    /**
     * Runs a legacy script.
     *
     * @throws \RuntimeException
     */
    public function run()
    {
        if ( !isset( $this->embeddedScriptPath ) )
            throw new RuntimeException( 'No legacy script to run has been passed. Cannot run, aborting.' );

        if ( !file_exists( $this->embeddedScriptPath ) )
            throw new RuntimeException( 'Passed legacy script does not exist. Please provide the correct script path, relative to the legacy root.' );

        $this->sessionInit();
        // Exposing $argv to embedded script
        $argv = $_SERVER['argv'];
        include $this->embeddedScriptPath;
    }

    private function sessionInit()
    {
        $sfHandler = new ezpSessionHandlerSymfony(
            $this->sessionSettings['has_previous']
            || $this->sessionSettings['started']
        );
        $sfHandler->setStorage( $this->sessionSettings['storage'] );
        eZSession::init(
            $this->sessionSettings['name'],
            $this->sessionSettings['started'],
            $this->sessionSettings['namespace'],
            $sfHandler
        );
    }

    /**
     * Runs a callback function in the legacy kernel environment.
     * This is useful to run eZ Publish 4.x code from a non-related context (like eZ Publish 5)
     *
     * @param \Closure $callback
     * @param boolean $postReinitialize Default is true.
     *                               If set to false, the kernel environment will not be reinitialized.
     *                               This can be useful to optimize several calls to the kernel within the same context.
     * @return mixed The result of the callback
     */
    public function runCallback( \Closure $callback, $postReinitialize = true )
    {
        if ( !$this->script->isInitialized() )
            $this->script->initialize();

        $return = $callback();
        $this->script->shutdown();
        if ( !$postReinitialize )
            $this->script->setIsInitialized( true );

        return $return;
    }

    /**
     * Not supported by CLIHandler
     *
     * @param boolean $useExceptions
     *
     * @throws \RuntimeException
     */
    public function setUseExceptions( $useExceptions )
    {
    }

    /**
     * Reinitializes the kernel environment.
     *
     * @return void
     */
    public function reInitialize()
    {
        $this->script->setIsInitialized( false );
    }

    /**
     * Checks whether the kernel handler has the Symfony Dependency Injection
     * container or not.
     *
     * @return boolean
     */
    public function hasServiceContainer()
    {
        return isset( $this->container );
    }

    /**
     * Returns the Symfony Dependency Injection container if it has been injected,
     * otherwise returns null.
     *
     * @return \Symfony\Component\DependencyInjection\ContainerInterface|null
     */
    public function getServiceContainer()
    {
        return $this->container;
    }

    /**
     * Injects path to script to run in legacy context (relative to legacy root).
     *
     * @param string $scriptPath
     */
    public function setEmbeddedScriptPath( $scriptPath )
    {
        $this->embeddedScriptPath = $scriptPath;
    }
}
