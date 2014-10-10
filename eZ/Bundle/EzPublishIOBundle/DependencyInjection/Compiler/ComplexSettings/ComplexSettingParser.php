<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\DependencyInjection\Compiler\ComplexSettings;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\DynamicSettingParser;

/**
 * Parses a string that contains dynamic settings ($foo;eng;bar$).
 *
 * Example: "$var_dir$/$storage_dir$"
 */
class ComplexSettingParser extends DynamicSettingParser
{
    /**
     * Tests if $string contains dynamic settings
     *
     * @param string $string
     *
     * @return bool
     */
    public function containsDynamicSettings( $string )
    {
        return count( $this->matchDynamicSettings( $string ) ) > 0;
    }

    /**
     * Matches all dynamic settings in $string
     *
     * Example: '/tmp/$var_dir/$storage_dir' => ['$var_dir$', '$storage_dir']
     *
     * @param string $string
     *
     * @return array
     */
    protected function matchDynamicSettings( $string )
    {
        preg_match_all(
            '/\$[a-zA-Z0-9_.-]+(?:(?:;[a-zA-Z0-9_]+)(?:;[a-zA-Z0-9_.-]+)?)?\$/',
            $string,
            $matches,
            PREG_PATTERN_ORDER
        );

        return $matches[0];
    }

    /**
     * Parses dynamic settings
     *
     * @param string $string
     *
     * @return array key: original string, value: dynamic settings
     */
    public function parseComplexSetting( $string )
    {
        return $this->matchDynamicSettings( $string );
    }
}
