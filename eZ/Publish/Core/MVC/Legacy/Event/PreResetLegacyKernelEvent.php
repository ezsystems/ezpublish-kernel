<?php
/**
 * File containing the PreBuildKernelEvent class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Event;

use eZ\Publish\Core\MVC\Legacy\Kernel as LegacyKernel;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\ParameterBag;

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
