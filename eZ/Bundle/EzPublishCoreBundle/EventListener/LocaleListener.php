<?php

/**
 * File containing the LocaleListener class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface;
use Symfony\Component\HttpKernel\EventListener\LocaleListener as BaseRequestListener;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Enhanced LocaleListener, injecting the converted locale extracted from eZ Publish configuration.
 */
class LocaleListener extends BaseRequestListener
{
    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface
     */
    private $localeConverter;

    /**
     * @param \eZ\Publish\Core\MVC\ConfigResolverInterface $configResolver
     */
    public function setConfigResolver(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    /**
     * @param \eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface $localeConverter
     */
    public function setLocaleConverter(LocaleConverterInterface $localeConverter)
    {
        $this->localeConverter = $localeConverter;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->attributes->has('_locale')) {
            $convertedLocales = [];
            foreach ($this->configResolver->getParameter('languages') as $locale) {
                $convertedLocale = $this->localeConverter->convertToPOSIX($locale);
                if ($convertedLocale !== null) {
                    $convertedLocales[] = $convertedLocale;
                }
            }

            $request->attributes->set('_locale', $request->getPreferredLanguage($convertedLocales));
        }

        parent::onKernelRequest($event);
    }
}
