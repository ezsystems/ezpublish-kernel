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
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessAware;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * ConsoleCommandListener event listener.
 */
class ConsoleCommandListener implements EventSubscriberInterface, SiteAccessAware
{
    /**
     * @var string
     */
    private $defaultSiteAccessName;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var
     */
    private $siteAccess;

    /**
     * ConsoleCommandListener constructor.
     */
    public function __construct($defaultSiteAccessName, EventDispatcherInterface $eventDispatcher)
    {
        $this->defaultSiteAccessName = $defaultSiteAccessName;
        $this->eventDispatcher = $eventDispatcher;
    }

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

        $this->siteAccess->name = $siteAccessName ?: $this->defaultSiteAccessName;
        $this->siteAccess->matchingType = 'cli';

        $this->eventDispatcher->dispatch(MVCEvents::CONFIG_SCOPE_CHANGE, new ScopeChangeEvent($this->siteAccess));
    }

    public function setSiteAccess(SiteAccess $siteAccess = null)
    {
        $this->siteAccess = $siteAccess;
    }
}
