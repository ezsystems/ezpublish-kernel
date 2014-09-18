<?php
/**
 * File containing the ImageTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\Parser;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\Image;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension;
use Symfony\Component\Yaml\Yaml;

class ImageTest extends AbstractParserTestCase
{
    private $config;

    protected function setUp()
    {
        parent::setUp();

        if ( !isset( $_ENV['imagemagickConvertPath'] ) || !is_executable( $_ENV['imagemagickConvertPath'] ) )
        {
            $this->markTestSkipped( 'Missing or mis-configured Imagemagick convert path.' );
        }
    }

    protected function getMinimalConfiguration()
    {
        $this->config = Yaml::parse( __DIR__ . '/../../Fixtures/ezpublish_image.yml' );
        $this->config += array(
            'imagemagick' => array(
                'enabled' => true,
                'path' => $_ENV['imagemagickConvertPath']
            )
        );

        return $this->config;
    }

    protected function getContainerExtensions()
    {
        return array(
            new EzPublishCoreExtension( array( new Image() ) )
        );
    }

    public function testVariations()
    {
        $this->load();

        $expectedParsedVariations = array();
        foreach ( $this->config['system'] as $sa => $saConfig )
        {
            $expectedParsedVariations[$sa] = array();
            foreach ( $saConfig['image_variations'] as $variationName => $imageVariationConfig )
            {
                foreach ( $imageVariationConfig['filters'] as $i => $filter )
                {
                    $imageVariationConfig['filters'][$filter['name']] = $filter['params'];
                    unset( $imageVariationConfig['filters'][$i] );
                }
                $expectedParsedVariations[$sa][$variationName] = $imageVariationConfig;
            }
        }

        $expected = $expectedParsedVariations['ezdemo_group'] + $this->container->getParameter( 'ezsettings.default.image_variations' );
        $this->assertConfigResolverParameterValue( 'image_variations', $expected, 'ezdemo_site', false );
        $this->assertConfigResolverParameterValue( 'image_variations', $expected, 'ezdemo_site_admin', false );
        $this->assertConfigResolverParameterValue(
            'image_variations',
            $expected + $expectedParsedVariations['fre'],
            'fre',
            false
        );
    }

    /**
     * @dataProvider prePostParametersProvider
     */
    public function testPrePostParameters( array $config, array $expected )
    {
        $this->load( array( 'system' => $config ) );
        foreach ( $expected as $name => $values )
        {
            foreach ( $values as $sa => $expectedValue )
            {
                $this->assertConfigResolverParameterValue( $name, $expectedValue, $sa );
            }
        }
    }

    public function prePostParametersProvider()
    {
        return array(
            array(
                array(
                    'ezdemo_site' => array(
                        'imagemagick' => array(
                            'pre_parameters' => '-foo -bar',
                            'post_parameters' => '-baz'
                        )
                    )
                ),
                array(
                    'imagemagick.pre_parameters' => array(
                        'ezdemo_site' => '-foo -bar',
                        'fre' => null
                    ),
                    'imagemagick.post_parameters' => array(
                        'ezdemo_site' => '-baz',
                        'fre' => null
                    )
                )
            ),
            array(
                array(
                    'ezdemo_site' => array(
                        'imagemagick' => array(
                            'pre_parameters' => '-foo -bar',
                        )
                    )
                ),
                array(
                    'imagemagick.pre_parameters' => array(
                        'ezdemo_site' => '-foo -bar',
                        'fre' => null
                    ),
                    'imagemagick.post_parameters' => array(
                        'ezdemo_site' => null,
                        'fre' => null
                    )
                )
            ),
            array(
                array(
                    'ezdemo_site' => array(
                        'imagemagick' => array(
                            'post_parameters' => '-baz'
                        )
                    )
                ),
                array(
                    'imagemagick.pre_parameters' => array(
                        'ezdemo_site' => null,
                        'fre' => null
                    ),
                    'imagemagick.post_parameters' => array(
                        'ezdemo_site' => '-baz',
                        'fre' => null
                    )
                )
            ),
            array(
                array(
                    'fre' => array(
                        'imagemagick' => array(
                            'pre_parameters' => '-fre',
                            'post_parameters' => '-baz -fre'
                        )
                    )
                ),
                array(
                    'imagemagick.pre_parameters' => array(
                        'ezdemo_site' => null,
                        'fre' => '-fre'
                    ),
                    'imagemagick.post_parameters' => array(
                        'ezdemo_site' => null,
                        'fre' => '-baz -fre'
                    )
                )
            ),
        );
    }
}
