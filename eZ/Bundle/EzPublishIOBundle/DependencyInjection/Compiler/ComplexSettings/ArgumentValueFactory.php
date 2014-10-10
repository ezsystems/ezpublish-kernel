<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\DependencyInjection\Compiler\ComplexSettings;

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
class ArgumentValueFactory
{
    private $argumentString = '';

    private $dynamicSettings = array();

    public function __construct( $argumentString )
    {
        $this->argumentString = $argumentString;
    }

    public function setDynamicSetting( array $argumentString, $dynamicValue )
    {
        $this->dynamicSettings[$argumentString[0]] = $dynamicValue;
    }

    public function getArgumentValue()
    {
        $value = $this->argumentString;
        foreach ( $this->dynamicSettings as $dynamicSettingString => $dynamicSettingValue )
        {
            $value = str_replace( $dynamicSettingString, $dynamicSettingValue, $value );
        }
        return $value;
    }
}
