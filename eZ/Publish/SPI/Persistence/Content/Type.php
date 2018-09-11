<?php

/**
 * File containing the ContentType class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Persistence\Content;

use eZ\Publish\SPI\Persistence\ValueObject;

/**
 * SPI Persistence Content\Type value object.
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
     * Primary key: Content type ID.
     *
     * @var mixed
     */
    public $id;

    /**
     * Primary key: Status (legacy: "version").
     *
     * @var int One of Type::STATUS_DEFINED|Type::STATUS_DRAFT|Type::STATUS_MODIFIED
     */
    public $status = self::STATUS_DRAFT;

    /**
     * Human readable name of the content type.
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
     * Human readable description of the content type.
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
     * String identifier of a type.
     *
     * @var string
     */
    public $identifier;

    /**
     * Creation date (timestamp).
     *
     * @var int
     */
    public $created;

    /**
     * Modification date (timestamp).
     *
     * @var int
     */
    public $modified;

    /**
     * Creator user id.
     *
     * @var mixed
     */
    public $creatorId;

    /**
     * Modifier user id.
     *
     * @var mixed
     */
    public $modifierId;

    /**
     * Unique remote ID.
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
     *
     * @see \eZ\Publish\SPI\Persistence\Content\Type::$nameSchema
     */
    public $urlAliasSchema;

    /**
     * Name schema.
     * Can be composed of FieldDefinition identifier place holders.
     * These place holders must comply this pattern : <field_definition_identifier>.
     * An OR condition can be used :
     * <field_def|other_field_def>
     * In this example, field_def will be used if available. If not, other_field_def will be used for content name generation.
     *
     * @var string
     */
    public $nameSchema;

    /**
     * Determines if the type is a container.
     *
     * @var bool
     */
    public $isContainer;

    /**
     * Initial language.
     *
     * @var mixed
     */
    public $initialLanguageId;

    /**
     * Specifies which property the child locations should be sorted on by default when created.
     *
     * Valid values are found at {@link Location::SORT_FIELD_*}
     *
     * @var mixed
     */
    public $sortField = Location::SORT_FIELD_PUBLISHED;

    /**
     * Specifies whether the sort order should be ascending or descending by default when created.
     *
     * Valid values are {@link Location::SORT_ORDER_*}
     *
     * @var mixed
     */
    public $sortOrder = Location::SORT_ORDER_DESC;

    /**
     * Contains an array of type group IDs.
     *
     * @var mixed[]
     */
    public $groupIds = array();

    /**
     * Definitions for Content fields in this type.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition[]
     */
    public $fieldDefinitions = array();

    /**
     * Defines if content objects should have always available enabled or not by default.
     *
     * Always available (when enabled) means main language is always available, and works as a editorial fallback
     * language on load operations when translation filter is provided but no match is found.
     *
     * @var bool
     */
    public $defaultAlwaysAvailable = false;
}
