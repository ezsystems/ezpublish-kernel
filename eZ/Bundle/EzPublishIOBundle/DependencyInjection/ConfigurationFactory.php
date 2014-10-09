<?php
/**
 * This file is part of the eZ Publish Legacy package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;

interface ConfigurationFactory
{
    /**
     * Adds the handler's semantic configuration
     * @param NodeDefinition $nodeDefinition
     */
    public function addConfiguration( NodeDefinition $nodeDefinition );
}
