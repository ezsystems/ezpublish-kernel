<?php
/**
 * File containing the CLIHandler class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Legacy\Kernel;

use ezpKernelHandler,
    eZScript,
    RuntimeException,
    eZ\Publish\MVC\SiteAccess;

class CLIHandler implements ezpKernelHandler
{
    /**
     * @var \eZScript
     */
    protected $script;

    /**
     * Constructor
     *
     * @param array $settings Settings to pass to \eZScript constructor.
     * @param \eZ\Publish\MVC\SiteAccess $siteAccess
     */
    public function __construct( array $settings = array(), SiteAccess $siteAccess = null )
    {
        $this->script = eZScript::instance( $settings );
        $this->script->startup();
        if ( isset( $siteAccess ) )
            $this->script->setUseSiteAccess( $siteAccess->name );

        $this->script->initialize();
    }

    /**
     * Not supported by CLIHandler
     *
     * @throws \RuntimeException
     */
    public function run()
    {
        throw new RuntimeException( 'run() method is not supported by CLIHandler' );
    }

    /**
     * Runs a callback function in the legacy kernel environment.
     * This is useful to run eZ Publish 4.x code from a non-related context (like eZ Publish 5)
     *
     * @param \Closure $callback
     * @param bool $postReinitialize Default is true.
     *                               If set to false, the kernel environment will not be reinitialized.
     *                               This can be useful to optimize several calls to the kernel within the same context.
     * @return mixed The result of the callback
     */
    public function runCallback( \Closure $callback, $postReinitialize = true )
    {
        $return = $callback();
        $this->script->shutdown();
        if ( !$postReinitialize )
            $this->script->setIsInitialized( true );

        return $return;
    }

    /**
     * Not supported by CLIHandler
     *
     * @param bool $useExceptions
     * @throws \RuntimeException
     */
    public function setUseExceptions( $useExceptions )
    {
    }
}
