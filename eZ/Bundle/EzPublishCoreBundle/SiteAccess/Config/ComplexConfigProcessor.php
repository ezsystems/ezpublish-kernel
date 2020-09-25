<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\SiteAccess\Config;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ComplexSettings\ComplexSettingParser;
use eZ\Publish\Core\MVC\Exception\ParameterNotFoundException;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessService;
use eZ\Publish\SPI\SiteAccess\ConfigProcessor;
use function str_replace;

final class ComplexConfigProcessor implements ConfigProcessor
{
    private const DEFAULT_NAMESPACE = 'ezsettings';

    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessService */
    private $siteAccessService;

    /** @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ComplexSettings\ComplexSettingParserInterface */
    private $complexSettingParser;

    public function __construct(
        ConfigResolverInterface $configResolver,
        SiteAccessService $siteAccessService
    ) {
        $this->configResolver = $configResolver;
        $this->siteAccessService = $siteAccessService;

        // instantiate non-injectable DI configuration parser
        $this->complexSettingParser = new ComplexSettingParser();
    }

    public function processComplexSetting(string $setting): string
    {
        $siteAccessName = $this->siteAccessService->getCurrent()->name;

        if (!$this->configResolver->hasParameter($setting, null, $siteAccessName)) {
            throw new ParameterNotFoundException($setting, null, [$siteAccessName]);
        }

        $settingValue = $this->configResolver->getParameter($setting, null, $siteAccessName);

        if (!$this->complexSettingParser->containsDynamicSettings($settingValue)) {
            return $settingValue;
        }

        // we kind of need to process this as well, don't we ?
        if ($this->complexSettingParser->isDynamicSetting($settingValue)) {
            $parts = $this->complexSettingParser->parseDynamicSetting($settingValue);

            return $this->configResolver->getParameter($parts['param'], null, $siteAccessName);
        }

        return $this->processSettingValue($settingValue);
    }

    public function processSettingValue(string $value): string
    {
        foreach ($this->complexSettingParser->parseComplexSetting($value) as $dynamicSetting) {
            $parts = $this->complexSettingParser->parseDynamicSetting($dynamicSetting);
            if (!isset($parts['namespace'])) {
                $parts['namespace'] = self::DEFAULT_NAMESPACE;
            }

            $dynamicSettingValue = $this->configResolver->getParameter(
                $parts['param'],
                $parts['namespace'],
                $parts['scope'] ?? $this->siteAccessService->getCurrent()->name
            );

            $value = str_replace($dynamicSetting, $dynamicSettingValue, $value);
        }

        return $value;
    }
}
