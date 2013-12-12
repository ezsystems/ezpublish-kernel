<?php
/**
 * File containing the RequestListener class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use eZ\Publish\Core\MVC\Symfony\Security\User;

class RequestListener implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    private $securityContext;

    public function __construct( ContainerInterface $container, SecurityContextInterface $securityContext )
    {
        $this->container = $container;
        $this->securityContext = $securityContext;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => 'onKernelRequest'
        );
    }

    /**
     * If user is logged-in in legacy_mode (e.g. legacy admin interface),
     * will inject currently logged-in user in the repository.
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest( GetResponseEvent $event )
    {
        /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface $configResolver */
        $configResolver = $this->container->get( 'ezpublish.config.resolver' );
        $request = $event->getRequest();
        if (
            $event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST
            || !$configResolver->getParameter( 'legacy_mode' )
            || !$request->getSession()->has( 'eZUserLoggedInID' )
        )
        {
            return;
        }

        /** @var \eZ\Publish\API\Repository\Repository $repository */
        $repository = $this->container->get( 'ezpublish.api.repository' );
        $apiUser = $repository->getUserService()->loadUser( $request->getSession()->get( 'eZUserLoggedInID' ) );
        $repository->setCurrentUser( $apiUser );

        $token = $this->securityContext->getToken();
        if ( $token instanceof TokenInterface )
        {
            $token->setUser( new User( $apiUser ) );
            $token->setAuthenticated( true );
        }
    }
}
