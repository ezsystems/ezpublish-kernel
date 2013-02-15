<?php
/**
 * File containing the Session class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\LegacyMapper;

use eZ\Publish\Core\MVC\Legacy\LegacyEvents;
use eZ\Publish\Core\MVC\Legacy\Event\PreBuildKernelEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
            LegacyEvents::PRE_BUILD_LEGACY_KERNEL => array( 'onBuildKernelHandler', 128 )
        );
    }

    /**
     * Adds the session settings to the parameters that will be injected
     * into the legacy kernel
     *
     * @param \eZ\Publish\Core\MVC\Legacy\Event\PreBuildKernelEvent $event
     */
    public function onBuildKernelHandler( PreBuildKernelEvent $event )
    {
        $sessionInfos = array(
            'configured' => false,
            'started' => false,
            'name' => false,
            'namespace' => false,
            'has_previous' => false,
            'storage' => false,
        );
        if ( $this->container->has( 'session' ) )
        {
            $sessionInfos['configured'] = true;

            $session = $this->container->get( 'session' );
            $sessionInfos['name'] = $session->getName();
            $sessionInfos['started'] = $session->isStarted();
            $sessionInfos['namespace'] = $this->container->getParameter(
                'ezpublish.session.attribute_bag.storage_key'
            );
            $sessionInfos['has_previous'] = $this->container->isScopeActive( 'request' ) ? $this->container->get( 'request' )->hasPreviousSession() : false;
            $sessionInfos['storage'] = $this->container->get( 'session.storage' );
        }

        $event->getParameters()->set( 'session', $sessionInfos );
    }
}
