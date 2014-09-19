<?php
/**
 * File containing the Languages class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishIOBundle\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\AbstractParser;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class IO extends AbstractParser
{
    /**
     * Adds semantic configuration definition.
     *
     * @param \Symfony\Component\Config\Definition\Builder\NodeBuilder $nodeBuilder Node just under ezpublish.system.<siteaccess>
     *
     * @return void
     */
    public function addSemanticConfig( NodeBuilder $nodeBuilder )
    {
        $nodeBuilder
            ->scalarNode( 'handler' )
                ->info( 'IO handler uses to manipulate binary files' )
                ->example( 'legacy_kernel' )
            ->end();
    }

    public function preMap( array $config, ContextualizerInterface $contextualizer )
    {
    }

    public function mapConfig( array &$scopeSettings, $currentScope, ContextualizerInterface $contextualizer )
    {
        if ( isset( $scopeSettings['handler'] ) )
        {
            $handlersMap = $contextualizer->getContainer()->getParameter( 'ez_io.handlers_map' );
            $handlerId = $scopeSettings['handler'];
            if ( !isset($handlersMap[$handlerId] ) )
            {
                throw new InvalidConfigurationException(
                    "Unknown IO handler {$handlerId}. Possible values: " . implode( ', ', array_keys( $handlersMap ) )
                );
            }
            $contextualizer->setContextualParameter( 'handler', $currentScope, $scopeSettings['handler'] );
        }
    }

    public function postMap( array $config, ContextualizerInterface $contextualizer )
    {
    }
}
