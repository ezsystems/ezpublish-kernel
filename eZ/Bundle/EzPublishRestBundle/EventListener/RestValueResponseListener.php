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
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

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

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct( ContainerInterface $container )
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::VIEW => 'onKernelResultView',
            KernelEvents::EXCEPTION => 'onKernelExceptionView'
        );
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent $event
     *
     * @throws \Exception
     */public function onKernelResultView( GetResponseForControllerResultEvent $event )
    {
        if ( $event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST )
            return;

        if ( !$this->isRestRequest( $event->getRequest() ) )
            return;

        $result = $event->getControllerResult();
//        if ( !$result instanceof \eZ\Publish\Core\REST\Server\Value && !$result instanceof \eZ\Publish\API\Repository\Values\ValueObject )
//        {
//            throw new \Exception( print_r( $result, true ) );
//            return;
//        }

        $event->setResponse( $this->visitResponse( $result ) );
        $event->stopPropagation();
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent $event
     *
     * @throws \Exception
     * @return void
     */public function onKernelExceptionView( GetResponseForExceptionEvent $event )
    {
        if ( $event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST )
            return;


        if ( !$this->isRestRequest( $event->getRequest() ) )
            return;

        $result = $event->getException();

        $event->setResponse( $this->visitResult( $result ) );
        $event->stopPropagation();
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return bool
     */
    protected function isRestRequest( Request $request )
    {
        return ( strpos( $request->getPathInfo(), '/api/ezp/v2/' ) === 0 );
    }

    /**
     * @param $result
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function visitResult( $result )
    {
        // visit response
        $viewDispatcher = $this->container->get( 'ezpublish_rest.response_visitor_dispatcher' );
        $message = $viewDispatcher->dispatch( $this->container->get( 'ezpublish_rest.request' ), $result );

        // @todo It would be even better if visitors would return a Symfony message directly
        return new Response( $message->body, $message->statusCode, $message->headers );
    }
}
