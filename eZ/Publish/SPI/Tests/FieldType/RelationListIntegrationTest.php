<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Tests\FieldType;

use eZ\Publish\Core\FieldType;
use eZ\Publish\Core\Persistence\Legacy;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Field;
use Ibexa\Core\Repository\Validator\TargetContentValidatorInterface;

/**
 * Integration test for legacy storage field types.
 *
 * This abstract base test case is supposed to be the base for field type
 * integration tests. It basically calls all involved methods in the field type
 * ``Converter`` and ``Storage`` implementations. Fo get it working implement
 * the abstract methods in a sensible way.
 *
 * The following actions are performed by this test using the custom field
 * type:
 *
 * - Create a new content type with the given field type
 * - Load create content type
 * - Create content object of new content type
 * - Load created content
 * - Copy created content
 * - Remove copied content
 *
 * @group integration
 */
class RelationListIntegrationTest extends BaseIntegrationTest
{
    /**
     * Get name of tested field type.
     *
     * @return string
     */
    public function getTypeName()
    {
        return 'ezobjectrelationlist';
    }

    /**
     * Get handler with required custom field types registered.
     *
     * @return Handler
     */
    public function getCustomHandler()
    {
        $targetContentValidator = $this->createMock(TargetContentValidatorInterface::class);

        $fieldType = new FieldType\RelationList\Type(
            $targetContentValidator
        );
        $fieldType->setTransformationProcessor($this->getTransformationProcessor());

        return $this->getHandler(
            'ezobjectrelationlist',
            $fieldType,
            new Legacy\Content\FieldValue\Converter\RelationListConverter($this->handler),
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
                'fieldSettings' => [
                    'selectionMethod' => 0,
                    'selectionDefaultLocation' => '',
                    'selectionContentTypes' => [],
                ],
                'validators' => [
                    'RelationListValueValidator' => [
                        'selectionLimit' => 3,
                    ],
                ],
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
        $fieldSettings = [
            'selectionMethod' => 0,
            'selectionDefaultLocation' => '',
            'selectionContentTypes' => [],
        ];

        $validators = [
            'RelationListValueValidator' => [
                'selectionLimit' => 3,
            ],
        ];

        return [
            ['fieldType', 'ezobjectrelationlist'],
            ['fieldTypeConstraints', new Content\FieldTypeConstraints([
                'fieldSettings' => $fieldSettings,
                'validators' => $validators,
            ])],
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
                'data' => ['destinationContentIds' => [4]],
                'externalData' => null,
                'sortKey' => null,
            ]
        );
    }

    /**
     * Asserts that the loaded field data is correct.
     *
     * Performs assertions on the loaded field, mainly checking that the
     * $field->value->externalData is loaded correctly. If the loading of
     * external data manipulates other aspects of $field, their correctness
     * also needs to be asserted. Make sure you implement this method agnostic
     * to the used SPI\Persistence implementation!
     */
    public function assertLoadedFieldDataCorrect(Field $field)
    {
        $expected = $this->getInitialValue();
        $this->assertEquals(
            $expected->externalData,
            $field->value->externalData
        );

        $this->assertNotNull(
            $field->value->data['destinationContentIds']
        );
        $this->assertEquals(
            $expected->data['destinationContentIds'],
            $field->value->data['destinationContentIds']
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
                'data' => ['destinationContentIds' => [11]],
                'externalData' => null,
                'sortKey' => null,
            ]
        );
    }

    /**
     * Asserts that the updated field data is loaded correct.
     *
     * Performs assertions on the loaded field after it has been updated,
     * mainly checking that the $field->value->externalData is loaded
     * correctly. If the loading of external data manipulates other aspects of
     * $field, their correctness also needs to be asserted. Make sure you
     * implement this method agnostic to the used SPI\Persistence
     * implementation!
     */
    public function assertUpdatedFieldDataCorrect(Field $field)
    {
        $expected = $this->getUpdatedValue();
        $this->assertEquals(
            $expected->externalData,
            $field->value->externalData
        );

        $this->assertNotNull(
            $field->value->data['destinationContentIds']
        );
        $this->assertEquals(
            $expected->data['destinationContentIds'],
            $field->value->data['destinationContentIds']
        );
    }
}
