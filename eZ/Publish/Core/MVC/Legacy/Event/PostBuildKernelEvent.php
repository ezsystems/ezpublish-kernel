<?php
/**
 * File containing the PostBuildKernelWebHandlerEvent class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Event;

use Symfony\Component\EventDispatcher\Event;
use eZ\Publish\Core\MVC\Legacy\Kernel as LegacyKernel;
use ezpKernelHandler;

/**
 * This event is sent just after the LegacyKernel has been built.
 */
class PostBuildKernelEvent extends Event
{
    /**
     * @var \eZ\Publish\Core\MVC\Legacy\Kernel
     */
    private $legacyKernel;

    /**
     * @var \ezpKernelHandler
     */
    private $kernelHandler;

    public function __construct( LegacyKernel $legacyKernel, ezpKernelHandler $handler )
    {
        $this->legacyKernel = $legacyKernel;
        $this->kernelHandler = $handler;
    }

    /**
     * @return \eZ\Publish\Core\MVC\Legacy\Kernel
     */
    public function getLegacyKernel()
    {
        return $this->legacyKernel;
    }

    /**
     * @return \ezpKernelHandler
     */
    public function getKernelHandler()
    {
        return $this->kernelHandler;
    }
}
