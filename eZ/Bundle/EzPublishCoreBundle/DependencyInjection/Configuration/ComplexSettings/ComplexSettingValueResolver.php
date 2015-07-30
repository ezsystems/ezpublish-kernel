<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ComplexSettings;

/**
 * Factory for complex dynamic settings resolution.
 *
 * Meant to be added, as a service, in place of a complex argument, containing one or more dynamic setting
 * within another string.
 *
 * During the ComplexSettingPass, complex settings will be replaced by a factory based on this class.
 *
 * Each setting is added twice:
 * - once with the $ trimmed, so that we know what is being replaced
 * - once with the $ untrimmed, so that the ConfigResolverPass transforms those into their value.
 *
 * When the services using those factories are built, every dynamic setting in the string
 * is resolved, and the setting is replaced with its value in the string, and returned.
 *
 * Example:
 * ```php
 * $argumentValue = ComplexSettingValueResolver::resolveSetting(
 *     '$var_dir$/$storage_dir$',
 *     'var_dir',
 *     '$var_dir$'
 *     'storage_dir',
 *     '$storage_dir$'
 * );
 * ```
 */
class ComplexSettingValueResolver
{
    /**
     * Can receive as many tuples of array( argumentName ), argumentValue as necessary.
     *
     * @param $argumentString
     * @param string $dynamicSettingName..
     * @param string $dynamicSettingValue..
     *
     * @return string
     */
    public function resolveSetting($argumentString)
    {
        $arguments = array_slice(func_get_args(), 1);

        $value = $argumentString;
        while ($dynamicSettingName = array_shift($arguments)) {
            $dynamicSettingValue = array_shift($arguments);
            $value = str_replace("\$$dynamicSettingName\$", $dynamicSettingValue, $value);
        }

        return $value;
    }
}
