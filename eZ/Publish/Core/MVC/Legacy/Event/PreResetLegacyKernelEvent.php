<?php
/**
 * File containing the PreBuildKernelEvent class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Event;

use eZ\Publish\Core\MVC\Legacy\Kernel as LegacyKernel;
use Symfony\Component\EventDispatcher\Event;

/**
 * This event is triggered right before the initialization of the legacy kernel.
 * It allows to inject parameters into the legacy kernel through
 * the parameter bag.
 */
class PreResetLegacyKernelEvent extends Event
{
    /**
     * @var \eZ\Publish\Core\MVC\Legacy\Kernel
     */
    private $legacyKernel;

    public function __construct( LegacyKernel $legacyKernel )
    {
        $this->legacyKernel = $legacyKernel;
    }

    /**
     * @return \eZ\Publish\Core\MVC\Legacy\Kernel
     */
    public function getLegacyKernel()
    {
        return $this->legacyKernel;
    }
}
