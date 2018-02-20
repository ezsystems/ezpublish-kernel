<?php

/**
 * File containing the RichTextTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\Parser\FieldType;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension;
use eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\Parser\AbstractParserTestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\FieldType\RichText as RichTextConfigParser;
use Symfony\Component\Yaml\Yaml;

class RichTextTest extends AbstractParserTestCase
{
    /**
     * Return an array of container extensions you need to be registered for each test (usually just the container
     * extension you are testing.
     *
     * @return ExtensionInterface[]
     */
    protected function getContainerExtensions()
    {
        return array(
            new EzPublishCoreExtension(array(new RichTextConfigParser())),
        );
    }

    protected function getMinimalConfiguration()
    {
        return Yaml::parse(file_get_contents(__DIR__ . '/../../../Fixtures/FieldType/RichText/ezrichtext.yml'));
    }

    public function testDefaultContentSettings()
    {
        $this->load();

        $this->assertConfigResolverParameterValue(
            'fieldtypes.ezrichtext.tags.default',
            array(
                'template' => 'EzPublishCoreBundle:FieldType/RichText/tag:default.html.twig',
            ),
            'ezdemo_site'
        );
        $this->assertConfigResolverParameterValue(
            'fieldtypes.ezrichtext.output_custom_xsl',
            array(
                0 => array(
                    'path' => '%kernel.root_dir%/../vendor/ezsystems/ezpublish-kernel/eZ/Publish/Core/FieldType/RichText/Resources/stylesheets/docbook/xhtml5/output/core.xsl',
                    'priority' => 0,
                ),
            ),
            'ezdemo_site'
        );
    }

    /**
     * Test Rich Text Custom Tags invalid settings, like enabling undefined Custom Tag.
     */
    public function testRichTextCustomTagsInvalidSettings()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('Unknown RichText Custom Tag \'foo\'');

        $this->load(
            [
                'system' => [
                    'ezdemo_site' => [
                        'fieldtypes' => [
                            'ezrichtext' => [
                                'custom_tags' => ['foo'],
                            ],
                        ],
                    ],
                ],
            ]
        );
        $this->assertConfigResolverParameterValue(
            'fieldtypes.ezrichtext.custom_tags',
            ['foo'],
            'ezdemo_site'
        );
    }

    /**
     * @dataProvider richTextSettingsProvider
     */
    public function testRichTextSettings(array $config, array $expected)
    {
        $this->load(
            array(
                'system' => array(
                    'ezdemo_site' => $config,
                ),
            )
        );

        foreach ($expected as $key => $val) {
            $this->assertConfigResolverParameterValue($key, $val, 'ezdemo_site');
        }
    }

    public function richTextSettingsProvider()
    {
        return array(
            array(
                array(
                    'fieldtypes' => array(
                        'ezrichtext' => array(
                            'output_custom_tags' => array(
                                array('path' => '/foo/bar.xsl', 'priority' => 123),
                                array('path' => '/foo/custom.xsl', 'priority' => -10),
                                array('path' => '/another/custom.xsl', 'priority' => 27),
                            ),
                        ),
                    ),
                ),
                array(
                    'fieldtypes.ezrichtext.output_custom_xsl' => array(
                        // Default settings will be added
                        array('path' => '%kernel.root_dir%/../vendor/ezsystems/ezpublish-kernel/eZ/Publish/Core/FieldType/RichText/Resources/stylesheets/docbook/xhtml5/output/core.xsl', 'priority' => 0),
                        array('path' => '/foo/bar.xsl', 'priority' => 123),
                        array('path' => '/foo/custom.xsl', 'priority' => -10),
                        array('path' => '/another/custom.xsl', 'priority' => 27),
                    ),
                ),
            ),
            array(
                array(
                    'fieldtypes' => array(
                        'ezrichtext' => array(
                            'edit_custom_tags' => array(
                                array('path' => '/foo/bar.xsl', 'priority' => 123),
                                array('path' => '/foo/custom.xsl', 'priority' => -10),
                                array('path' => '/another/custom.xsl', 'priority' => 27),
                            ),
                        ),
                    ),
                ),
                array(
                    'fieldtypes.ezrichtext.edit_custom_xsl' => array(
                        // Default settings will be added
                        array('path' => '%kernel.root_dir%/../vendor/ezsystems/ezpublish-kernel/eZ/Publish/Core/FieldType/RichText/Resources/stylesheets/docbook/xhtml5/edit/core.xsl', 'priority' => 0),
                        array('path' => '/foo/bar.xsl', 'priority' => 123),
                        array('path' => '/foo/custom.xsl', 'priority' => -10),
                        array('path' => '/another/custom.xsl', 'priority' => 27),
                    ),
                ),
            ),
            array(
                array(
                    'fieldtypes' => array(
                        'ezrichtext' => array(
                            'input_custom_tags' => array(
                                array('path' => '/foo/bar.xsl', 'priority' => 123),
                                array('path' => '/foo/custom.xsl', 'priority' => -10),
                                array('path' => '/another/custom.xsl', 'priority' => 27),
                            ),
                        ),
                    ),
                ),
                array(
                    'fieldtypes.ezrichtext.input_custom_xsl' => array(
                        // No default settings for input
                        array('path' => '/foo/bar.xsl', 'priority' => 123),
                        array('path' => '/foo/custom.xsl', 'priority' => -10),
                        array('path' => '/another/custom.xsl', 'priority' => 27),
                    ),
                ),
            ),
            array(
                array(
                    'fieldtypes' => array(
                        'ezrichtext' => array(
                            'tags' => array(
                                'default' => array(
                                    'template' => 'MyBundle:FieldType/RichText/tag:default.html.twig',
                                    'config' => array(
                                        'watch' => 'out',
                                        'only' => 'first level',
                                        'can' => 'be mapped to ezxml',
                                    ),
                                ),
                                'math_equation' => array(
                                    'template' => 'MyBundle:FieldType/RichText/tag:math_equation.html.twig',
                                    'config' => array(
                                        'some' => 'arbitrary',
                                        'hash' => array(
                                            'structure' => 12345,
                                            'works' => array(
                                                'drink' => 'beer',
                                                'explode' => false,
                                            ),
                                            'does not work' => array(
                                                'drink' => 'whiskey',
                                                'deeble' => true,
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'fieldtypes.ezrichtext.tags.default' => array(
                        'template' => 'MyBundle:FieldType/RichText/tag:default.html.twig',
                        'config' => array(
                            'watch' => 'out',
                            'only' => 'first level',
                            'can' => 'be mapped to ezxml',
                        ),
                    ),
                    'fieldtypes.ezrichtext.tags.math_equation' => array(
                        'template' => 'MyBundle:FieldType/RichText/tag:math_equation.html.twig',
                        'config' => array(
                            'some' => 'arbitrary',
                            'hash' => array(
                                'structure' => 12345,
                                'works' => array(
                                    'drink' => 'beer',
                                    'explode' => false,
                                ),
                                'does not work' => array(
                                    'drink' => 'whiskey',
                                    'deeble' => true,
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            array(
                array(
                    'fieldtypes' => array(
                        'ezrichtext' => array(
                            'custom_tags' => array('video', 'equation'),
                        ),
                    ),
                ),
                array(
                    'fieldtypes.ezrichtext.custom_tags' => array('video', 'equation'),
                ),
            ),
            array(
                array(
                    'fieldtypes' => array(
                        'ezrichtext' => array(
                            'embed' => array(
                                'content' => array(
                                    'template' => 'MyBundle:FieldType/RichText/embed:content.html.twig',
                                    'config' => array(
                                        'have' => array(
                                            'spacesuit' => array(
                                                'travel' => true,
                                            ),
                                        ),
                                    ),
                                ),
                                'location_inline_denied' => array(
                                    'template' => 'MyBundle:FieldType/RichText/embed:location_inline_denied.html.twig',
                                    'config' => array(
                                        'have' => array(
                                            'location' => array(
                                                'index' => true,
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'fieldtypes.ezrichtext.embed.content' => array(
                        'template' => 'MyBundle:FieldType/RichText/embed:content.html.twig',
                        'config' => array(
                            'have' => array(
                                'spacesuit' => array(
                                    'travel' => true,
                                ),
                            ),
                        ),
                    ),
                    'fieldtypes.ezrichtext.embed.location_inline_denied' => array(
                        'template' => 'MyBundle:FieldType/RichText/embed:location_inline_denied.html.twig',
                        'config' => array(
                            'have' => array(
                                'location' => array(
                                    'index' => true,
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );
    }
}
