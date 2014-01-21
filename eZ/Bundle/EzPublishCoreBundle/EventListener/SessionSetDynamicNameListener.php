<?php
/**
 * File containing the SessionSetDynamicNameListener class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\Event\PostSiteAccessMatchEvent;
use eZ\Bundle\EzPublishCoreBundle\Kernel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * SiteAccess match listener.
 *
 * Allows to set a dynamic session name based on the siteaccess name.
 */
class SessionSetDynamicNameListener implements EventSubscriberInterface
{
    const MARKER = "{siteaccess_hash}";

    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface|null
     */
    private $session;

    /**
     * @note Getting session from the container and not from the request because the session object is assigned to the request only when session has started.
     *
     * @param ConfigResolverInterface $configResolver
     * @param SessionInterface $session
     */
    public function __construct( ConfigResolverInterface $configResolver, SessionInterface $session = null )
    {
        $this->configResolver = $configResolver;
        $this->session = $session;
    }

    public static function getSubscribedEvents()
    {
        return array(
            MVCEvents::SITEACCESS => array( 'onSiteAccessMatch', 250 )
        );
    }

    public function onSiteAccessMatch( PostSiteAccessMatchEvent $event )
    {
        if ( !isset( $this->session ) || $event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST )
        {
            return;
        }

        if ( !$this->session->isStarted() )
        {
            $sessionName = $this->configResolver->getParameter( 'session_name' );
            // Add session prefix if needed.
            if ( strpos( $sessionName, Kernel::SESSION_NAME_PREFIX ) !== 0 )
            {
                $sessionName = Kernel::SESSION_NAME_PREFIX . '_' . $sessionName;
            }

            if ( strpos( $sessionName, self::MARKER ) !== false )
            {
                $this->session->setName(
                    str_replace(
                        self::MARKER,
                        md5( $event->getSiteAccess()->name ),
                        $sessionName
                    )
                );
            }
            else
            {
                $this->session->setName( $sessionName );
            }
        }
    }
}
