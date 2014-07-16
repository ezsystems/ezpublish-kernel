<?php
/**
 * File containing the HookableConfigurationMapper class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware;

interface HookableConfigurationMapper extends ConfigurationMapper
{
    public function preMap( array $config, $object );

    public function postMap( array $config, $object );
}
