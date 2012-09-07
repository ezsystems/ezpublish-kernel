<?php
/**
 * File containing the Controller class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * @var \Closure
     */
    private $legacyKernelClosure;

    /**
     * @return \eZ\Publish\API\Repository\Repository
     * @throws \LogicException
     */
    public function getRepository()
    {
        if ( !$this->container->has( 'ezpublish.api.repository' ) )
            throw new \LogicException( 'The EzPublishCoreBundle has not been registered in your application.' );

        return $this->container->get( 'ezpublish.api.repository' );
    }

    /**
     * Returns the legacy kernel object.
     *
     * @return \eZ\Publish\Core\MVC\Legacy\Kernel
     */
    final protected function getLegacyKernel()
    {
        if ( !isset( $this->legacyKernelClosure ) )
            $this->legacyKernelClosure = $this->get( 'ezpublish_legacy.kernel' );

        $legacyKernelClosure = $this->legacyKernelClosure;
        return $legacyKernelClosure();
    }

    /**
     * @return \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\DynamicConfigResolver
     */
    protected function getConfigResolver()
    {
        return $this->container->get( 'ezpublish.config.resolver' );
    }

    /**
     * @return \eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Configuration\LegacyConfigResolver
     */
    protected function getLegacyConfigResolver()
    {
        return $this->container->get( 'ezpublish_legacy.config.resolver' );
    }
}
