<?php
namespace eZ\Publish\API\Repository\Values\User;

use eZ\Publish\API\Repository\Values\Content\ContentCreateStruct;

/**
 * This class is used to create a new user in the repository
 */
abstract class UserCreateStruct extends ContentCreateStruct
{
    /**
     * User login
     *
     * @required
     *
     * @var string
     */
    public $login;

    /**
     * User E-Mail address
     *
     * @required
     *
     * @var string
     */
    public $email;

    /**
     * the plain password
     *
     * @required
     *
     * @var string
     */
    public $password;

    /**
     *
     * indicates if the user is enabled after creation
     * @var boolean
     */
    public $enabled = true;
}
