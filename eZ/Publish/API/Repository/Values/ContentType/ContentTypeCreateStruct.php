<?php
namespace eZ\Publish\API\Repository\Values\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreate;

use eZ\Publish\API\Repository\Values\ValueObject;

use eZ\Publish\API\Repository\Values\Content\Location;

/**
 * this clss is used for creating content types
 *
 * @property $fieldDefinitions the collection of field definitions
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
     * Main language Code.
     *
     * @var string
     */
    public $mainLanguageCode;

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
     * @var boolean
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
     * @var boolean
     */
    public $defaultAlwaysAvailable = true;

    /**
     * If set this array of names with languageCode keys replace the complete name collection
     * 
     * @var array an array of string
     */
    public $names;

    /**
     * If set this array of descriptions with languageCode keys replace the complete description collection
     * 
     * @var array an array of string
     */
    public $descriptions;
    

    /**
     * adds a new field definition
     *
     * @param FieldDefinitionCreate $fieldDef
     */
    abstract public function addFieldDefinition( FieldDefinitionCreate $fieldDef );

    /**
     * if set this value overrides the current user as creator
     * @var int
     */
    public $creatorId = null;

    /**
     * If set this value overrides the current time for creation
     * @var DateTime
     */
    public $creationDate = null;
}
