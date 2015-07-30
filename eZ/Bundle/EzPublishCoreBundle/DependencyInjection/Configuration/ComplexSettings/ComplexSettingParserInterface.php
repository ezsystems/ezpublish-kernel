<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ComplexSettings;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\DynamicSettingParserInterface;

/**
 * Parses a string that contains dynamic settings ($foo;eng;bar$).
 *
 * Example: "$var_dir$/$storage_dir$"
 */
interface ComplexSettingParserInterface extends DynamicSettingParserInterface
{
    /**
     * Tests if $string contains dynamic settings.
     *
     * @param string $string
     *
     * @return bool
     */
    public function containsDynamicSettings($string);

    /**
     * Parses dynamic settings.
     *
     * @param string $string
     *
     * @return array key: original string, value: dynamic settings
     */
    public function parseComplexSetting($string);
}
