<?php
/**
 * File containing the ResponseListener class.
 *
 * @copyright Copyright (C) 2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishRestBundle\EventListener;

use eZ\Publish\Core\REST\Server\View\AcceptHeaderVisitorDispatcher;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * REST Response Listener.
 *
 * Converts responses from REST controllers to REST Responses, depending on the Accept-Header value.
 */
class ResponseListener implements EventSubscriberInterface
{
    /**
     * @var AcceptHeaderVisitorDispatcher
     */
    private $viewDispatcher;

    /**
     * @param $viewDispatcher AcceptHeaderVisitorDispatcher
     */
    public function __construct( AcceptHeaderVisitorDispatcher $viewDispatcher )
    {
        $this->viewDispatcher = $viewDispatcher;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::VIEW => 'onKernelResultView',
            KernelEvents::EXCEPTION => 'onKernelExceptionView',
        );
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent $event
     */
    public function onKernelResultView( GetResponseForControllerResultEvent $event )
    {
        if ( !$event->getRequest()->attributes->get( 'is_rest_request' ) )
            return;

        $event->setResponse(
            $this->visitResult(
                $event->getRequest(),
                $event->getControllerResult()
            )
        );
        $event->stopPropagation();
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent $event
     *
     * @throws \Exception
     *
     * @return void
     */
    public function onKernelExceptionView( GetResponseForExceptionEvent $event )
    {
        if ( !$event->getRequest()->attributes->get( 'is_rest_request' ) )
            return;

        $event->setResponse(
            $this->visitResult(
                $event->getRequest(),
                $event->getException()
            )
        );
        $event->stopPropagation();
    }

    /**
     * @param Request $request
     * @param mixed $result
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function visitResult( Request $request, $result )
    {
        $message = $this->viewDispatcher->dispatch( $request, $result );

        // @todo It would be even better if visitors would return a Symfony message directly
        // Use a message visitor, that is injected the dispatcher
        return new Response( $message->body, $message->statusCode, $message->headers );
    }
}
