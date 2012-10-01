<?php
/**
 * File containing the RestValueResponseListener class.
 *
 * @copyright Copyright (C) 2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishRestBundle\EventListener;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * This class listens, as a service, for the kernel.view event, triggered when a controller method
 * didn't return a Response object.
 *
 * It converts the RestValue / Value Object to a Response using Visitors
 */
class RestValueResponseListener implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    public function __construct( ContainerInterface $container )
    {
        $this->container = $container;
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::VIEW => 'onKernelView'
        );
    }

    public function onKernelView( GetResponseForControllerResultEvent $event )
    {
        if ( $event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST )
            return;

        $result = $event->getControllerResult();
        if ( !$result instanceof \eZ\Publish\Core\REST\Server\Value && !$result instanceof \eZ\Publish\API\Repository\Values\ValueObject )
        {
            throw new \Exception( print_r( $result, true ) );
            return;
        }
        // visit response
        $viewDispatcher = $this->container->get( 'ezpublish_rest.response_visitor_dispatcher' );
        $result = $viewDispatcher->dispatch( $this->container->get( 'ezpublish_rest.request' ), $result );
        $response = new Response( $result->body, 200, $result->headers );
        $event->setResponse( $response );
        $event->stopPropagation();
    }
}
