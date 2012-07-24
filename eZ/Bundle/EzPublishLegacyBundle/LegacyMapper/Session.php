<?php
/**
 * File containing the Session class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\LegacyMapper;

use eZ\Publish\MVC\MVCEvents,
    eZ\Publish\MVC\Event\PreBuildKernelWebHandlerEvent,
    Symfony\Component\EventDispatcher\EventSubscriberInterface,
    Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Maps the session parameters to the legacy parameters
 */
class Session implements EventSubscriberInterface
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
            MVCEvents::BUILD_KERNEL_WEB_HANDLER => array( 'onBuildKernelWebHandler', 128 )
        );
    }

    /**
     * Adds the session settings to the parameters that will be injected
     * into the legacy kernel
     *
     * @param \eZ\Publish\MVC\Event\PreBuildKernelWebHandlerEvent $event
     */
    public function onBuildKernelWebHandler( PreBuildKernelWebHandlerEvent $event )
    {
        $sessionInfos = array();
        $sessionInfos['configured'] = false;
        $sessionInfos['started'] = false;
        $sessionInfos['name'] = false;
        if ( $this->container->has( 'session' ) )
        {
            $sessionInfos['configured'] = true;

            $session = $this->container->get( 'session' );
            $sessionInfos['name'] = $session->getName();
            $sessionInfos['started'] = $session->isStarted();
        }

        $event->getParameters()->set( 'session', $sessionInfos );
    }
}
