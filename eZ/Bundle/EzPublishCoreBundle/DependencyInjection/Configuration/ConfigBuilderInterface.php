<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration;

use Symfony\Component\Config\Resource\ResourceInterface;

/**
 * Interface for config builders.
 * Config builders can be used to add/extend configuration.
 */
interface ConfigBuilderInterface
{
    /**
     * Adds config to the builder.
     *
     * @param array $config
     */
    public function addConfig(array $config);

    /**
     * Adds given resource, which would typically be added to container resources.
     *
     * @param ResourceInterface $resource
     */
    public function addResource(ResourceInterface $resource);
}
