<?php
/**
 * File containing the RoutingListener class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use eZ\Publish\Core\MVC\Symfony\Event\PostSiteAccessMatchEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;

/**
 * This siteaccess listener handles routing related runtime configuration.
 */
class RoutingListener implements EventSubscriberInterface
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
            MVCEvents::SITEACCESS => array( 'onSiteAccessMatch', 200 )
        );
    }

    public function onSiteAccessMatch( PostSiteAccessMatchEvent $event )
    {
        $configResolver = $this->container->get( 'ezpublish.config.resolver' );
        $rootLocationId = $configResolver->getParameter( 'content.tree_root.location_id' );
        $this->container->get( 'ezpublish.urlalias_router' )->setRootLocationId( $rootLocationId );
        $urlAliasGenerator = $this->container->get( 'ezpublish.urlalias_generator' );
        $urlAliasGenerator->setRootLocationId( $rootLocationId );
        $urlAliasGenerator->setExcludedUriPrefixes( $configResolver->getParameter( 'content.tree_root.excluded_uri_prefixes' ) );
    }
}
