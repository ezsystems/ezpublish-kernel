<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration;

use Symfony\Component\DependencyInjection\ContainerBuilder;

abstract class ContainerConfigBuilder implements ConfigBuilderInterface
{
    /** @var ContainerBuilder */
    protected $containerBuilder;

    public function __construct(ContainerBuilder $containerBuilder)
    {
        $this->containerBuilder = $containerBuilder;
    }
}
