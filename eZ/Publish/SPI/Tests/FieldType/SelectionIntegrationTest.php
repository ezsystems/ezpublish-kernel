<?php

/**
 * File contains: eZ\Publish\SPI\Tests\FieldType\SelectionIntegrationTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
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

        return $this->getHandler(
            'ezselection',
            $fieldType,
            new Legacy\Content\FieldValue\Converter\SelectionConverter(),
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
            array(
                'validators' => null,
                'fieldSettings' => new FieldSettings(
                    array(
                        'isMultiple' => true,
                        'options' => array(
                            1 => 'First',
                            2 => 'Second',
                            3 => 'Sindelfingen',
                        ),
                    )
                ),
            )
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
        return array(
            array('fieldType', 'ezselection'),
            array(
                'fieldTypeConstraints',
                new Content\FieldTypeConstraints(
                    array(
                        'validators' => null,
                        'fieldSettings' => new FieldSettings(
                            array(
                                'isMultiple' => true,
                                'options' => array(
                                    1 => 'First',
                                    2 => 'Second',
                                    3 => 'Sindelfingen',
                                ),
                            )
                        ),
                    )
                ),
            ),
        );
    }

    /**
     * Get initial field value.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function getInitialValue()
    {
        return new Content\FieldValue(
            array(
                'data' => array(1, 3),
                'externalData' => null,
                'sortKey' => '1-3',
            )
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
            array(
                'data' => array(2),
                'externalData' => null,
                'sortKey' => '2',
            )
        );
    }
}
