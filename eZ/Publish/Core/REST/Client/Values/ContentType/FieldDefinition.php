<?php

namespace eZ\Publish\Core\REST\Client\Values\ContentType;

/**
 *
 * This class represents a field definition
 * @property-read $names calls getNames() or on access getName($language)
 * @property-read $descriptions calls getDescriptions() or on access getDescription($language)
 * @property-read $fieldSettings calls getFieldSettings()
 * @property-read mixed $validators calls getValidatorConfiguration()
 * @property-read int $id the id of the field definition
 * @property-read string $identifier the identifier of the field definition
 * @property-read string $fieldGroup the field group name
 * @property-read int $position the position of the field definition in the content typr
 * @property-read string $fieldType String identifier of the field type
 * @property-read boolean $isTranslatable indicatats if fields of this definition are translatable
 * @property-read boolean $isRequired indicates if this field is required in the content object
 * @property-read boolean $isSearchable indicates if the field is searchable
 * @property-read boolean $isInfoCollector indicates if this field is used for information collection
 * @property-read $defaultValue the default value of the field
 */
class FieldDefinition extends \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition
{
    /**
     * Contains the human readable name of this field in all provided languages
     * of the content type
     *
     * @var string[]
     */
    protected $names;

    /**
     * Contains the human readable description of the field
     *
     * @var string[]
     */
    protected $descriptions;

    /**
     * Contains the validators of this field definition supported by the field type#
     *
     * @var \eZ\Publish\Core\FieldType\Validator[]
     */
    protected $validators;

    /**
     * Contains settings for the field definition supported by the field type
     *
     * @var array
     */
    protected $fieldSettings;

    function __construct( array $data = array() )
    {
        foreach ( $data as $propertyName => $propertyValue )
        {
            $this->$propertyName = $propertyValue;
        }
    }

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
     * This method returns the name of the field in the given language
     *
     * @param string $languageCode
     *
     * @return string the name for the given language or null if none existis.
     */
    public function getName( $languageCode )
    {
        return $this->names[$languageCode];
    }

    /**
     * This method returns the human readable description of the field
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
     * This method returns the name of the field in the given language
     *
     * @param string $languageCode
     *
     * @return string the description for the given language or null if none existis.
     */
    public function getDescription( $languageCode )
    {
        return $this->descriptions[$languageCode];
    }

    /**
     * This method returns the validators of this field definition supported by the field type
     *
     * @return \eZ\Publish\Core\FieldType\Validator[]
     */
    public function getValidatorConfiguration()
    {
        return $this->validators;
    }

    /**
     * This method returns settings for the field definition supported by the field type
     *
     * @return array
     */
    public function getFieldSettings()
    {
        return $this->fieldSettings;
    }
}
