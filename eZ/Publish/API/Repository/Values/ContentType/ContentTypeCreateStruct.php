<?php
namespace eZ\Publish\API\Repository\Values\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\Content\Location;

/**
 * this class is used for creating content types
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
     * The remote id
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
     * if an instance of a content type is created the always available flag is set
     * by default this this value.
     *
     * @var boolean
     */
    public $defaultAlwaysAvailable = true;

    /**
     * AN array of names with languageCode keys
     *
     * @var string[]
     */
    public $names;

    /**
     * An array of descriptions with languageCode keys
     *
     * @var string[]
     */
    public $descriptions;

    /**
     * returns the list of field definitions
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct[]
     */
    abstract public function getFieldDefinitions();

    /**
     * adds a new field definition
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct $fieldDef
     */
    abstract public function addFieldDefinition( FieldDefinitionCreateStruct $fieldDef );

    /**
     * if set this value overrides the current user as creator
     *
     * @var int
     */
    public $creatorId = null;

    /**
     * If set this value overrides the current time for creation
     *
     * @var \DateTime
     */
    public $creationDate = null;
}
