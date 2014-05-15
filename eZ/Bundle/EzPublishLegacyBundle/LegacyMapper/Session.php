<?php
/**
 * File containing the Session class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\LegacyMapper;

use eZ\Publish\Core\MVC\Legacy\Event\PreBuildKernelEvent;
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
        if ( isset( $this->session ) )
        {
            $sessionInfos['configured'] = true;

            $sessionInfos['name'] = $this->session->getName();
            $sessionInfos['started'] = $this->session->isStarted();
            $sessionInfos['namespace'] = $this->sessionStorageKey;
            $sessionInfos['has_previous'] = isset( $this->request ) ? $this->request->hasPreviousSession() : false;
            $sessionInfos['storage'] = $this->sessionStorage;
        }

        $legacyKernelParameters = $event->getParameters();
        $legacyKernelParameters->set( 'session', $sessionInfos );

        // Deactivate session cookie settings in legacy kernel.
        // This will force using settings defined in Symfony.
        $sessionSettings = array(
            'site.ini/Session/CookieTimeout' => false,
            'site.ini/Session/CookiePath' => false,
            'site.ini/Session/CookieDomain' => false,
            'site.ini/Session/CookieSecure' => false,
            'site.ini/Session/CookieHttponly' => false,
        );
        $legacyKernelParameters->set(
            "injected-settings",
            $sessionSettings + (array)$legacyKernelParameters->get( "injected-settings" )
        );
    }
}
