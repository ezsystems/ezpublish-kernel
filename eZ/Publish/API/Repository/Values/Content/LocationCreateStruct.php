<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\LocationCreateStruct class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class is used to create a new Location for a content object
 */
class LocationCreateStruct extends ValueObject
{
    /**
     * Location priority
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
     * @var boolean
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
     * @var mixed
     */
    public $sortField = Location::SORT_FIELD_NAME;

    /**
     * Specifies whether the sort order should be ascending or descending.
     *
     * Valid values are {@link Location::SORT_ORDER_*}
     *
     * @var mixed
     */
    public $sortOrder = Location::SORT_ORDER_ASC;

    /**
     * The id of the parent location under which the new location should be created.
     *
     * @required
     *
     * @var mixed
     */
    public $parentLocationId;
}
