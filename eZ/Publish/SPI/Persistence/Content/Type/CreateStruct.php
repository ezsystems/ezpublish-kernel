<?php

/**
 * File containing the Content Type CreateStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Persistence\Content\Type;

use eZ\Publish\SPI\Persistence\ValueObject;
use eZ\Publish\SPI\Persistence\Content\Location;

class CreateStruct extends ValueObject
{
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
     * Version (state) to create.
     *
     * @var int
     */
    public $status;

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
    public $description = [];

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
     * URL alias schema.
     *
     * @var string
     */
    public $urlAliasSchema;

    /**
     * Name schema.
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
    public $groupIds = [];

    /**
     * Content fields in this type.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition[]
     */
    public $fieldDefinitions = [];

    /**
     * @todo: Document.
     *
     * @var bool
     */
    public $defaultAlwaysAvailable = false;

    /**
     * Performs a deep cloning.
     */
    public function __clone()
    {
        foreach ($this->fieldDefinitions as $id => $fieldDef) {
            $this->fieldDefinitions[$id] = clone $fieldDef;
        }
    }
}
