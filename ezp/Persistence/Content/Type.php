<?php
/**
 * File containing the ContentType class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Content;
use ezp\Persistence\ValueObject;

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
    public $isContainer;

    /**
     * Initial language
     *
     * @var mixed
     */
    public $initialLanguageId;

    /**
     * Contains an array of type group IDs
     *
     * @var mixed[]
     */
    public $groupIds = array();

    /**
     * Content fields in this type
     *
     * @var \ezp\Persistence\Content\Type\FieldDefinition[]
     */
    public $fieldDefinitions = array();
}
?>
