<?php
namespace eZ\Publish\Core\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct as APIContentUpdateStruct;

/**
 * This class is used for updating the fields of a content object draft
 *
 * @property-write array $fields
 */
class ContentUpdateStruct extends APIContentUpdateStruct
{
    /**
     * Adds a field to the field collection.
     * This method could also be implemented by ArrayAccess so that
     * $fields[$fieldDefIdentifier][$language] = $value or without language $fields[$fieldDefIdentifier] = $value
     * is an equivalent call.
     *
     * @param string $fieldDefIdentifier the identifier of the field definition
     * @param mixed $value Either a plain value which is understandable by the field type or an instance of a Value class provided by the field type
     * @param bool|string $language If not given on a translatable field the initial language is used,
     */
    public function setField( $fieldDefIdentifier, $value, $language = false )
    {
        // todo: implement
    }
}
