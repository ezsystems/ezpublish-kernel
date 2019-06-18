<?php

/**
 * File containing the ImageTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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

        if (!isset($_ENV['imagemagickConvertPath']) || !is_executable($_ENV['imagemagickConvertPath'])) {
            $this->markTestSkipped('Missing or mis-configured Imagemagick convert path.');
        }
    }

    protected function getMinimalConfiguration()
    {
        $this->config = Yaml::parse(file_get_contents(__DIR__ . '/../../Fixtures/ezpublish_image.yml'));
        $this->config += [
            'imagemagick' => [
                'enabled' => true,
                'path' => $_ENV['imagemagickConvertPath'],
            ],
        ];

        return $this->config;
    }

    protected function getContainerExtensions()
    {
        return [
            new EzPublishCoreExtension([new Image()]),
        ];
    }

    public function testVariations()
    {
        $this->load();

        $expectedParsedVariations = [];
        foreach ($this->config['system'] as $sa => $saConfig) {
            $expectedParsedVariations[$sa] = [];
            foreach ($saConfig['image_variations'] as $variationName => $imageVariationConfig) {
                $imageVariationConfig['post_processors'] = [];
                foreach ($imageVariationConfig['filters'] as $i => $filter) {
                    $imageVariationConfig['filters'][$filter['name']] = $filter['params'];
                    unset($imageVariationConfig['filters'][$i]);
                }
                $expectedParsedVariations[$sa][$variationName] = $imageVariationConfig;
            }
        }

        $expected = $expectedParsedVariations['ezdemo_group'] + $this->container->getParameter('ezsettings.default.image_variations');
        $this->assertConfigResolverParameterValue('image_variations', $expected, 'ezdemo_site', false);
        $this->assertConfigResolverParameterValue('image_variations', $expected, 'ezdemo_site_admin', false);
        $this->assertConfigResolverParameterValue(
            'image_variations',
            $expected + $expectedParsedVariations['fre'],
            'fre',
            false
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testPrePostParameters()
    {
        $this->load(
            [
                'system' => [
                    'ezdemo_site' => [
                        'imagemagick' => [
                            'pre_parameters' => '-foo -bar',
                            'post_parameters' => '-baz',
                        ],
                    ],
                ],
            ]
        );
    }
}
