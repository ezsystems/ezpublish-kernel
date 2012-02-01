<?php
namespace eZ\Publish\API\Values\Content;
use eZ\Publish\API\Values\ValueObject;
/**
 *
 * This class is used for updating location meta data.
 *
 */
class LocationUpdateStruct extends ValueObject
{
    /**
     * If set the location priority is changed to the new value
     *
     * @var int
     */
    public $priority;

    /**
     * if set the location gets a new remoteId.
     *
     * @var mixed
     */
    public $remoteId;

    /**
     * if set to true this location will set as new main location for the content.
     * If set to false this parameter is ignored.
     *
     * @var mixed|true
     */
    public $isMainLocation;

    /**
     * If set the sortField is changed.
     * The sort field specifies which property the child locations should be sorted on.
     * Valid values are found at {@link Location::SORT_FIELD_*}
     *
     * @var mixed
     */
    public $sortField;

    /**
     * if set the sortOrder is changed.
     * The sort order specifies whether the sort order should be ascending or descending.
     * Valid values are {@link Location::SORT_ORDER_*}
     *
     * @var mixed
     */
    public $sortOrder;
}
