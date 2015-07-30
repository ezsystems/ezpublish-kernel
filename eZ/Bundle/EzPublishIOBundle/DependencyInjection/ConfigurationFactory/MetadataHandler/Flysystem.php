<?php

/**
 * This file is part of the eZ Publish Legacy package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\DependencyInjection\ConfigurationFactory\MetadataHandler;

use eZ\Bundle\EzPublishIOBundle\DependencyInjection\ConfigurationFactory\Flysystem as BaseFactory;

class Flysystem extends BaseFactory
{
    public function getParentServiceId()
    {
        return 'ezpublish.core.io.metadata_handler.flysystem';
    }
}
