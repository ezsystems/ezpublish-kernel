<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle;

use eZ\Bundle\EzPublishCoreBundle\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * @var \Closure
     */
    private $legacyKernelClosure;

    /**
     * Returns the legacy kernel object.
     *
     * @return \eZ\Publish\Core\MVC\Legacy\Kernel
     */
    final protected function getLegacyKernel()
    {
        if ( !isset( $this->legacyKernelClosure ) )
        {
            $this->legacyKernelClosure = $this->get( 'ezpublish_legacy.kernel' );
        }

        $legacyKernelClosure = $this->legacyKernelClosure;

        return $legacyKernelClosure();
    }
}
