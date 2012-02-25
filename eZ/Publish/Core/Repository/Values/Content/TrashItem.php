<?php
namespace eZ\Publish\Core\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\Content\TrashItem as APITrashItem;

/**
 * this class represents a trash item, which is actually a trashed location
 */
class TrashItem extends APITrashItem
{
    /**
     * content info of the content object of this trash item
     *
     * @var \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    protected $contentInfo;

    /**
     * content ID of the content object of this trash item
     *
     * @var int
     */
    protected $contentId;

    /**
     * returns the content info of the content object of this trash item
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    public function getContentInfo()
    {
        return $this->contentInfo;
    }
}
