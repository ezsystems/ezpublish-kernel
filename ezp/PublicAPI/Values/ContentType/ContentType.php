<?php
namespace ezp\PublicAPI\Values\ContentType;

use ezp\PublicAPI\Values\ValueObject;

use ezp\PublicAPI\Values\ContentType\FieldDefinition;

use ezp\PublicAPI\Values\ContentType\ContentTypeGroup;

/**
 *
 * this class represents a content type value
 * @property-read array $names calls getNames() or on access getName($language)
 * @property-read array $descriptions calls getDescriptions() or on access getDescription($language)
 * @property-read array $contentTypeGroups calls getContentTypeGroups
 * @property-read array $fieldDefinitions calls getFieldDefinitions() or on access getFieldDefinition($fieldDefIdentifier)
 *
 */
abstract class ContentType extends ValueObject
{
    /**
     * @var int Status constant for defined (aka "published") Type
     */
    const STATUS_DEFINED = 0;

    /**
     * @var int Status constant for draft (aka "temporary") Type
     */
    const STATUS_DRAFT = 1;

    /**
     * @var int Status constant for modified (aka "deferred for publishing") Type
     */
    const STATUS_MODIFIED = 2;

    /**
     * Content type ID
     *
     * @var mixed
     */
    public $id;

    /**
     * The status of the content type.
     * @var int One of Type::STATUS_DEFINED|Type::STATUS_DRAFT|Type::STATUS_MODIFIED
     */
    public $status;

    /**
     * This method returns the human readable name in all provided languages
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
     * this method returns the name of the content type in the given language
     * @param string $languageCode
     * @return string the name for the given language or null if none existis.
     */
    public abstract function getName( $languageCode );

    /**
     *  This method returns the human readable description of the content type
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
     * this method returns the name of the content type in the given language
     * @param string $languageCode
     * @return string the description for the given language or null if none existis.
     */
    public abstract function getDescription( $languageCode );

    /**
     * String identifier of a content type
     *
     * @var string
     */
    public $identifier;

    /**
     * Creation date (timestamp) of the content type
     *
     * @var int
     */
    public $created;

    /**
     * Modification date (timestamp) of the content type
     *
     * @var int
     */
    public $modified;

    /**
     * Creator user id of the content type
     *
     * @var mixed
     */
    public $creatorId;

    /**
     * Modifier user id of the content type
     *
     * @var mixed
     *
     */
    public $modifierId;

    /**
     * Unique remote ID of the content type
     *
     * @var string
     */
    public $remoteId;

    /**
     * URL alias schema
     * If nothing is provided, $nameSchema will be used instead.
     *
     * @var string
     */
    public $urlAliasSchema;

    /**
     * Name schema.
     * Can be composed of FieldDefinition identifier place holders.
     * These place holders must comply this pattern : <field_definition_identifier>.
     * An OR condition can be used :
     * <field_def|other_field_def>
     * In this example, field_def will be used if available. If not, other_field_def will be used for content name generation
     *
     * @var string
     */
    public $nameSchema;

    /**
     * Determines if the type is a container
     *
     * @var boolean
     */
    public $isContainer;

    /**
     * Main language
     *
     * @var mixed
     */
    public $mainLanguageCode;

    /**
     * if an instance of acontent type is created the always available flag is set
     * by default this this value.
     *
     * @var boolean
     */
    public $defaultAlwaysAvailable = true;

    /**
     * Specifies which property the child locations should be sorted on by default when created
     *
     * Valid values are found at {@link Location::SORT_FIELD_*}
     *
     * @var mixed
     */
    public $defaultSortField;

    /**
     * Specifies whether the sort order should be ascending or descending by default when created
     *
     * Valid values are {@link Location::SORT_ORDER_*}
     *
     * @var mixed
     */
    public $defaultSortOrder;

    /**
     * This method returns the content type groups this content type is assigned to
     * @return array an array of {@link ContentTypeGroup}
     */
    public abstract function getContentTypeGroups();

    /**
     * This method returns the content type field definitions from this type
     *
     * @return array an array of {@link FieldDefinition}
     */
    public abstract function getFieldDefinitions();

    /**
     *
     * this method returns the field definition for the given identifier
     * @param $fieldDefinitionIdentifier
     * @return FieldDefinition
     */
    public abstract function getFieldDefinition( $fieldDefinitionIdentifier );
}
