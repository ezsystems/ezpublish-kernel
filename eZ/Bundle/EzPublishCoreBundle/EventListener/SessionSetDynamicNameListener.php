<?php

/**
 * File containing the SessionSetDynamicNameListener class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\Event\PostSiteAccessMatchEvent;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * SiteAccess match listener.
 *
 * Allows to set a dynamic session name based on the siteaccess name.
 */
class SessionSetDynamicNameListener implements EventSubscriberInterface
{
    const MARKER = '{siteaccess_hash}';

    /**
     * Prefix for session name.
     */
    const SESSION_NAME_PREFIX = 'eZSESSID';

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface|null */
    private $session;

    /** @var \Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface|NativeSessionStorage */
    private $sessionStorage;

    /**
     * @param ConfigResolverInterface $configResolver
     * @param SessionInterface $session
     * @param \Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface $sessionStorage
     */
    public function __construct(ConfigResolverInterface $configResolver, SessionInterface $session = null, SessionStorageInterface $sessionStorage = null)
    {
        $this->configResolver = $configResolver;
        $this->session = $session;
        $this->sessionStorage = $sessionStorage;
    }

    public static function getSubscribedEvents()
    {
        return [
            MVCEvents::SITEACCESS => ['onSiteAccessMatch', 250],
        ];
    }

    public function onSiteAccessMatch(PostSiteAccessMatchEvent $event)
    {
        if (
            !(
                $event->getRequestType() === HttpKernelInterface::MASTER_REQUEST
                && isset($this->session)
                && !$this->session->isStarted()
                && $this->sessionStorage instanceof NativeSessionStorage
            )
        ) {
            return;
        }

        $sessionOptions = (array)$this->configResolver->getParameter('session');
        $sessionName = isset($sessionOptions['name']) ? $sessionOptions['name'] : $this->session->getName();
        $sessionOptions['name'] = $this->getSessionName($sessionName, $event->getSiteAccess());
        $this->sessionStorage->setOptions($sessionOptions);
    }

    /**
     * @param string $sessionName
     * @param \eZ\Publish\Core\MVC\Symfony\SiteAccess $siteAccess
     *
     * @return string
     */
    private function getSessionName($sessionName, SiteAccess $siteAccess)
    {
        // Add session prefix if needed.
        if (strpos($sessionName, static::SESSION_NAME_PREFIX) !== 0) {
            $sessionName = static::SESSION_NAME_PREFIX . '_' . $sessionName;
        }

        // Check if uniqueness marker is present. If so, session name will be unique for current siteaccess.
        if (strpos($sessionName, self::MARKER) !== false) {
            $sessionName = str_replace(self::MARKER, md5($siteAccess->name), $sessionName);
        }

        return $sessionName;
    }
}
