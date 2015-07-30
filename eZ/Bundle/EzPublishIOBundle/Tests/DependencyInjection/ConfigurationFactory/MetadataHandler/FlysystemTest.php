<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\Tests\DependencyInjection\ConfigurationFactory\MetadataHandler;

class FlysystemTest
{
    /**
     * Returns an instance of the tested factory.
     *
     * @return \eZ\Bundle\EzPublishIOBundle\DependencyInjection\ConfigurationFactory\MetadataHandler\Flysystem
     */
    public function provideTestedFactory()
    {
        return new Flysystem();
    }

    /**
     * Returns the expected parent service id.
     */
    public function provideExpectedParentServiceId()
    {
        return 'ezpublish.core.io.metadata_handler.flysystem';
    }
}
