<?php
namespace ezp\PublicAPI\Values\User;

use ezp\PublicAPI\Values\Content\VersionUpdateStruct;
use ezp\PublicAPI\Values\Content\ContentUpdateStruct;
use ezp\PublicAPI\Values\ValueObject;

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
