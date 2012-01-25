<?php
namespace ezp\PublicAPI\Values\ContentType;

use ezp\PublicAPI\Values\ContentType\FieldDefinitionCreate;

use ezp\PublicAPI\Values\ValueObject;

use ezp\PublicAPI\Values\Content\Location;

/**
 * this clss is used for creating content types
 *
 * @property-write $names $names[$language] calls setName($language)
 * @property-write $name calls setName() for setting a namein the initial language
 * @property-write $descriptions $descriptions[$language] calls setDescription($language)
 * @property-write $description calls setDescription() for setting a description in an initial language
 *
 */
abstract class ContentTypeCreateStruct extends ValueObject
{
    /**
     * String identifier of a type
     *
     * @var string
     */
    public $identifier;

    /**
     * Initial language Code.
     *
     * @var mixed
     */
    public $initialLanguageCode;

    /**
     * The renote id
     *
     * @var string
     */
    public $remoteId;

    /**
     * URL alias schema
     *
     * @var string
     */
    public $urlAliasSchema;

    /**
     * Name schema
     *
     * @var string
     */
    public $nameSchema;

    /**
     * Determines if the type is a container
     *
     * @var bool
     */
    public $isContainer = false;

    /**
     * Specifies which property the child locations should be sorted on by default when created
     *
     * Valid values are found at {@link Location::SORT_FIELD_*}
     *
     * @var mixed
     */
    public $defaultSortField = Location::SORT_FIELD_PUBLISHED;

    /**
     * Specifies whether the sort order should be ascending or descending by default when created
     *
     * Valid values are {@link Location::SORT_ORDER_*}
     *
     * @var mixed
     */
    public $defaultSortOrder = Location::SORT_ORDER_DESC;

    /**
     * if an instance of acontent type is created the always available flag is set
     * by default this this value.
     *
     * @var bool
     */
    public $defaultAlwaysAvailable = true;

    /**
     * set a content type name for the given language
     *
     * @param string $name
     * @param string $language if not given the initialLanguage is used as default
     */
    public abstract function setName( $name, $language = null );

    /**
     * set a content type description for the given language
     *
     * @param string $description
     * @param string $language if not given the initialLanguage is used as default
     */
    public abstract function setDescription( $description, $language = null );

    /**
     * adds a new field definition
     *
     * @param FieldDefinitionCreate $fieldDef
     */
    public abstract function addFieldDefinition( /*FieldDefinitionCreate*/ $fieldDef );

    /**
     * if set this value overrides the current user as creator
     * @var int
     */
    public $creatorId = null;

    /**
     * If set this value overrides the current time for creation
     * @var int (unix timestamp)
     */
    public $created = null;
}
