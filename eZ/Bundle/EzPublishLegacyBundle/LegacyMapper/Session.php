<?php
/**
 * File containing the Session class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\LegacyMapper;

use eZ\Publish\Core\MVC\Legacy\Event\PreBuildKernelWebHandlerEvent;
use eZ\Publish\Core\MVC\Legacy\LegacyEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;

/**
 * Maps the session parameters to the legacy parameters
 */
class Session implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    private $session;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface
     */
    private $sessionStorage;

    /**
     * @var string
     */
    private $sessionStorageKey;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;

    public function __construct( SessionStorageInterface $sessionStorage, $sessionStorageKey, SessionInterface $session = null )
    {
        $this->sessionStorage = $sessionStorage;
        $this->sessionStorageKey = $sessionStorageKey;
        $this->session = $session;
    }

    public function setRequest( Request $request = null )
    {
        $this->request = $request;
    }

    public static function getSubscribedEvents()
    {
        return array(
            LegacyEvents::PRE_BUILD_LEGACY_KERNEL_WEB => array( 'onBuildKernelHandler', 128 )
        );
    }

    /**
     * Adds the session settings to the parameters that will be injected
     * into the legacy kernel
     *
     * @param \eZ\Publish\Core\MVC\Legacy\Event\PreBuildKernelWebHandlerEvent $event
     */
    public function onBuildKernelHandler( PreBuildKernelWebHandlerEvent $event )
    {
        $sessionInfos = array(
            'configured' => false,
            'started' => false,
            'name' => false,
            'namespace' => false,
            'has_previous' => false,
            'storage' => false,
        );
        if ( isset( $this->session ) )
        {
            $sessionInfos['configured'] = true;

            $sessionInfos['name'] = $this->session->getName();
            $sessionInfos['started'] = $this->session->isStarted();
            $sessionInfos['namespace'] = $this->sessionStorageKey;
            $sessionInfos['has_previous'] = isset( $this->request ) ? $this->request->hasPreviousSession() : false;
            $sessionInfos['storage'] = $this->sessionStorage;
        }

        $event->getParameters()->set( 'session', $sessionInfos );
    }
}
