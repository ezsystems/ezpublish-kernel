<?php
/**
 * File containing the Controller class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use eZ\Publish\Core\MVC\Symfony\Security\Authorization\Attribute as AuthorizationAttribute;

class Controller extends BaseController
{
    /**
     * @var \Closure
     */
    private $legacyKernelClosure;

    /**
     * @throws \LogicException
     *
     * @return \eZ\Publish\API\Repository\Repository
     */
    public function getRepository()
    {
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
     * @return \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver
     */
    protected function getConfigResolver()
    {
        return $this->container->get( 'ezpublish.config.resolver' );
    }

    /**
     * Checks if current user has granted access to provided attribute
     *
     * @param \eZ\Publish\Core\MVC\Symfony\Security\Authorization\Attribute $attribute
     *
     * @return boolean
     */
    public function isGranted( AuthorizationAttribute $attribute )
    {
        return $this->container->get( 'security.context' )->isGranted( $attribute );
    }

    /**
     * Returns the general helper service, exposed in Twig templates as "ezpublish" global variable.
     *
     * @return \eZ\Publish\Core\MVC\Legacy\Templating\GlobalHelper
     */
    public function getGlobalHelper()
    {
        return $this->container->get( 'ezpublish.templating.global_helper' );
    }

    /**
     * Returns the root location object for current siteaccess configuration.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    public function getRootLocation()
    {
        return $this->getGlobalHelper()->getRootLocation();
    }
}
