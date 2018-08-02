<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\Parser\FieldType;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\Parser\FieldType\ImageAsset as ImageAssetConfigParser;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\EzPublishCoreExtension;
use eZ\Bundle\EzPublishCoreBundle\Tests\DependencyInjection\Configuration\Parser\AbstractParserTestCase;

class ImageAssetTest extends AbstractParserTestCase
{
    /**
     * @{@inheritdoc}
     */
    protected function getContainerExtensions()
    {
        return [
            new EzPublishCoreExtension([new ImageAssetConfigParser()]),
        ];
    }

    public function testDefaultImageAssetSettings()
    {
        $this->load();

        $this->assertConfigResolverParameterValue(
            'fieldtypes.ezimageasset.mappings',
            [
                'content_type_identifier' => 'image',
                'content_field_identifier' => 'image',
                'name_field_identifier' => 'name',
                'parent_location_id' => 51,
            ],
            'ezdemo_site'
        );
    }

    /**
     * @dataProvider imageAssetSettingsProvider
     */
    public function testImageAssetSettings(array $config, array $expected)
    {
        $this->load(
            [
                'system' => [
                    'ezdemo_site' => $config,
                ],
            ]
        );

        foreach ($expected as $key => $val) {
            $this->assertConfigResolverParameterValue($key, $val, 'ezdemo_site');
        }
    }

    public function imageAssetSettingsProvider(): array
    {
        return [
            [
                [
                    'fieldtypes' => [
                        'ezimageasset' => [
                            'content_type_identifier' => 'photo',
                            'content_field_identifier' => 'file',
                            'name_field_identifier' => 'title',
                            'parent_location_id' => 68,
                        ],
                    ],
                ],
                [
                    'fieldtypes.ezimageasset.mappings' => [
                        'content_type_identifier' => 'photo',
                        'content_field_identifier' => 'file',
                        'name_field_identifier' => 'title',
                        'parent_location_id' => 68,
                    ],
                ],
            ],
        ];
    }
}
