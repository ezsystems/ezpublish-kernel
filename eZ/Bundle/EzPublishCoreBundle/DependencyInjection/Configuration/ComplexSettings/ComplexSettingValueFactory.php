<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ComplexSettings;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigResolver;

/**
 * Factory for complex dynamic settings resolution.
 *
 * Meant to be added, as a service, in place of a complex argument, containing one or more dynamic setting
 * within another string.
 *
 * During the ComplexSettingPass, an instance of this factory will be created, and will be added one addDynamicSetting
 * call per setting in the string. The settings in these calls will be replaced by the ConfigResolverCompilerPass.
 * When the services using those factories are built, every dynamic setting in the string
 * is resolved, and the setting is replaced with its value in the string, and returned.
 */
class ComplexSettingValueFactory
{
    /**
     * Can receive as many tuples of array( argumentName ), argumentValue as necessary
     *
     * @param $argumentString
     * @param string $dynamicSettingName..
     * @param string $dynamicSettingValue..
     *
     * @return string
     */
    public static function getArgumentValue( $argumentString )
    {
        $arguments = array_slice( func_get_args(), 1 );

        $value = $argumentString;
        while ( $dynamicSettingName = array_shift( $arguments ) )
        {
            $dynamicSettingName = $dynamicSettingName[0];
            $dynamicSettingValue = array_shift( $arguments );
            $value = str_replace( $dynamicSettingName, $dynamicSettingValue, $value );
        }

        return $value;
    }
}
