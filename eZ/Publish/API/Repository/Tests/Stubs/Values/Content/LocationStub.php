<?php
/**
 * File containing the LocationStub class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Stubs\Values\Content;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;

/**
 * Stubbed implementation of the {@link \eZ\Publish\API\Repository\Values\Content\Location}
 * class.
 *
 * @see \eZ\Publish\API\Repository\Values\Content\Location
 */
class LocationStub extends Location
{
    /**
     * Location ID.
     *
     * @var mixed Location ID.
     */
    // protected $id;

    /**
     * Location priority
     *
     * Position of the Location among its siblings when sorted using priority
     * sort order.
     *
     * @var int
     */
    // protected $priority;

    /**
     * Indicates that the Location entity has been explicitly marked as hidden.
     *
     * @var boolean
     */
    // protected $hidden;

    /**
     * Indicates that the Location is implicitly marked as hidden by a parent
     * location.
     *
     * @var boolean
     */
    // protected $invisible;

    /**
     * Remote ID.
     *
     * A universally unique identifier.
     *
     * @var mixed
     */
    // protected $remoteId;

    /**
     * Parent ID.
     *
     * @var mixed Location ID.
     */
    // protected $parentLocationId;

    /**
     * The materialized path of the location entry, eg: /1/2/
     *
     * @var string
     */
    // protected $pathString;

    /**
     * Identifier of the main location.
     *
     * If the content object in this location has multiple locations,
     * $mainLocationId will point to the main one.
     *
     * @var mixed
     */
    // protected $mainLocationId;

    /**
     * Depth location has in the location tree.
     *
     * @var int
     */
    // protected $depth;

    /**
     * Specifies which property the child locations should be sorted on.
     *
     * Valid values are found at {@link Location::SORT_FIELD_*}
     *
     * @var mixed
     */
    // protected $sortField;

    /**
     * Specifies whether the sort order should be ascending or descending.
     *
     * Valid values are {@link Location::SORT_ORDER_*}
     *
     * @var mixed
     */
    // protected $sortOrder;

    /**
     * ContentInfo
     *
     * @var \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    protected $contentInfo;

    /**
     * returns the content info of the content object of this location
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    public function getContentInfo()
    {
        return $this->contentInfo;
    }

    /**
     * FOR TEST USE ONLY!
     *
     * @param int $parentLocationId
     * @return void
     */
    public function __setParentLocationId( $parentLocationId )
    {
        $this->parentLocationId = $parentLocationId;
    }

    /**
     * FOR TEST USE ONLY!
     *
     * @return void
     */
    public function __hide()
    {
        $this->hidden = true;
    }

    /**
     * FOR TEST USE ONLY!
     *
     * @return void
     */
    public function __unhide()
    {
        $this->hidden = false;
    }

    /**
     * FOR TEST USE ONLY!
     *
     * @return void
     */
    public function __makeVisible()
    {
        $this->invisible = false;
    }

    /**
     * FOR TEST USE ONLY!
     *
     * @return void
     */
    public function __makeInvisible()
    {
        $this->invisible = true;
    }

    /**
     * FOR TEST USE ONLY!
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $newContentInfo
     * @return void
     */
    public function __setContentInfo( ContentInfo $newContentInfo )
    {
        $this->contentInfo = $newContentInfo;
    }

    /**
     * FOR TEST USE ONLY!
     *
     * @param int $depth
     * @return void
     */
    public function __setDepth( $depth )
    {
        $this->depth = $depth;
    }

    /**
     * FOR TEST USE ONLY!
     *
     * @param string $pathString
     * @return void
     */
    public function __setPathString( $pathString )
    {
        $this->pathString = $pathString;
    }
}
