<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Compiler;

use eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\SlugConverter;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This compiler pass will create configuration injected into SlugConverter responsible for url pattern creation.
 */
class SlugConverterConfigurationPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('ezpublish.persistence.slug_converter')) {
            return;
        }
        $slugConverterDefinition = $container->getDefinition('ezpublish.persistence.slug_converter');

        $parameterConfiguration = $slugConverterDefinition->getArgument(1);
        $semanticConfiguration = $container->getParameter('ezpublish.url_alias.slug_converter_config');

        $mergedConfiguration = $parameterConfiguration;

        if (isset($semanticConfiguration['transformation'])) {
            $mergedConfiguration['transformation'] = $semanticConfiguration['transformation'];
        }

        if (isset($semanticConfiguration['separator'])) {
            $mergedConfiguration['wordSeparatorName'] = $semanticConfiguration['separator'];
        }

        $transformationGroups = $parameterConfiguration['transformationGroups'] ?? SlugConverter::DEFAULT_CONFIGURATION['transformationGroups'];

        if (isset($semanticConfiguration['transformationGroups'])) {
            $mergedConfiguration['transformationGroups'] = array_merge_recursive(
                $transformationGroups,
                $semanticConfiguration['transformationGroups']
            );
        }

        if (isset($mergedConfiguration['transformation']) &&
            !array_key_exists($mergedConfiguration['transformation'], $mergedConfiguration['transformationGroups'])) {
            throw new InvalidConfigurationException(sprintf(
                "Unknown transformation group selected: '%s'.\nAvailable ones: '%s'",
                $mergedConfiguration['transformation'],
                implode(', ', array_keys($mergedConfiguration['transformationGroups']))
            ));
        }

        $slugConverterDefinition->setArgument(1, $mergedConfiguration);
    }
}
