<?php
namespace ezp\PublicAPI\Values\ContentType;
use ezp\PublicAPI\Value\ValueObject;
/**
 *
 * This class represents a field definiton
 * @property-read $names calls getNames() or on access getName($language)
 * @property-read $descriptions calls getDescriptions() or on access getDescription($language)
 * @property-read $fieldSettings calls getFieldSettings()
 * @property-read $validators calls getValidators()
 */
class FieldDefinition extends ValueObject
{
    /**
     * the unique id of this field definition
     *
     * @var mixed
     */
    public $id;

    /**
     * Readable string identifier of a field definition
     *
     * @var string
     */
    public $identifier;

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
    public abstract function getNames();

    /**
     *
     * this method returns the name of the field in the given language
     * @param string $languageCode
     * @return string the name for the given language or null if none existis.
     */
    public abstract function getName( $languageCode );

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
    public abstract function getDescriptions();

    /**
     * this method returns the name of the field in the given language
     * @param string $languageCode
     * @return string the description for the given language or null if none existis.
     */
    public abstract function getDescription( $languageCode );

    /**
     * Field group name
     *
     * @var string
     */
    public $fieldGroup;

    /**
     * the position of the field definition in the content typr
     *
     * @var int
     */
    public $position;

    /**
     * String identifier of the field type
     *
     * @var string
     */
    public $fieldType;

    /**
     * If the field type is translatable
     *
     * @var boolean
     */
    public $isTranslatable;

    /**
     * Is the field required
     *
     * @var boolean
     */
    public $isRequired;

    /**
     * the flag if this attribute is used for information collection
     *
     * @var boolean
     */
    public $isInfoCollector;

    /**
     * this method returns the validators of this field definition supported by the field type
     * @return array an array of {@link Validator}
     */
    public abstract function getValidators();

    /**
     * this method returns settings for the field definition supported by the field type
     * @return array
     */
    public abstract function getFieldSettings();

    /**
     * Default value of the field
     *
     * @var mixed
     */
    public $defaultValue;

    /**
     * Indicates if th the content is searchable by this attribute
     *
     * @var bool
     */
    public $isSearchable;
}
