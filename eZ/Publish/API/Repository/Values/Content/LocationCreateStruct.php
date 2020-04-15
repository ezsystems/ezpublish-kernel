<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class is used to create a new Location for a content object.
 */
class LocationCreateStruct extends ValueObject
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
     * An universally unique string identifier.
     *
     * Needs to be a unique Location->remoteId string value.
     *
     * @var mixed
     */
    public $remoteId;

    /**
     * Specifies which property the child locations should be sorted on.
     *
     * Valid values are found at {@link Location::SORT_FIELD_*}
     *
     * If not set, will be taken out of ContentType's default sort field
     *
     * @var mixed
     */
    public $sortField = null;

    /**
     * Specifies whether the sort order should be ascending or descending.
     *
     * Valid values are {@link Location::SORT_ORDER_*}
     *
     * If not set, will be taken out of ContentType's default sort order
     *
     * @var mixed
     */
    public $sortOrder = null;

    /**
     * The id of the parent location under which the new location should be created.
     *
     * Required.
     *
     * @var mixed
     */
    public $parentLocationId;
}
