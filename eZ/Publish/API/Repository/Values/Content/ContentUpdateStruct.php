<?php
namespace eZ\Publish\API\Repository\Values\Content;
use eZ\Publish\API\Repository\Values\ValueObject;
/**
 * This class is used for updating the fields of a content object draft
 *
 * @property-write array $fields
 */
abstract class ContentUpdateStruct extends ValueObject
{
 
    /**
     * The language code of the version. In 4.x this code will be used as the language code of the translation
     * (which is shown in the admin interface).
     * It is also used as default language for added fields.
     *
     * @var string
     */
    public $initialLanguageCode;

    /**
     * Adds a field to the field collection.
     * This method could also be implemented by ArrayAccess so that
     * $fields[$fieldDefIdentifier][$language] = $value or without language $fields[$fieldDefIdentifier] = $value
     * is an equivalent call.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException if the field is translatable and $initialLanguageCode is not set and $languageCode is NULL
     *
     * @param string $fieldDefIdentifier the identifier of the field definition
     * @param mixed $value Either a plain value which is understandable by the field type or an instance of a Value class provided by the field type
     * @param bool|string $languageCode If not given on a translatable field the initial language is used,
     */
    abstract public function setField( $fieldDefIdentifier, $value, $languageCode = NULL );
}
