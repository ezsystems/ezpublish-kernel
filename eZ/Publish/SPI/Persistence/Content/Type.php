<?php
/**
 * File containing the ContentType class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\Content;

use eZ\Publish\SPI\Persistence\ValueObject;

/**
 * @todo What about sort_field and sort_order?
 */
class Type extends ValueObject
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
     * Primary key: Content type ID
     *
     * @var mixed
     */
    public $id;

    /**
     * Primary key: Status (legacy: "version")
     *
     * @var int One of Type::STATUS_DEFINED|Type::STATUS_DRAFT|Type::STATUS_MODIFIED
     */
    public $status = self::STATUS_DRAFT;

    /**
     * Human readable name of the content type
     *
     * The structure of this field is:
     * <code>
     * array( 'eng' => '<name_eng>', 'de' => '<name_de>' );
     * </code>
     *
     * @var string[]
     */
    public $name;

    /**
     * Human readable description of the content type
     *
     * The structure of this field is:
     * <code>
     * array( 'eng' => '<description_eng>', 'de' => '<description_de>' );
     * </code>
     *
     * @var string[]
     */
    public $description = array();

    /**
     * String identifier of a type
     *
     * @var string
     */
    public $identifier;

    /**
     * Creation date (timestamp)
     *
     * @var int
     */
    public $created;

    /**
     * Modification date (timestamp)
     *
     * @var int
     */
    public $modified;

    /**
     * Creator user id
     *
     * @var mixed
     */
    public $creatorId;

    /**
     * Modifier user id
     *
     * @var mixed
     *
     */
    public $modifierId;

    /**
     * Unique remote ID
     *
     * @var string
     */
    public $remoteId;

    /**
     * URL alias schema
     * Same as {@link \eZ\Publish\SPI\Persistence\Content\Type::$nameSchema}.
     * If nothing is provided, $nameSchema will be used instead.
     *
     * @var string
     * @see \eZ\Publish\SPI\Persistence\Content\Type::$nameSchema
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
     * Initial language
     *
     * @var mixed
     */
    public $initialLanguageId;

    /**
     * Specifies which property the child locations should be sorted on by default when created
     *
     * Valid values are found at {@link Location::SORT_FIELD_*}
     *
     * @var mixed
     */
    public $sortField = Location::SORT_FIELD_PUBLISHED;

    /**
     * Specifies whether the sort order should be ascending or descending by default when created
     *
     * Valid values are {@link Location::SORT_ORDER_*}
     *
     * @var mixed
     */
    public $sortOrder = Location::SORT_ORDER_DESC;

    /**
     * Contains an array of type group IDs
     *
     * @var mixed[]
     */
    public $groupIds = array();

    /**
     * Content fields in this type
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition[]
     */
    public $fieldDefinitions = array();

    /**
     * @todo: Document.
     *
     * @var boolean
     */
    public $defaultAlwaysAvailable = false;
}
