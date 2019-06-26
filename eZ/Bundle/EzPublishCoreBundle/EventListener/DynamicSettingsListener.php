<?php

/**
 * File containing the DynamicSettingsListener class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use eZ\Publish\Core\MVC\Symfony\Event\PostSiteAccessMatchEvent;
use eZ\Publish\Core\MVC\Symfony\Event\ScopeChangeEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use Symfony\Component\DependencyInjection\ExpressionLanguage;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class DynamicSettingsListener implements EventSubscriberInterface
{
    /**
     * Array of serviceIds to reset in the container.
     *
     * @var array
     */
    private $resettableServiceIds;

    /** @var array */
    private $updatableServices;

    /** @var ExpressionLanguage */
    private $expressionLanguage;

    /** @var ContainerInterface */
    private $container;

    public function __construct(array $resettableServiceIds, array $updatableServices, ExpressionLanguage $expressionLanguage = null)
    {
        $this->resettableServiceIds = $resettableServiceIds;
        $this->updatableServices = $updatableServices;
        $this->expressionLanguage = $expressionLanguage ?: new ExpressionLanguage();
    }

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public static function getSubscribedEvents()
    {
        return [
            MVCEvents::SITEACCESS => ['onSiteAccessMatch', 254],
            MVCEvents::CONFIG_SCOPE_CHANGE => ['onConfigScopeChange', 90],
            MVCEvents::CONFIG_SCOPE_RESTORE => ['onConfigScopeChange', 90],
        ];
    }

    public function onSiteAccessMatch(PostSiteAccessMatchEvent $event)
    {
        if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        $this->resetDynamicSettings();
    }

    public function onConfigScopeChange(ScopeChangeEvent $event)
    {
        $this->resetDynamicSettings();
    }

    /**
     * Ensure that dynamic settings are correctly reset,
     * so that services that rely on those are correctly updated.
     */
    private function resetDynamicSettings()
    {
        // Ensure to reset services that need to be.
        foreach ($this->resettableServiceIds as $serviceId) {
            if (!$this->container->initialized($serviceId)) {
                continue;
            }

            $this->container->set($serviceId, null);
        }

        // Update services that can be updated.
        foreach ($this->updatableServices as $serviceId => $methodCalls) {
            if (!$this->container->initialized($serviceId)) {
                continue;
            }

            $service = $this->container->get($serviceId);
            foreach ($methodCalls as $callConfig) {
                list($method, $expression) = $callConfig;
                $argument = $this->expressionLanguage->evaluate($expression, ['container' => $this->container]);
                call_user_func_array([$service, $method], [$argument]);
            }
        }
    }
}
