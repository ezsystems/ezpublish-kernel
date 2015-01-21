<?php
/**
 * File containing the LegacyKernelAware interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy;

/**
 * Interface for "legacy kernel aware" services.
 */
interface LegacyKernelAware
{
    /**
     * Injects the legacy kernel instance.
     *
     * @param \eZ\Publish\Core\MVC\Legacy\Kernel $legacyKernel
     *
     * @return void
     */
    public function setLegacyKernel( Kernel $legacyKernel );
}
