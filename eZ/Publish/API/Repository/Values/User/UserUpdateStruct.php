<?php
namespace eZ\Publish\API\Repository\Values\User;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class is used to update a user in the repository
 *
 */
class UserUpdateStruct extends ValueObject
{
    /**
     * If set the email address is updated with this value
     *
     * @var string
     */
    public $email = null;

    /**
     * If set the password is updated with this plain password
     *
     * @var string
     */
    public $password = null;

    /**
     * Flag to signal if user is enabled or not
     * If set the enabled status is changed to this value
     *
     * @var boolean
     */
    public $isEnabled = null;

    /**
     * Max number of time user is allowed to login
     * If set the maximal number of logins is changed to this value
     *
     * @var int
     */
    public $maxLogin = null;


    /**
     * The update structure for the profile content
     *
     * @var \eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct
     */
    public $contentUpdateStruct = null;

    /**
     * The update structure  for the profile meta data
     *
     * @var \eZ\Publish\API\Repository\Values\Content\ContentMetaDataUpdateStruct
     */
    public $contentMetaDataUpdateStruct = null;
    
}
