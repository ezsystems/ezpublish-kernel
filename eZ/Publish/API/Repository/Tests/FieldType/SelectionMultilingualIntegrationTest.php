<?php

/**
 * File contains: eZ\Publish\API\Repository\Tests\FieldType\SelectionIntegrationTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests\FieldType;

/**
 * Integration test for use field type.
 *
 * @group integration
 * @group field-type
 */
class SelectionMultilingualIntegrationTest extends SelectionIntegrationTest
{
    /**
     * Get expected settings schema.
     *
     * @return array
     */
    public function getSettingsSchema()
    {
        return array_merge(
            parent::getSettingsSchema(),
            [
                'multilingualOptions' => [
                    'type' => 'hash',
                    'default' => [],
                ],
            ]
        );
    }

    /**
     * Get a valid $fieldSettings value.
     *
     * @return mixed
     */
    public function getValidFieldSettings()
    {
        return
            [
                'isMultiple' => true,
                'options' => [
                    0 => 'A first',
                    1 => 'Bielefeld',
                    2 => 'Sindelfingen',
                    3 => 'Turtles',
                    4 => 'Zombies',
                ],
                'multilingualOptions' => [
                    'eng-GB' => [
                        0 => 'A first',
                        1 => 'Bielefeld',
                        2 => 'Sindelfingen',
                        3 => 'Turtles',
                        4 => 'Zombies',
                    ],
                    'eng-US' => [
                        0 => 'Missouri',
                        1 => 'Mississippi',
                        2 => 'Yukon',
                        3 => 'Rio Grande',
                        4 => 'Arkansas',
                    ],
                    'ger-DE' => [
                        0 => 'Zuerst',
                        1 => 'Zweite',
                        2 => 'Dritte',
                    ],
                ],
            ];
    }

    public function getValidFieldConfiguration(): array
    {
        return [
            'names' => [
                'eng-GB' => 'Test',
                'eng-US' => 'US TEST',
                'ger-DE' => 'GER Test',
            ],
        ];
    }

    public function getFieldName()
    {
        return 'Missouri' . ' ' . 'Yukon';
    }

    protected function getAdditionallyIndexedFieldData()
    {
        return [
            [
                'selected_option_value',
                'Mississippi',
                'Yukon',
            ],
            [
                'sort_value',
                '1',
                '2',
            ],
        ];
    }

    protected function getAdditionallyIndexedMultivaluedFieldData()
    {
        return [
            [
                'selected_option_value',
                ['Missouri', 'Mississippi'],
                ['Yukon', 'Rio Grande', 'Arkansas'],
            ],
        ];
    }

    protected function getFullTextIndexedFieldData()
    {
        return [
            ['Mississippi', 'Yukon'],
        ];
    }
}
