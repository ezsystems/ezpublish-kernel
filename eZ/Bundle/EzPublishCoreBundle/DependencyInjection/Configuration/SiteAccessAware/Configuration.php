<?php

/**
 * File containing the SiteAccess aware Configuration class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Base class to build scope based semantic configuration tree (aka SiteAccess aware configuration).
 * This is very helpful if you need to define specific configuration blocks which need to be repeated by scope/contexts.
 *
 * Example of scope (aka SiteAccesses) usage, "system" being the node under which scope based configuration take place.
 * Key is the context name.
 *
 * <code>
 * ezpublish:
 *     system:
 *         eng:
 *             languages:
 *                 - eng-GB
 *
 *         fre:
 *             languages:
 *                 - fre-FR
 *                 - eng-GB
 * </code>
 */
abstract class Configuration implements ConfigurationInterface
{
    /**
     * Generates the context node under which context based configuration will be defined.
     *
     * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $rootNode Node under which the generated node will be placed.
     * @param string $scopeNodeName
     *
     * @return \Symfony\Component\Config\Definition\Builder\NodeBuilder
     */
    public function generateScopeBaseNode(ArrayNodeDefinition $rootNode, $scopeNodeName = 'system')
    {
        $contextNode = $rootNode
            ->children()
                ->arrayNode($scopeNodeName)
                    ->info('System configuration. First key is always a siteaccess or siteaccess group name')
                    ->example(
                        [
                            'my_siteaccess' => [
                                'preferred_quote' => 'Let there be Light!',
                                'j_aime' => ['le_saucisson'],
                            ],
                            'my_siteaccess_group' => [
                                'j_aime' => ['la_truite_a_la_vapeur'],
                            ],
                        ]
                    )
                    ->useAttributeAsKey('siteaccess_name')
                    ->requiresAtLeastOneElement()
                    ->normalizeKeys(false)
                    ->prototype('array')
                        ->children();

        return $contextNode;
    }
}
