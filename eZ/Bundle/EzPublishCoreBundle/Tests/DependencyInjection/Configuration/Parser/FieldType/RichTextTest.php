<?php
/**
 * File containing the RichTextTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\Parser\FieldType;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension;
use eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\Parser\AbstractParserTestCase;
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
            new EzPublishCoreExtension( array( new RichTextConfigParser ) )
        );
    }

    protected function getMinimalConfiguration()
    {
        return Yaml::parse( __DIR__ . '/../../../Fixtures/ezpublish_minimal.yml' );
    }

    /**
     * @dataProvider richTextSettingsProvider
     */
    public function testRichTextSettings( array $config, array $expected )
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

    public function richTextSettingsProvider()
    {
        return array(
            array(
                array(
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
