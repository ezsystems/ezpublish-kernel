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

final class ComplexConfigProcessor
{
    /** @var \eZ\Publish\Core\MVC\ConfigResolverInterface */
    private $configResolver;

    /** @var \eZ\Publish\Core\MVC\Symfony\SiteAccess\SiteAccessService */
    private $siteAccessService;

    public function __construct(
        ConfigResolverInterface $configResolver,
        SiteAccessService $siteAccessService
    ) {
        $this->configResolver = $configResolver;
        $this->siteAccessService = $siteAccessService;
    }

    public function processComplexSetting(string $setting): string
    {
        $siteAccessName = $this->siteAccessService->getCurrent()->name;

        $complexSettingParser = new ComplexSettingParser();

        if (!$this->configResolver->hasParameter($setting, null, $siteAccessName)) {
            throw new ParameterNotFoundException($setting, null, [$siteAccessName]);
        }

        $settingValue = $this->configResolver->getParameter($setting, null, $siteAccessName);

        if (!$complexSettingParser->containsDynamicSettings($settingValue)) {
            return $settingValue;
        }

        // we kind of need to process this as well, don't we ?
        if ($complexSettingParser->isDynamicSetting($settingValue)) {
            $parts = $complexSettingParser->parseDynamicSetting($settingValue);
            if (!isset($parts['namespace'])) {
                $parts['namespace'] = 'ezsettings';
            }
            if (!isset($parts['scope'])) {
                $parts['scope'] = $siteAccessName;
            }

            return $this->configResolver->getParameter($parts['param'], null, $siteAccessName);
        }

        $value = $settingValue;
        foreach ($complexSettingParser->parseComplexSetting($settingValue) as $dynamicSetting) {
            $parts = $complexSettingParser->parseDynamicSetting($dynamicSetting);
            if (!isset($parts['namespace'])) {
                $parts['namespace'] = 'ezsettings';
            }
            if (!isset($parts['scope'])) {
                $parts['scope'] = $siteAccessName;
            }

            $dynamicSettingValue = $this->configResolver->getParameter($parts['param'], $parts['namespace'], $parts['scope']);

            $value = str_replace($dynamicSetting, $dynamicSettingValue, $value);
        }

        return $value;
    }
}
