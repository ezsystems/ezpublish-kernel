<?php
namespace ezp\PublicAPI\Values\Content;
use ezp\PublicAPI\Values\ValueObject;
/**
 * This class is used for updating the fields of a version
 * @property-write FieldCollection $fields
 */
abstract class VersionUpdate extends ValueObject
{

    /**
     * @var integer modifier of the new version. If not set the current authenticated user is used.
     */
    public $userId;


    /**
     * The language code of the version. In 4.x this code will be used as the language code of the translation
     * (which is shown in the admin interface).
     * It is also used as default language for added fields.
     * @var string
     */
    public $initialLanguageCode;
     
    /**
     * Adds a field to the field collection.
     * This method could also be implemented by ArrayAccess so that
     * $fielfs[$fieldDefIdentifer][$language] = $value or without language $fielfs[$fieldDefIdentifer] = $value
     * is an aquivalent call.
     * @param string $fieldDefIdentifier the identifier of the field definition
     * @param mixed $value Either a plain value which is understandable by the field type or an instance of a Value class provided by the field type
     * @param string $language If not ghiven on a translatable field the initial language is used,
     */
    public abstract function setField($fieldDefIdentifier, $value, $language = false);


}
?>
