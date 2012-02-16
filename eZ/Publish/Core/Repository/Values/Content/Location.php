<?php
namespace eZ\Publish\Core\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\Content\Location as APILocation;

/**
 * This class represents a location in the repository
 *
 * @property-read \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo calls getContentInfo()
 * @property-read int $contentId calls getContent()->contentId
 * @property-read int $id the id of the location
 * @property-read int $priority Position of the Location among its siblings when sorted using priority
 * @property-read boolean $hidden Indicates that the Location is implicitly marked as hidden by a parent location.
 * @property-read boolean $invisible  Indicates that the Location is implicitly marked as hidden by a parent location
 * @property-read string $remoteId a global unique id of the content object
 * @property-read $int $parentId the id of the parent location
 * @property-read string $pathString the path to this location e.g. /1/2/4/23
 * @property-read \DateTime $modifiedSubLocationDate Date of the latest update of a content object in a sub location.
 * @property-read int $mainLocationId the id of the main location of the content of this location (if equals to $id it indicates that this location is the main location)
 * @property-read int $sortField Specifies which property the child locations should be sorted on. Valid values are found at {@link Location::SORT_FIELD_*}
 * @property-read int $sortOrder Specifies whether the sort order should be ascending or descending. Valid values are {@link Location::SORT_ORDER_*}
 * @property-read int $childrenCount the number of children visible to the authenticated user which has loaded this instance.
 */
class Location extends APILocation
{
    /**
     * content info of the content object of this location
     *
     * @var \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    protected $contentInfo;

    /**
     * content ID of the content object of this location
     *
     * @var int
     */
    protected $contentId;

    /**
     * returns the content info of the content object of this location
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    public function getContentInfo()
    {
        return $this->contentInfo;
    }

}
