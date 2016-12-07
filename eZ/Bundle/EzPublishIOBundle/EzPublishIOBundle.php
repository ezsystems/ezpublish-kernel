<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle;

use eZ\Bundle\EzPublishIOBundle\DependencyInjection\Compiler;
use eZ\Bundle\EzPublishIOBundle\DependencyInjection\ConfigurationFactory;
use eZ\Bundle\EzPublishIOBundle\DependencyInjection\EzPublishIOExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class EzPublishIOBundle extends Bundle
{
    /** @var \eZ\Bundle\EzPublishIOBundle\DependencyInjection\EzPublishIOExtension */
    protected $extension;

    public function build(ContainerBuilder $container)
    {
        $extension = $this->getContainerExtension();
        $container->addCompilerPass(
            new Compiler\IOConfigurationPass(
                $extension->getMetadataHandlerFactories(),
                $extension->getBinarydataHandlerFactories()
            )
        );
        $container->addCompilerPass(new Compiler\MigrationFileListerPass());
        parent::build($container);
    }

    public function getContainerExtension()
    {
        if (!isset($this->extension)) {
            $this->extension = new EzPublishIOExtension();
            $this->extension->addMetadataHandlerFactory('flysystem', new ConfigurationFactory\MetadataHandler\Flysystem());
            $this->extension->addMetadataHandlerFactory('legacy_dfs_cluster', new ConfigurationFactory\MetadataHandler\LegacyDFSCluster());
            $this->extension->addBinarydataHandlerFactory('flysystem', new ConfigurationFactory\BinarydataHandler\Flysystem());
        }

        return $this->extension;
    }
}
