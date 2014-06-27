<?php
/**
 * File containing the ContentTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\Content as ContentConfigParser;
use Symfony\Component\Yaml\Yaml;

class ContentTest extends AbstractParserTestCase
{
    protected function getContainerExtensions()
    {
        return array(
            new EzPublishCoreExtension( array( new ContentConfigParser ) )
        );
    }

    protected function getMinimalConfiguration()
    {
        return Yaml::parse( __DIR__ . '/../../Fixtures/ezpublish_minimal.yml' );
    }

    public function testDefaultContentSettings()
    {
        $this->load();

        $this->assertConfigResolverParameterValue( 'content.view_cache', true, 'ezdemo_site' );
        $this->assertConfigResolverParameterValue( 'content.ttl_cache', true, 'ezdemo_site' );
        $this->assertConfigResolverParameterValue( 'content.default_ttl', 60, 'ezdemo_site' );
    }

    /**
     * @dataProvider contentSettingsProvider
     */
    public function testContentSettings( array $config, array $expected )
    {
        $this->load(
            array(
                'system' => array(
                    'ezdemo_site' => $config
                )
            )
        );

        foreach ( $expected as $key => $val )
        {
            $this->assertConfigResolverParameterValue( $key, $val, 'ezdemo_site' );
        }
    }

    public function contentSettingsProvider()
    {
        return array(
            array(
                array(
                    'content' => array(
                        'view_cache' => true,
                        'ttl_cache' => true,
                        'default_ttl' => 100,
                    )
                ),
                array(
                    'content.view_cache' => true,
                    'content.ttl_cache' => true,
                    'content.default_ttl' => 100,
                )
            ),
            array(
                array(
                    'content' => array(
                        'view_cache' => false,
                        'ttl_cache' => false,
                        'default_ttl' => 123,
                    )
                ),
                array(
                    'content.view_cache' => false,
                    'content.ttl_cache' => false,
                    'content.default_ttl' => 123,
                )
            ),
            array(
                array(
                    'content' => array(
                        'view_cache' => false,
                    )
                ),
                array(
                    'content.view_cache' => false,
                    'content.ttl_cache' => true,
                    'content.default_ttl' => 60,
                )
            ),
            array(
                array(
                    'content' => array(
                        'tree_root' => array( 'location_id' => 123 ),
                    )
                ),
                array(
                    'content.view_cache' => true,
                    'content.ttl_cache' => true,
                    'content.default_ttl' => 60,
                    'content.tree_root.location_id' => 123,
                )
            ),
            array(
                array(
                    'content' => array(
                        'tree_root' => array(
                            'location_id' => 456,
                            'excluded_uri_prefixes' => array( '/media/images', '/products' )
                        ),
                    )
                ),
                array(
                    'content.view_cache' => true,
                    'content.ttl_cache' => true,
                    'content.default_ttl' => 60,
                    'content.tree_root.location_id' => 456,
                    'content.tree_root.excluded_uri_prefixes' => array( '/media/images', '/products' ),
                )
            ),
            array(
                array(
                    'content' => array(),
                    'fieldtypes' => array(
                        'ezxml' => array(
                            'custom_tags' => array(
                                array( 'path' => '/foo/bar.xsl', 'priority' => 123 ),
                                array( 'path' => '/foo/custom.xsl', 'priority' => -10 ),
                                array( 'path' => '/another/custom.xsl', 'priority' => 27 ),
                            )
                        )
                    )
                ),
                array(
                    'content.view_cache' => true,
                    'content.ttl_cache' => true,
                    'content.default_ttl' => 60,
                    'fieldtypes.ezxml.custom_xsl' => array(
                        // Default settings will be added
                        array( 'path' => '%kernel.root_dir%/../vendor/ezsystems/ezpublish-kernel/eZ/Publish/Core/FieldType/XmlText/Input/Resources/stylesheets/eZXml2Html5_core.xsl', 'priority' => 0 ),
                        array( 'path' => '%kernel.root_dir%/../vendor/ezsystems/ezpublish-kernel/eZ/Publish/Core/FieldType/XmlText/Input/Resources/stylesheets/eZXml2Html5_custom.xsl', 'priority' => 0 ),
                        array( 'path' => '/foo/bar.xsl', 'priority' => 123 ),
                        array( 'path' => '/foo/custom.xsl', 'priority' => -10 ),
                        array( 'path' => '/another/custom.xsl', 'priority' => 27 ),
                    ),
                )
            ),
            array(
                array(
                    'content' => array(),
                    'fieldtypes' => array(
                        'ezrichtext' => array(
                            'tags' => array(
                                'default' => array(
                                    'template' => 'MyBundle:FieldType/RichText/tag:default.html.twig',
                                ),
                                'math_equation' => array(
                                    'template' => 'MyBundle:FieldType/RichText/tag:math_equation.html.twig',
                                ),
                            )
                        )
                    )
                ),
                array(
                    'content.view_cache' => true,
                    'content.ttl_cache' => true,
                    'content.default_ttl' => 60,
                    'fieldtypes.ezrichtext.tags.default' => array(
                        'template' => 'MyBundle:FieldType/RichText/tag:default.html.twig',
                    ),
                    'fieldtypes.ezrichtext.tags.math_equation' => array(
                        'template' => 'MyBundle:FieldType/RichText/tag:math_equation.html.twig',
                    ),
                )
            ),
            array(
                array(
                    'content' => array(),
                    'fieldtypes' => array(
                        'ezrichtext' => array(
                            'embed' => array(
                                'content' => array(
                                    'template' => 'MyBundle:FieldType/RichText/embed:content.html.twig',
                                ),
                                'location_inline_denied' => array(
                                    'template' => 'MyBundle:FieldType/RichText/embed:location_inline_denied.html.twig',
                                ),
                            )
                        )
                    )
                ),
                array(
                    'content.view_cache' => true,
                    'content.ttl_cache' => true,
                    'content.default_ttl' => 60,
                    'fieldtypes.ezrichtext.embed.content' => array(
                        'template' => 'MyBundle:FieldType/RichText/embed:content.html.twig',
                    ),
                    'fieldtypes.ezrichtext.embed.location_inline_denied' => array(
                        'template' => 'MyBundle:FieldType/RichText/embed:location_inline_denied.html.twig',
                    ),
                )
            ),
        );
    }
}
