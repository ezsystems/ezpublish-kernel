<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\PlatformBehatBundle\Initializer;

use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use eZ\Publish\Core\MVC\Symfony\Event\ScopeChangeEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\SiteAccess;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelInterface;

final class BehatSiteAccessInitializer implements EventSubscriberInterface
{
    private const EZPLATFORM_SITEACCESS_ENV_VAR = 'EZPLATFORM_SITEACCESS';
    private const DEFAULT_SITEACCESS_PARAMETER = 'ezpublish.siteaccess.default';
    private const EVENT_DISPATCHER_SERVICE_ID = 'event_dispatcher';

    /** @var \Symfony\Component\HttpKernel\KernelInterface */
    private $kernel;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ScenarioTested::BEFORE => ['setSiteAccess'],
            ExampleTested::BEFORE => ['setSiteAccess'],
        ];
    }

    public function setSiteAccess(): void
    {
        $siteAccess = $this->getSiteAccess();
        // We cannot inject EventDispatcher directly because it would be the one used by Behat internally
        // We need to get the one made available by Symfony2Extension
        $this->kernel
            ->getContainer()
            ->get(self::EVENT_DISPATCHER_SERVICE_ID)
            ->dispatch(MVCEvents::CONFIG_SCOPE_CHANGE, new ScopeChangeEvent($siteAccess));
    }

    private function getSiteAccess(): SiteAccess
    {
        $siteAccessFromEnvVar = getenv(self::EZPLATFORM_SITEACCESS_ENV_VAR);
        $defaultSiteAccess = $this->kernel->getContainer()->getParameter(self::DEFAULT_SITEACCESS_PARAMETER);

        $siteAccessName = $siteAccessFromEnvVar !== false ? $siteAccessFromEnvVar : $defaultSiteAccess;

        return new SiteAccess($siteAccessName, 'cli');
    }
}
