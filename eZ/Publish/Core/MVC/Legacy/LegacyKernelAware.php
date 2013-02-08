<?php
/**
 * File containing the LegacyKernelAware interface.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
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
