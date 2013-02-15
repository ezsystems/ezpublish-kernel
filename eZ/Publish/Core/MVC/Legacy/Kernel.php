<?php
/**
 * File containing the LegacyKernel class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy;

use ezpKernel;
use ezpKernelHandler;

/**
 * Class wrapping the legacy kernel
 */
class Kernel extends ezpKernel
{
    /**
     * Legacy root directory
     *
     * @var string
     */
    private $legacyRootDir;

    /**
     * Original webroot directory
     *
     * @var string
     */
    private $webRootDir;

    /**
     * @param \ezpKernelHandler $kernelHandler
     * @param string $legacyRootDir Must be a absolute dir
     * @param string $webRootDir Must be a absolute dir
     */
    public function __construct( ezpKernelHandler $kernelHandler, $legacyRootDir, $webRootDir )
    {
        $this->legacyRootDir = $legacyRootDir;
        $this->webRootDir = $webRootDir;

        $this->enterLegacyRootDir();
        parent::__construct( $kernelHandler );
        $this->leaveLegacyRootDir();
        $this->setUseExceptions( true );
    }

    /**
     * Changes the current working directory to the legacy root dir.
     * Calling this method is mandatory to use legacy kernel since a lot of resources in eZ Publish 4.x relatively defined.
     */
    public function enterLegacyRootDir()
    {
        chdir( $this->legacyRootDir );
    }

    /**
     * Leaves the legacy root dir and switches back to the initial webroot dir.
     */
    public function leaveLegacyRootDir()
    {
        chdir( $this->webRootDir );
    }

    /**
     * Runs current request through legacy kernel.
     *
     * @return array
     */
    public function run()
    {
        $this->enterLegacyRootDir();
        $return = parent::run();
        $this->leaveLegacyRootDir();
        return $return;
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
        $this->enterLegacyRootDir();
        $return = parent::runCallback( $callback, $postReinitialize );
        $this->leaveLegacyRootDir();
        return $return;
    }
}
