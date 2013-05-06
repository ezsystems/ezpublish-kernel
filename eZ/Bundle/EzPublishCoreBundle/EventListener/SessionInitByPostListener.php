<?php
/**
 * File containing the SessionInitByPostListener class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\Event\PostSiteAccessMatchEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Initializes the session id by looking at a POST variable named like the
 * session. Mainly used by Flash (for instance ezmultiupload LS).
 */
class SessionInitByPostListener implements EventSubscriberInterface
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
            MVCEvents::SITEACCESS => array( 'onSiteAccessMatch', 249 )
        );
    }

    public function onSiteAccessMatch( PostSiteAccessMatchEvent $event )
    {
        if ( !$this->container->has( 'session' ) || $event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST )
        {
            return;
        }
        $session = $this->container->get( 'session' );
        $sessionName = $session->getName();
        $request = $event->getRequest();

        if (
            !$session->isStarted()
            && !$request->hasPreviousSession()
            && $request->request->has( $sessionName )
        )
        {
            $session->setId( $request->request->get( $sessionName ) );
            $session->start();
        }
    }
}
