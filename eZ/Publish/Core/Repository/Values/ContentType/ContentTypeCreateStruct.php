<?php
namespace eZ\Publish\Core\Repository\Values\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct as APIContentTypeCreateStruct,
    eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct;

/**
 * this class is used for creating content types
 *
 * @property \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct[] $fieldDefinitions the collection of field definitions
 */
class ContentTypeCreateStruct extends APIContentTypeCreateStruct
{
    /**
     * Holds the collection of field definitions
     *
     * @var \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct[]
     */
    public $fieldDefinitions = array();

    /**
     * adds a new field definition
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct $fieldDef
     */
    public function addFieldDefinition( FieldDefinitionCreateStruct $fieldDef )
    {
        $this->fieldDefinitions[] = $fieldDef;
    }
}
