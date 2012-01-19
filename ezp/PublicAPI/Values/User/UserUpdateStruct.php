<?php
namespace ezp\PublicAPI\Values\User;

use ezp\PublicAPI\Values\Content\VersionUpdateStruct;
use ezp\PublicAPI\Values\Content\ContentUpdateStruct;
use ezp\PublicAPI\Values\ValueObject;

/**
 * This class is used to update a user in the repository
 *
 */
class UserUpdateStruct extends ValueObject {

    /**
     * if set the email address is updated with this value
     *
     * @var string
     */
    public $email = null;

    /**
     * if set the password is updated with this plain password
     *
     * @var string
     */
    public $password = null;

    /**
     * Flag to signal if user is enabled or not
     * If set the enabled status is changed to this value
     *
     * @var bool
     */
    public $isEnabled = null;

    /**
     * Max number of time user is allowed to login
     * If set the maximal number of logins is changed to this value
     * @var int
     */
    public $maxLogin = null;


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
