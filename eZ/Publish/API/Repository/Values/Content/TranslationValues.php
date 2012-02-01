<?php
namespace eZ\Publish\API\Repository\Values\Content;
/**
 * This value object is used for adding a translation to a version
 *
 * @property-write FieldCollection $fields
 */
abstract class TranslationValues extends ValueObject
{

    /**
     * Adds a translated field to the field collection in the given language
     * This method is also be implemented by ArrayAccess so that
     * $fielfs[$fieldDefIdentifer] = $value is an equivalent call
     *
     * @param string $fieldDefIdentifier the identifier of the field definition
     * @param mixed $value Either a plain value which is understandable by the field type or an instance of a Value class provided by the field type
     */
    public abstract function setField( $fieldDefIdentifier, $value );
}
