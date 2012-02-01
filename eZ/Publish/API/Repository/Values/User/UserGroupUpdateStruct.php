<?php
namespace eZ\Publish\API\Repository\Values\User;

use eZ\Publish\API\Repository\Values\Content\VersionUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct;
use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class is used to update a user in the repository
 *
 */
class UserGroupUpdateStruct extends ValueObject
{
    /**
     * the update structure  for the profile version
     * @var VersionUpdateStruct
     */
    public $versionUpdateStruct = null;

    /**
     * the update structure  for the profile meta data
     * @var ContentUpdateStruct
     */
    public $contentUpdateStruct = null;
}
