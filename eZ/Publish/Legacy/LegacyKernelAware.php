<?php
/**
 * File containing the LegacyKernelAware interface.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Legacy;

/**
 * Interface for "legacy kernel aware" services.
 */
interface LegacyKernelAware
{
    /**
     * Injects the legacy kernel instance.
     *
     * @abstract
     * @param \eZ\Publish\Legacy\Kernel $legacyKernel
     * @return void
     */
    public function setLegacyKernel( Kernel $legacyKernel );

    /**
     * Gets the legacy kernel instance.
     *
     * @abstract
     * @return \eZ\Publish\Legacy\Kernel
     */
    public function getLegacyKernel();
}
