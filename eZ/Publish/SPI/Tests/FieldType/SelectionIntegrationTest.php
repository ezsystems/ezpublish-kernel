<?php

/**
 * File contains: eZ\Publish\SPI\Tests\FieldType\SelectionIntegrationTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Tests\FieldType;

use eZ\Publish\Core\Persistence\Legacy;
use eZ\Publish\Core\FieldType\FieldSettings;
use eZ\Publish\Core\FieldType;
use eZ\Publish\SPI\Persistence\Content;

/**
 * Integration test for legacy storage field types.
 *
 * @group integration
 */
class SelectionIntegrationTest extends BaseIntegrationTest
{
    /**
     * Get name of tested field type.
     *
     * @return string
     */
    public function getTypeName()
    {
        return 'ezselection';
    }

    /**
     * Get handler with required custom field types registered.
     *
     * @return Handler
     */
    public function getCustomHandler()
    {
        $fieldType = new FieldType\Selection\Type();
        $fieldType->setTransformationProcessor($this->getTransformationProcessor());
        $languageService = self::$container->get('ezpublish.api.service.language');

        return $this->getHandler(
            'ezselection',
            $fieldType,
            new Legacy\Content\FieldValue\Converter\SelectionConverter($languageService),
            new FieldType\NullStorage()
        );
    }

    /**
     * Returns the FieldTypeConstraints to be used to create a field definition
     * of the FieldType under test.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldTypeConstraints
     */
    public function getTypeConstraints()
    {
        return new Content\FieldTypeConstraints(
            [
                'validators' => null,
                'fieldSettings' => new FieldSettings(
                    [
                        'isMultiple' => true,
                        'options' => [
                            1 => 'First',
                            2 => 'Second',
                            3 => 'Sindelfingen',
                        ],
                        'multilingualOptions' => [
                            'ger-DE' => [
                                1 => 'Zuerst',
                                2 => 'Zweite',
                            ],
                            'eng-US' => [
                                1 => 'ML First',
                                2 => 'ML Second',
                                3 => 'ML Sindelfingen',
                            ],
                        ],
                    ]
                ),
            ]
        );
    }

    /**
     * Get field definition data values.
     *
     * This is a PHPUnit data provider
     *
     * @return array
     */
    public function getFieldDefinitionData()
    {
        return [
            ['fieldType', 'ezselection'],
            [
                'fieldTypeConstraints',
                new Content\FieldTypeConstraints(
                    [
                        'validators' => null,
                        'fieldSettings' => new FieldSettings(
                            [
                                'isMultiple' => true,
                                'options' => [
                                    1 => 'ML First',
                                    2 => 'ML Second',
                                    3 => 'ML Sindelfingen',
                                ],
                                'multilingualOptions' => [
                                    'ger-DE' => [
                                        1 => 'Zuerst',
                                        2 => 'Zweite',
                                    ],
                                    'eng-US' => [
                                        1 => 'ML First',
                                        2 => 'ML Second',
                                        3 => 'ML Sindelfingen',
                                    ],
                                ],
                            ]
                        ),
                    ]
                ),
            ],
        ];
    }

    /**
     * Get initial field value.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function getInitialValue()
    {
        return new Content\FieldValue(
            [
                'data' => [1, 3],
                'externalData' => null,
                'sortKey' => '1-3',
            ]
        );
    }

    /**
     * Get update field value.
     *
     * Use to update the field
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function getUpdatedValue()
    {
        return new Content\FieldValue(
            [
                'data' => [2],
                'externalData' => null,
                'sortKey' => '2',
            ]
        );
    }

    /**
     * Performs the creation of the content type with a field of the field type
     * under test.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type
     */
    protected function createContentType()
    {
        $createStruct = new Content\Type\CreateStruct(
            [
                'name' => [
                    'eng-US' => 'Test',
                    'ger-DE' => 'Test NOR',
                ],
                'identifier' => 'test-' . $this->getTypeName(),
                'status' => 0,
                'creatorId' => 14,
                'created' => time(),
                'modifierId' => 14,
                'modified' => time(),
                'initialLanguageId' => 2,
                'remoteId' => 'abcdef',
            ]
        );

        $createStruct->fieldDefinitions = [
            new Content\Type\FieldDefinition(
                [
                    'name' => [
                        'eng-US' => 'Name',
                        'ger-DE' => 'Name NOR',
                    ],
                    'identifier' => 'name',
                    'fieldGroup' => 'main',
                    'position' => 1,
                    'fieldType' => 'ezstring',
                    'isTranslatable' => false,
                    'isRequired' => true,
                    'mainLanguageCode' => 'eng-US',
                ]
            ),
            new Content\Type\FieldDefinition(
                [
                    'name' => [
                        'eng-US' => 'Data',
                        'ger-DE' => 'Data NOR',
                    ],
                    'identifier' => 'data',
                    'fieldGroup' => 'main',
                    'position' => 2,
                    'fieldType' => $this->getTypeName(),
                    'isTranslatable' => false,
                    'isRequired' => true,
                    'fieldTypeConstraints' => $this->getTypeConstraints(),
                    'mainLanguageCode' => 'eng-US',
                ]
            ),
        ];

        $handler = $this->getCustomHandler();
        $contentTypeHandler = $handler->contentTypeHandler();

        return $contentTypeHandler->create($createStruct);
    }
}
