<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\DependencyInjection\Compiler\ComplexSettings;

class ComplexSetting
{
    public $settingString;

    public $dynamicSettings;

    /**
     * @param       $settingsString
     * @param array $dynamicSettings
     */
    public function __construct( $settingsString, array $dynamicSettings )
    {
        $this->settingString = $settingsString;
        $this->dynamicSettings = $dynamicSettings;
    }
}
