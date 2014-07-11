<?php
/**
 * File containing the ScopeConfiguration class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration;

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
abstract class ScopeConfiguration implements ConfigurationInterface
{
    /**
     * Generates the context node under which context based configuration will be defined.
     *
     * @param \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition $rootNode Node under which the generated node will be placed.
     * @param string $scopeNodeName
     *
     * @return \Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition
     */
    public function generateScopeBaseNode( ArrayNodeDefinition $rootNode, $scopeNodeName = 'system' )
    {
        $contextNode = $rootNode
            ->children()
                ->arrayNode( $scopeNodeName )
                    ->info( 'System configuration. First key is always a siteaccess or siteaccess group name' )
                    ->example(
                        array(
                            'ezdemo_site'      => array(
                                'languages'        => array( 'eng-GB', 'fre-FR' ),
                                'content'          => array(
                                    'view_cache'   => true,
                                    'ttl_cache'    => true,
                                    'default_ttl'  => 30
                                )
                            ),
                            'ezdemo_group'     => array(
                                'repository' => 'my_repository'
                            )
                        )
                    )
                    ->useAttributeAsKey( 'siteaccess_name' )
                    ->requiresAtLeastOneElement()
                    ->normalizeKeys( false )
                    ->prototype( 'array' )
                        ->children();

        return $contextNode;
    }
}
