<?php
/**
 * File containing the RequestListener class.
 *
 * @copyright Copyright (C) 2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishRestBundle\EventListener;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * REST request listener.
 *
 * Flags a REST request as such using the is_rest_request attribute.
 */
class RequestListener implements EventSubscriberInterface
{
    /**
     * @var string
     */
    private $restPrefix;

    /**
     * @param string $restPrefix
     */
    public function __construct( $restPrefix )
    {
        $this->restPrefix = $restPrefix;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => 'onKernelRequest',
        );
    }

    /**
     * If the request is a REST one, sets the is_rest_request request attribute
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
     */
    public function onKernelRequest( GetResponseEvent $event )
    {
        $isRestRequest = true;

        if ( $event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST )
        {
            $isRestRequest = false;
        }

        if ( !$this->hasRestPrefix( $event->getRequest() ) )
        {
            $isRestRequest = false;
        }

        $event->getRequest()->attributes->set( 'is_rest_request', $isRestRequest );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return boolean
     */
    protected function hasRestPrefix( Request $request )
    {
        return (
            strpos(
                $request->getPathInfo(),
                $this->restPrefix
            ) === 0
        );
    }
}
