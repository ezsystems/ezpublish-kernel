<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ComplexSettings;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\DynamicSettingParser;

class ComplexSettingParser extends DynamicSettingParser implements ComplexSettingParserInterface
{
    /**
     * Regular expression that matches a dynamic variable.
     *
     * @var string
     */
    private $dynamicSettingRegex;

    public function __construct()
    {
        $boundaryDelimiter = preg_quote(static::BOUNDARY_DELIMITER, '/');
        $this->dynamicSettingRegex = sprintf(
            '%s[a-zA-Z0-9_.-]+(?:(?:%s[a-zA-Z0-9_]+)(?:%s[a-zA-Z0-9_.-]+)?)?%s',
            $boundaryDelimiter,
            static::INNER_DELIMITER,
            static::INNER_DELIMITER,
            $boundaryDelimiter
        );
    }

    /**
     * In addition to the parent's test, verifies the variables with a regexp.
     *
     * {@inheritdoc}
     */
    public function isDynamicSetting($setting)
    {
        if (parent::isDynamicSetting($setting) === false) {
            return false;
        }

        return (bool)preg_match('/^' . $this->dynamicSettingRegex . '$/', $setting);
    }

    public function containsDynamicSettings($string)
    {
        return count($this->matchDynamicSettings($string)) > 0;
    }

    /**
     * Matches all dynamic settings in $string.
     *
     * Example: '/tmp/$var_dir/$storage_dir' => ['$var_dir$', '$storage_dir']
     *
     * @param string $string
     *
     * @return array
     */
    protected function matchDynamicSettings($string)
    {
        preg_match_all('/' . $this->dynamicSettingRegex . '/', $string, $matches, PREG_PATTERN_ORDER);

        return $matches[0];
    }

    public function parseComplexSetting($string)
    {
        return $this->matchDynamicSettings($string);
    }
}
