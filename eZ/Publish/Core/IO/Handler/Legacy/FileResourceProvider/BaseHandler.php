<?php
/**
 * File containing the BaseHandler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\IO\Handler\Legacy\FileResourceProvider;

use eZ\Publish\Core\MVC\Legacy\LegacyKernelAware;
use eZ\Publish\Core\MVC\Legacy\Kernel as LegacyKernel;

abstract class BaseHandler implements LegacyKernelAware
{
    /**
     * @var \eZ\Publish\Core\MVC\Legacy\Kernel
     */
    protected $legacyKernel;

    /**
     * Injects the legacy kernel instance.
     *
     * @param \eZ\Publish\Core\MVC\Legacy\Kernel $legacyKernel
     *
     * @return void
     */
    public function setLegacyKernel( LegacyKernel $legacyKernel )
    {
        $this->legacyKernel = $legacyKernel;
    }

    /**
     * Gets the legacy kernel instance.
     *
     * @return \eZ\Publish\Core\MVC\Legacy\Kernel
     */
    protected function getLegacyKernel()
    {
        return $this->legacyKernel;
    }
}
