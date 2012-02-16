<?php
namespace eZ\Publish\Core\Repository\Values\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct as APIContentTypeCreateStruct;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct;

use eZ\Publish\API\Repository\Values\ValueObject;

use eZ\Publish\API\Repository\Values\Content\Location;

/**
 * this clss is used for creating content types
 *
 * @property $fieldDefinitions the collection of field definitions
 */
class ContentTypeCreateStruct extends APIContentTypeCreateStruct
{
    /**
     * Holds the collection of field definitions
     *
     * @var array
     */
    public $fieldDefinitions = array();

    /**
     * adds a new field definition
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct $fieldDef
     */
    public function addFieldDefinition( FieldDefinitionCreateStruct $fieldDef )
    {
        $this->fieldDefinitions[$fieldDef->identifier] = $fieldDef;
    }
}
