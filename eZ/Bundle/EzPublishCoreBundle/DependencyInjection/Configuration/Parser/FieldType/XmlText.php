<?php
/**
 * File containing the XmlText class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\FieldType;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\AbstractFieldTypeParser;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * Configuration parser handling XmlText field type related config
 */
class XmlText extends AbstractFieldTypeParser
{
    /**
     * Returns the fieldType identifier the config parser works for.
     * This is to create the right configuration node under system.<siteaccess_name>.fieldtypes.
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return "ezxml";
    }

    /**
     * Adds semantic configuration definition.
     *
     * @param \Symfony\Component\Config\Definition\Builder\NodeBuilder $nodeBuilder Node just under ezpublish.system.<siteaccess>
     *
     * @return void
     */
    public function addFieldTypeSemanticConfig( NodeBuilder $nodeBuilder )
    {
        $nodeBuilder
            ->arrayNode( 'custom_tags' )
                ->info( 'Custom XSL stylesheets to use for XmlText transformation to HTML5. Useful for "custom tags".' )
                ->example(
                    array(
                        'path' => '%kernel.root_dir%/../src/Acme/TestBundle/Resources/myTag.xsl',
                        'priority' => 10
                    )
                )
                ->prototype( 'array' )
                    ->children()
                        ->scalarNode( 'path' )
                            ->info( 'Path of the XSL stylesheet to load.' )
                            ->isRequired()
                        ->end()
                        ->integerNode( 'priority' )
                            ->info( 'Priority in the loading order. A high value will have higher precedence in overriding XSL templates.' )
                            ->defaultValue( 0 )
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    public function mapConfig( array &$scopeSettings, $currentScope, ContextualizerInterface $contextualizer )
    {
        if ( !empty( $scopeSettings['fieldtypes'] ) )
        {
            // Workaround to be able to use Contextualizer::mapConfigArray() which only supports first level entries.
            if ( isset( $scopeSettings['fieldtypes']['ezxml']['custom_tags'] ) )
            {
                $scopeSettings['fieldtypes.ezxml.custom_xsl'] = $scopeSettings['fieldtypes']['ezxml']['custom_tags'];
                unset( $scopeSettings['fieldtypes']['ezxml']['custom_tags'] );
            }
        }
    }

    public function postMap( array $config, ContextualizerInterface $contextualizer )
    {
        $contextualizer->mapConfigArray( 'fieldtypes.ezxml.custom_xsl', $config );
    }
}
