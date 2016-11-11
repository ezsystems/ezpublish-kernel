<?php

/**
 * File containing the Location CreateStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Persistence\Content\Location;

use eZ\Publish\SPI\Persistence\ValueObject;

class CreateStruct extends ValueObject
{
    /**
     * Location priority.
     *
     * Position of the Location among its siblings when sorted using priority
     * sort order.
     *
     * @var int
     */
    public $priority = 0;

    /**
     * Indicates that the Location entity has been explicitly marked as hidden.
     *
     * @var bool
     */
    public $hidden = false;

    /**
     * Indicates that the Location is implicitly marked as hidden by a parent
     * location.
     *
     * @var bool
     */
    public $invisible = false;

    /**
     * Remote ID.
     *
     * A universally unique identifier.
     *
     * @var mixed
     */
    public $remoteId;

    /**
     * ID of the corresponding {@link Content}.
     *
     * @var mixed Content ID.
     */
    public $contentId;

    /**
     * version of the corresponding {@link Content}.
     *
     * @todo Rename to $contentVersionNo?
     *
     * @var int Content version.
     */
    public $contentVersion;

    /**
     * Legacy format of the url alias.
     *
     * This field might be removed in a later version.
     *
     * @deprecated Since 5.4, planned to be removed in 6.0
     *
     * @var string
     */
    public $pathIdentificationString;

    /**
     * Identifier of the main location.
     *
     * If the content object in this location has multiple locations,
     * $mainLocationId will point to the main one.
     * This is allowed to be set to true, this will mean this should become main location
     * (@todo Find a better way to deal with being able to create the main location)
     *
     * @var mixed|true
     */
    public $mainLocationId = true;

    /**
     * Specifies which property the child locations should be sorted on.
     *
     * Valid values are found at {@link Location::SORT_FIELD_*}
     *
     * @var mixed
     */
    public $sortField;

    /**
     * Specifies whether the sort order should be ascending or descending.
     *
     * Valid values are {@link Location::SORT_ORDER_*}
     *
     * @var mixed
     */
    public $sortOrder;

    /**
     * Parent location's Id.
     *
     * @var int
     */
    public $parentId;
}
