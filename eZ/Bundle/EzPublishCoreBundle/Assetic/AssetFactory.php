<?php

/**
 * File containing the AssetFactory class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Assetic;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\DynamicSettingParserInterface;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Symfony\Bundle\AsseticBundle\Factory\AssetFactory as BaseAssetFactory;

class AssetFactory extends BaseAssetFactory
{
    /** @var ConfigResolverInterface */
    private $configResolver;

    /** @var DynamicSettingParserInterface */
    private $dynamicSettingParser;

    public function setConfigResolver(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    public function setDynamicSettingParser(DynamicSettingParserInterface $dynamicSettingParser)
    {
        $this->dynamicSettingParser = $dynamicSettingParser;
    }

    /**
     * Adds dynamic settings notation support: $<paramName>[;<namespace>[;<scope>]]$.
     *
     * {@inheritdoc}
     */
    protected function parseInput($input, array $options = [])
    {
        if ($this->dynamicSettingParser->isDynamicSetting($input)) {
            $parsedSettings = $this->dynamicSettingParser->parseDynamicSetting($input);
            $input = $this->configResolver->getParameter(
                $parsedSettings['param'],
                $parsedSettings['namespace'],
                $parsedSettings['scope']
            );

            if (is_array($input)) {
                $collection = $this->createAssetCollection([], $options);
                foreach ($input as $file) {
                    $collection->add(parent::parseInput($file, $options));
                }

                return $collection;
            }
        }

        return parent::parseInput($input, $options);
    }
}
