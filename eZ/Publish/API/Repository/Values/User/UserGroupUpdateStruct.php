<?php
namespace eZ\Publish\API\Repository\Values\User;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class is used to update a user group in the repository
 *
 */
class UserGroupUpdateStruct extends ValueObject
{
    /**
     * The update structure for the profile content
     *
     * @var \eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct
     */
    public $contentUpdateStruct = null;

    /**
     * The update structure for the profile meta data
     *
     * @var \eZ\Publish\API\Repository\Values\Content\ContentMetaDataUpdateStruct
     */
    public $contentMetaDataUpdateStruct = null;
}
