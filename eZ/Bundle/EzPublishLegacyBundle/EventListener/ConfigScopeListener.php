<?php
/**
 * File containing the ConfigScopeListener class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\EventListener;

use eZ\Publish\Core\MVC\Symfony\Event\ScopeChangeEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ConfigScopeListener implements EventSubscriberInterface
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
            MVCEvents::CONFIG_SCOPE_CHANGE => 'onConfigScopeChange',
            MVCEvents::CONFIG_SCOPE_RESTORE => 'onConfigScopeChange',
        );
    }

    public function onConfigScopeChange( ScopeChangeEvent $event )
    {
        // Resets legacy kernel services to ensure having a new instance of it.
        $this->container->set( 'ezpublish_legacy.kernel', null );
        $this->container->set( 'ezpublish_legacy.kernel.lazy', null );
        $this->container->set( 'ezpublish_legacy.kernel_handler.web', null );
    }
}
