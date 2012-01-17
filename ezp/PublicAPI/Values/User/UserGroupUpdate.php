<?php
namespace ezp\PublicAPI\Values\User;

use ezp\PublicAPI\Values\Content\VersionUpdate;

use ezp\PublicAPI\Values\Content\ContentUpdate;

use ezp\PublicAPI\Values\ValueObject;

/**
 * This class is used to update a user in the repository
 *
 */
class UserGroupUpdate extends ValueObject {

    /**
     * the update structure  for the profile version
     * @var VersionUpdate
     */
    public $versionUpdate = null;

    /**
     * the update structure  for the profile meta data
     * @var ContentUpdate
     */
    public $contentUpdate = null;


}

