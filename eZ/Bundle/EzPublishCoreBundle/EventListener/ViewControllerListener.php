<?php
/**
 * File containing the ViewControllerListener class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\MVC\Symfony\Controller\Manager as ControllerManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ViewControllerListener implements EventSubscriberInterface
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Controller\Manager
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
        ControllerManager $controllerManager,
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
        return array( KernelEvents::CONTROLLER => 'getController' );
    }

    /**
     * Detects if there is a custom controller to use to render a Location/Content.
     *
     * @param FilterControllerEvent $event
     */
    public function getController( FilterControllerEvent $event )
    {
        $request = $event->getRequest();
        // Only taking content related controller (i.e. ez_content:viewLocation or ez_content:viewContent)
        if ( strpos( $request->attributes->get( '_controller' ), 'ez_content:' ) === false )
        {
            return;
        }

        if ( $request->attributes->has( 'locationId' ) )
        {
            $valueObject = $this->repository->getLocationService()->loadLocation(
                $request->attributes->get( 'locationId' )
            );
        }
        else if ( $request->attributes->has( 'contentId' ) )
        {
            $valueObject = $this->repository->getContentService()->loadContentInfo(
                $request->attributes->get( 'contentId' )
            );
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
