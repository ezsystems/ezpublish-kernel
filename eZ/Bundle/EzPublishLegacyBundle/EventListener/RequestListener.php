<?php
/**
 * File containing the RequestListener class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\EventListener;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
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
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    private $repository;

    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    private $securityContext;

    public function __construct( ConfigResolverInterface $configResolver, Repository $repository, SecurityContextInterface $securityContext )
    {
        $this->configResolver = $configResolver;
        $this->repository = $repository;
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
        $request = $event->getRequest();
        if (
            $event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST
            || !$this->configResolver->getParameter( 'legacy_mode' )
            || !$request->getSession()->has( 'eZUserLoggedInID' )
        )
        {
            return;
        }

        $apiUser = $this->repository->getUserService()->loadUser( $request->getSession()->get( 'eZUserLoggedInID' ) );
        $this->repository->setCurrentUser( $apiUser );

        $token = $this->securityContext->getToken();
        if ( $token instanceof TokenInterface )
        {
            $token->setUser( new User( $apiUser ) );
            $token->setAuthenticated( true );
        }
    }
}
