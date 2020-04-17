<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\EventListener\LocaleListener as BaseLocaleListener;

/**
 * Enhanced LocaleListener, injecting the converted locale extracted from eZ Publish configuration.
 */
class LocaleListener implements EventSubscriberInterface
{
    /** @var \Symfony\Component\HttpKernel\EventListener\LocaleListener */
    private $innerListener;

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /** @var \eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface */
    private $localeConverter;

    public function __construct(BaseLocaleListener $innerListener, ConfigResolverInterface $configResolver, LocaleConverterInterface $localeConverter)
    {
        $this->innerListener = $innerListener;
        $this->configResolver = $configResolver;
        $this->localeConverter = $localeConverter;
    }

    public static function getSubscribedEvents(): array
    {
        return BaseLocaleListener::getSubscribedEvents();
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$request->attributes->has('_locale')) {
            foreach ($this->configResolver->getParameter('languages') as $locale) {
                $convertedLocale = $this->localeConverter->convertToPOSIX($locale);
                if ($convertedLocale !== null) {
                    // Setting the converted locale to the _locale request attribute, so that it can be properly processed by parent listener.
                    $request->attributes->set('_locale', $convertedLocale);
                    break;
                }
            }
        }

        $this->innerListener->onKernelRequest($event);
    }

    public function onKernelFinishRequest(FinishRequestEvent $event): void
    {
        $this->innerListener->onKernelFinishRequest($event);
    }

    public function setDefaultLocale(KernelEvent $event): void
    {
        $this->innerListener->setDefaultLocale($event);
    }
}
