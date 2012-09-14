<?php
/**
 * File containing the Generator base class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Output;

use eZ\Publish\API\Repository\FieldTypeService,
    eZ\Publish\API\Repository\FieldType,
    eZ\Publish\API\Repository\Values\ContentType\ContentType,
    eZ\Publish\API\Repository\Values\ContentType\FieldDefinition,
    eZ\Publish\API\Repository\Values\Content\Field;

/**
 * Serializes FieldType related data for REST output.
 */
class FieldTypeSerializer
{
    /**
     * FieldTypeService
     *
     * @var eZ\Publish\API\Repository\FieldTypeService
     */
    protected $fieldTypeService;

    /**
     * @param ieZ\Publish\API\Repository\FieldTypeService $fieldTypeService
     */
    public function __construct( FieldTypeService $fieldTypeService )
    {
        $this->fieldTypeService = $fieldTypeService;
    }

    /**
     * Serializes the field value of $field through $generator
     *
     * @param Generator $generator
     * @param ContentType $contentType
     * @param Field $field
     * @return void
     */
    public function serializeFieldValue( Generator $generator, ContentType $contentType, Field $field )
    {
        $this->serializeValue(
            'fieldValue',
            $generator,
            $this->fieldTypeService->getFieldType(
                $contentType->getFieldDefinition( $field->fieldDefIdentifier )->fieldTypeIdentifier
            ),
            $field->value
        );
    }

    /**
     * Serializes the given $value for $fieldType with $generator into
     * $elementName
     *
     * @param string $elementName
     * @param Generator $generator
     * @param FieldType $fieldType
     * @param mixed $value
     * @return void
     */
    protected function serializeValue( $elementName, Generator $generator, FieldType $fieldType, $value )
    {
        $generator->generateFieldTypeHash(
            $elementName,
            $fieldType->toHash( $value )
        );
    }

    /**
     * Serializes the $defaultValue for $fieldDefIdentifier through $generator
     *
     * @param Generator $generator
     * @param FieldDefinition $fieldDefinition
     * @param mixed $defaultValue
     * @return void
     */
    public function serializeFieldDefaultValue( Generator $generator, FieldDefinition $fieldDefinition, $defaultValue )
    {
        $this->serializeValue(
            'defaultValue',
            $generator,
            $this->fieldTypeService->getFieldType(
                $fieldDefinition->fieldTypeIdentifier
            ),
            $defaultValue
        );
    }
}
