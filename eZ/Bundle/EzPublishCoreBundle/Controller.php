<?php
/**
 * File containing the Controller class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use eZ\Publish\Core\MVC\Symfony\Security\Authorization\Attribute as AuthorizationAttribute;
use Symfony\Component\HttpFoundation\Response;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;

class Controller extends BaseController
{
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
     * @return \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver
     */
    protected function getConfigResolver()
    {
        return $this->container->get( 'ezpublish.config.resolver' );
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

    /**
     * {@inheritDoc}
     */
    public function render( $view, array $parameters = array(), Response $response = null )
    {
        if ( null === $response )
            $response = new Response();

        $response->setContent( $this->renderView( $view, $parameters ) );

        return $response;
    }

    /**
     * Using eZ view Manager
     *
     * {@inheritDoc}
     */
    public function renderView( $view, array $parameters = array() )
    {
        $viewManager = $this->container->get( 'ezpublish.view_manager' );

        return $viewManager->renderContentView( new ContentView( $view ), $parameters );
    }
}
