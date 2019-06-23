<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishIOBundle\DependencyInjection\Compiler;

use ArrayObject;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

/**
 * This compiler pass will create the metadata and binarydata IO handlers depending on container configuration.
 *
 * @todo Refactor into two passes, since they're very very close.
 */
class IOConfigurationPass implements CompilerPassInterface
{
    /** @var \eZ\Bundle\EzPublishIOBundle\DependencyInjection\ConfigurationFactory[]|ArrayObject */
    private $metadataHandlerFactories;

    /** @var \eZ\Bundle\EzPublishIOBundle\DependencyInjection\ConfigurationFactory[]|ArrayObject */
    private $binarydataHandlerFactories;

    public function __construct(
        ArrayObject $metadataHandlerFactories = null,
        ArrayObject $binarydataHandlerFactories = null
    ) {
        $this->metadataHandlerFactories = $metadataHandlerFactories;
        $this->binarydataHandlerFactories = $binarydataHandlerFactories;
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @throws \LogicException
     */
    public function process(ContainerBuilder $container)
    {
        $ioMetadataHandlers = $container->hasParameter('ez_io.metadata_handlers') ?
            $container->getParameter('ez_io.metadata_handlers') :
            [];
        $this->processHandlers(
            $container,
            $container->getDefinition('ezpublish.core.io.metadata_handler.factory'),
            $ioMetadataHandlers,
            $this->metadataHandlerFactories,
            'ezpublish.core.io.metadata_handler.flysystem.default'
        );

        $ioBinarydataHandlers = $container->hasParameter('ez_io.binarydata_handlers') ?
            $container->getParameter('ez_io.binarydata_handlers') :
            [];
        $this->processHandlers(
            $container,
            $container->getDefinition('ezpublish.core.io.binarydata_handler.factory'),
            $ioBinarydataHandlers,
            $this->binarydataHandlerFactories,
            'ezpublish.core.io.binarydata_handler.flysystem.default'
        );

        // Unset parameters that are no longer required ?
    }

    /**
     * @param ContainerBuilder $container
     * @param Definition $factory The factory service that should receive the list of handlers
     * @param array $configuredHandlers Handlers configuration declared via semantic config
     * @param \eZ\Bundle\EzPublishIOBundle\DependencyInjection\ConfigurationFactory[]|ArrayObject $factories Map of alias => handler service id
     * @param string $defaultHandler default handler id
     *
     * @internal param $HandlerTypesMap
     */
    protected function processHandlers(
        ContainerBuilder $container,
        Definition $factory,
        array $configuredHandlers,
        ArrayObject $factories,
        $defaultHandler
    ) {
        $handlers = ['default' => $defaultHandler];

        foreach ($configuredHandlers as $name => $config) {
            $configurationFactory = $this->getFactory($factories, $config['type'], $container);

            $parentHandlerId = $configurationFactory->getParentServiceId();
            $handlerId = sprintf('%s.%s', $parentHandlerId, $name);
            $definition = $container->setDefinition($handlerId, new DefinitionDecorator($parentHandlerId));

            $configurationFactory->configureHandler($definition, $config);

            $handlers[$name] = $handlerId;
        }

        $factory->addMethodCall('setHandlersMap', [$handlers]);
    }

    /**
     * Returns from $factories the factory for handler $type.
     *
     * @param ContainerBuilder $container
     * @param \eZ\Bundle\EzPublishIOBundle\DependencyInjection\ConfigurationFactory[]|ArrayObject|ContainerAware[] $factories
     * @param string $type
     *
     * @return \eZ\Bundle\EzPublishIOBundle\DependencyInjection\ConfigurationFactory
     */
    protected function getFactory(ArrayObject $factories, $type, ContainerBuilder $container)
    {
        if (!isset($factories[$type])) {
            throw new InvalidConfigurationException("Unknown handler type $type");
        }
        if ($factories[$type] instanceof ContainerAwareInterface) {
            $factories[$type]->setContainer($container);
        }

        return $factories[$type];
    }
}
