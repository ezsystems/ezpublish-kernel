<?php
namespace eZ\Publish\Core\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\Content\Location as APILocation;

/**
 * This class represents a location in the repository
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
