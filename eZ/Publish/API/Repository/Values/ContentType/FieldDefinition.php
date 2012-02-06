<?php
namespace eZ\Publish\API\Repository\Values\ContentType;
use eZ\Publish\API\Repository\Values\ValueObject;
/**
 *
 * This class represents a field definiton
 * @property-read $names calls getNames() or on access getName($language)
 * @property-read $descriptions calls getDescriptions() or on access getDescription($language)
 * @property-read $fieldSettings calls getFieldSettings()
 * @property-read $validators calls getValidators()
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
abstract class FieldDefinition extends ValueObject
{
    /**
     * the unique id of this field definition
     *
     * @var mixed
     */
    protected $id;

    /**
     * Readable string identifier of a field definition
     *
     * @var string
     */
    protected $identifier;

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
    protected $fieldGroup;

    /**
     * the position of the field definition in the content typr
     *
     * @var int
     */
    protected $position;

    /**
     * String identifier of the field type
     *
     * @var string
     */
    protected $fieldType;

    /**
     * If the field is translatable
     *
     * @var boolean
     */
    protected $isTranslatable;

    /**
     * Is the field required
     *
     * @var boolean
     */
    protected $isRequired;

    /**
     * the flag if this field is used for information collection
     *
     * @var boolean
     */
    protected $isInfoCollector;

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
    protected $defaultValue;

    /**
     * Indicates if th the content is searchable by this attribute
     *
     * @var boolean
     */
    protected $isSearchable;
}
