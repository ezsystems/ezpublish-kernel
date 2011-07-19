<?php
/**
 * File containing the LocationCreateStruct class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Content;
use ezp\Persistence\AbstractValueObject;

class LocationCreateStruct extends AbstractValueObject
{
    /**
     * Location priority
     *
     * Position of the Location among its siblings when sorted using priority
     * sort order.
     *
     * @var int
     */
    public $position;

    /**
     * Indicates that the Location entity has been explicitly marked as hidden.
     *
     * @var bool
     */
    public $hidden;

    /**
     * Indicates that the Location is implicitly marked as hidden by a parent
     * location.
     *
     * @var bool
     */
    public $invisible;

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
     * Legacy format of the url alias.
     *
     * This field might be removed in a later version.
     *
     * @var string
     */
    public $pathIdentificationString;

    /**
     * The materialized path of the location entry.
     *
     * @var string
     */
    public $pathString;

    /**
     * Timestamp of the latest update of a content object in a sub location.
     *
     * @var int
     */
    public $modifiedSubLocation;

    /**
     * Identifier of the main location.
     *
     * If the content object in this location has multiple locations,
     * $mainLocationId will point to the main one.
     *
     * @var mixed
     */
    public $mainLocationId;

    /**
     * Depth location has in the location tree.
     *
     * @var int
     */
    public $depth;

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
}