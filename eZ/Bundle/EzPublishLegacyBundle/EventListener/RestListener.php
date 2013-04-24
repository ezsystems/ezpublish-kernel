<?php
/**
 * File containing the LocaleListener class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use ezxFormToken;

/**
 * Enhanced LocaleListener, injecting the converted locale extracted from eZ Publish configuration.
 */
class RestListener implements EventSubscriberInterface
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
            KernelEvents::REQUEST => 'onKernelRequest'
        );
    }

    public function onKernelRequest( GetResponseEvent $event )
    {
        if ( !$this->isRestRequest( $event->getRequest() ) )
        {
            return;
        }

        if ( !$this->container->getParameter( 'form.type_extension.csrf.enabled' ) )
        {
            return;
        }

        // Inject csrf intent string to make sure legacy & symfony stack work together
        // TODO expose this in configuration? (also used in User controller)
        ezxFormToken::setIntention( "rest" );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return boolean
     */
    protected function isRestRequest( Request $request )
    {
        return ( strpos( $request->getPathInfo(), '/api/ezp/v2/' ) === 0 );
    }
}
