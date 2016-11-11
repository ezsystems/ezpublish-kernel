<?php

/**
 * File containing the Relation CreateStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Persistence\Content\Relation;

use eZ\Publish\SPI\Persistence\ValueObject;

/**
 * CreateStruct representing a relation between content.
 */
class CreateStruct extends ValueObject
{
    /**
     * Source Content ID.
     *
     * @var mixed
     */
    public $sourceContentId;

    /**
     * Source Content Version number.
     *
     * @var int
     */
    public $sourceContentVersionNo;

    /**
     * Source Content Type Field Definition Id.
     *
     * @var mixed
     */
    public $sourceFieldDefinitionId;

    /**
     * Destination Content ID.
     *
     * @var mixed
     */
    public $destinationContentId;

    /**
     * Type bitmask.
     *
     * @see \eZ\Publish\API\Repository\Values\Content\Relation::COMMON,
     *      \eZ\Publish\API\Repository\Values\Content\Relation::EMBED,
     *      \eZ\Publish\API\Repository\Values\Content\Relation::LINK,
     *      \eZ\Publish\API\Repository\Values\Content\Relation::FIELD
     *
     * @var int
     */
    public $type;
}
