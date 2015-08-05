<?php

/**
 * File containing the ConsoleCommandListener class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use eZ\Publish\Core\MVC\Symfony\Event\ScopeChangeEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * ConsoleCommandListener match listener.
 */
class ConsoleCommandListener extends ContainerAware implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            ConsoleEvents::COMMAND => [
                ['onConsoleCommand', -1],
            ],
        ];
    }

    public function onConsoleCommand(ConsoleCommandEvent $event)
    {
        $siteAccessName = $event->getInput()->getParameterOption('--siteaccess', null);

        $siteAccess = $this->container->get('ezpublish.siteaccess');
        $siteAccess->name = $siteAccessName ?: $this->container->getParameter('ezpublish.siteaccess.default');
        $siteAccess->matchingType = 'cli';

        $eventDispatcher = $this->container->get('event_dispatcher');
        $eventDispatcher->dispatch(MVCEvents::CONFIG_SCOPE_CHANGE, new ScopeChangeEvent($siteAccess));
    }
}
