<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\DependencyInjection\Compiler\ComplexSettings;

/**
 * Parses a string that contains dynamic settings ($foo;eng;bar$).
 *
 * Example: "$var_dir$/$storage_dir$"
 */
class ComplexSettingParser
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
        $dollarsCount = substr_count( $string, '$' );
        if ( $dollarsCount < 2 )
        {
            return false;
        }
    }

    /**
     * Tests if $string is a dynamic setting, meaning that it only contains the setting and nothing else
     *
     * @param string $string
     *
     * @return bool
     */
    public function isDynamicSetting( $string )
    {
        return ( preg_match( "^\$[a-z0-9_\.]+(?:;[a-z0-9_\.]+){0,2}\$$", $string ) );
    }

    /**
     * Parses a complex setting into a ComplexSetting object
     *
     * @param string $string
     *
     * @return ComplexSetting
     */
    public function getDynamicSetting( $string )
    {
        $elements = preg_split( "^\$[a-z0-9_\.]+(?:;[a-z0-9_\.]+){0,2}\$$", $string );

        return new ComplexSetting( $string, $elements );
    }
}
