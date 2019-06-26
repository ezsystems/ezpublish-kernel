<?php

/**
 * File containing the ConfigParser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * Main configuration parser/mapper.
 * It acts as a proxy to inner parsers.
 */
class ConfigParser implements ParserInterface
{
    /** @var \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ParserInterface[] */
    private $configParsers;

    public function __construct(array $configParsers = [])
    {
        foreach ($configParsers as $parser) {
            if (!$parser instanceof ParserInterface) {
                throw new InvalidArgumentType(
                    'Inner config parser',
                    'eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ParserInterface',
                    $parser
                );
            }
        }

        $this->configParsers = $configParsers;
    }

    /**
     * @param \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ParserInterface[] $configParsers
     */
    public function setConfigParsers($configParsers)
    {
        $this->configParsers = $configParsers;
    }

    /**
     * @return \eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ParserInterface[]
     */
    public function getConfigParsers()
    {
        return $this->configParsers;
    }

    public function mapConfig(array &$scopeSettings, $currentScope, ContextualizerInterface $contextualizer)
    {
        foreach ($this->configParsers as $parser) {
            $parser->mapConfig($scopeSettings, $currentScope, $contextualizer);
        }
    }

    public function preMap(array $config, ContextualizerInterface $contextualizer)
    {
        foreach ($this->configParsers as $parser) {
            $parser->preMap($config, $contextualizer);
        }
    }

    public function postMap(array $config, ContextualizerInterface $contextualizer)
    {
        foreach ($this->configParsers as $parser) {
            $parser->postMap($config, $contextualizer);
        }
    }

    public function addSemanticConfig(NodeBuilder $nodeBuilder)
    {
        $fieldTypeNodeBuilder = $nodeBuilder
            ->arrayNode('fieldtypes')
            ->children();

        // Delegate to configuration parsers
        foreach ($this->configParsers as $parser) {
            if ($parser instanceof FieldTypeParserInterface) {
                $parser->addSemanticConfig($fieldTypeNodeBuilder);
            } else {
                $parser->addSemanticConfig($nodeBuilder);
            }
        }
    }
}
