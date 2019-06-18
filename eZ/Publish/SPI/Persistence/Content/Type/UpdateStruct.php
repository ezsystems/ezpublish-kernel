<?php

/**
 * File containing the ContentType class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Persistence\Content\Type;

use eZ\Publish\SPI\Persistence\ValueObject;

class UpdateStruct extends ValueObject
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
     * Modification date (timestamp).
     *
     * @var int
     */
    public $modified;

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
    public $sortField;

    /**
     * Specifies whether the sort order should be ascending or descending by default when created.
     *
     * Valid values are {@link Location::SORT_ORDER_*}
     *
     * @var mixed
     */
    public $sortOrder;

    /**
     * @todo: Document.
     *
     * @var bool
     */
    public $defaultAlwaysAvailable;
}
