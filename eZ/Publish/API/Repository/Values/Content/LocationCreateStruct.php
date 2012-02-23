<?php
namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class is used to create a new Location for a content object
 *
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
     * An universally unique identifier.
     *
     * @var mixed
     */
    public $remoteId;

    /**
     * Indicates if the newly created location should be set as main location.
     * On creating a new content object with only one location this parameter is ignored.
     *
     * @var boolean
     */
    public $isMainLocation = false;

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
    public $sortOrder = Location::SORT_ORDER_ASC ;

    /**
     * The id of the parent location under which the new location should be created.
     * @var int
     */
    public $parentLocationId;
}
