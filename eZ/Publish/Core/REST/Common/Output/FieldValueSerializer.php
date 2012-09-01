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
    eZ\Publish\API\Repository\Values\ContentType\ContentType,
    eZ\Publish\API\Repository\Values\Content\Field;

/**
 * Serializes field values using field types.
 *
 * @TODO: Rename?
 */
class FieldValueSerializer
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
        $generator->generateFieldTypeHash(
            'fieldValue',
            $this->fieldTypeService->getFieldType(
                $contentType->getFieldDefinition( $field->fieldDefIdentifier )->fieldTypeIdentifier
            )->toHash( $field->value )
        );
    }
}
