<?php
/**
 * File containing the ViewControllerListener class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\Core\MVC\Symfony\Controller\ManagerInterface as ControllerManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ViewControllerListener implements EventSubscriberInterface
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Controller\ManagerInterface
     */
    private $controllerManager;

    /**
     * @var \Symfony\Component\HttpKernel\Controller\ControllerResolverInterface
     */
    private $controllerResolver;

    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    private $repository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        ControllerResolverInterface $controllerResolver,
        ControllerManagerInterface $controllerManager,
        Repository $repository,
        LoggerInterface $logger
    )
    {
        $this->controllerManager = $controllerManager;
        $this->controllerResolver = $controllerResolver;
        $this->repository = $repository;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return array( KernelEvents::CONTROLLER => array( 'getController', 10 ) );
    }

    /**
     * Detects if there is a custom controller to use to render a Location/Content.
     *
     * @param FilterControllerEvent $event
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function getController( FilterControllerEvent $event )
    {
        $request = $event->getRequest();
        // Only taking content related controller (i.e. ez_content:viewLocation or ez_content:viewContent)
        if ( strpos( $request->attributes->get( '_controller' ), 'ez_content:' ) === false )
        {
            return;
        }
        try
        {
            if ( $request->attributes->has( 'locationId' ) )
            {
                $valueObject = $this->repository->getLocationService()->loadLocation(
                    $request->attributes->get( 'locationId' )
                );
            }
            else if ( $request->attributes->get( 'location' ) instanceof Location )
            {
                $valueObject = $request->attributes->get( 'location' );
                $request->attributes->set( 'locationId', $valueObject->id );
            }
            else if ( $request->attributes->has( 'contentId' ) )
            {
                $valueObject = $this->repository->sudo(
                    function ( Repository $repository ) use ( $request )
                    {
                        return $repository->getContentService()->loadContentInfo(
                            $request->attributes->get( 'contentId' )
                        );
                    }
                );
            }
            else if ( $request->attributes->get( 'contentInfo' ) instanceof ContentInfo )
            {
                $valueObject = $request->attributes->get( 'contentInfo' );
                $request->attributes->set( 'contentId', $valueObject->id );
            }
        }
        catch ( UnauthorizedException $e)
        {
            throw new AccessDeniedException();
        }

        if ( !isset( $valueObject ) )
        {
            $this->logger->error( 'Could not resolver a view controller, invalid value object to match.' );
            return;
        }

        $controllerReference = $this->controllerManager->getControllerReference(
            $valueObject,
            $request->attributes->get( 'viewType' )
        );

        if ( !$controllerReference instanceof ControllerReference )
            return;

        $request->attributes->set( '_controller', $controllerReference->controller );
        $event->setController( $this->controllerResolver->getController( $request ) );
    }
}
