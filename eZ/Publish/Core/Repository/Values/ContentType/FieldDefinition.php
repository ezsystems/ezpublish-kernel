<?php
namespace eZ\Publish\Core\Repository\Values\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition as APIFieldDefinition;

/**
 *
 * This class represents a field definition
 * @property-read $names calls getNames() or on access getName($language)
 * @property-read $descriptions calls getDescriptions() or on access getDescription($language)
 * @property-read $fieldSettings calls getFieldSettings()
 * @property-read $validators calls getValidators()
 * @property-read int $id the id of the field definition
 * @property-read string $identifier the identifier of the field definition
 * @property-read string $fieldGroup the field group name
 * @property-read int $position the position of the field definition in the content type
 * @property-read string $fieldType String identifier of the field type
 * @property-read boolean $isTranslatable indicates if fields of this definition are translatable
 * @property-read boolean $isRequired indicates if this field is required in the content object
 * @property-read boolean $isSearchable indicates if the field is searchable
 * @property-read boolean $isInfoCollector indicates if this field is used for information collection
 * @property-read $defaultValue the default value of the field
 */
class FieldDefinition extends APIFieldDefinition
{
    /**
     * Holds the collection of names with languageCode keys
     *
     * @var array
     */
    protected $names = array();

    /**
     * Holds the collection of descriptions with languageCode keys
     *
     * @var array
     */
    protected $descriptions = array();

    /**
     * Holds collection of field settings for the contenttype
     *
     * @var array
     */
    protected $fieldSettings = array();

    /**
     * Holds collection of validators for the contenttype
     *
     * @var array
     */
    protected $validators = array();

    /**
     * This method returns the human readable name of this field in all provided languages
     * of the content type
     *
     * The structure of the return value is:
     * <code>
     * array( 'eng' => '<name_eng>', 'de' => '<name_de>' );
     * </code>
     *
     * @return string[]
     */
    public function getNames()
    {
        return $this->names;
    }

    /**
     *
     * this method returns the name of the field in the given language
     * @param string $languageCode
     * @return string the name for the given language or null if none exists.
     */
    public function getName( $languageCode )
    {
        if ( array_key_exists( $languageCode, $this->names ))
        {
            return $this->names[$languageCode];
        }

        return null;
    }

    /**
     *  This method returns the human readable description of the field
     *
     * The structure of this field is:
     * <code>
     * array( 'eng' => '<description_eng>', 'de' => '<description_de>' );
     * </code>
     *
     * @return string[]
     */
    public function getDescriptions()
    {
        return $this->descriptions;
    }

    /**
     * this method returns the name of the field in the given language
     * @param string $languageCode
     * @return string the description for the given language or null if none exists.
     */
    public function getDescription( $languageCode )
    {
        if ( array_key_exists( $languageCode, $this->descriptions ))
        {
            return $this->descriptions[$languageCode];
        }

        return null;
    }

    /**
     * this method returns the validators of this field definition supported by the field type
     * @return array an array of {@link Validator}
     */
    public function getValidators()
    {
        return $this->validators;
    }

    /**
     * this method returns settings for the field definition supported by the field type
     * @return array
     */
    public function getFieldSettings()
    {
        return $this->fieldSettings;
    }
}
