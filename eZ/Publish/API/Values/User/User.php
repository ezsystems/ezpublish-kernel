<?php
namespace eZ\Publish\API\Values\User;

use eZ\Publish\API\Values\ValueObject;

use eZ\Publish\API\Values\Content\Version;

/**
 * This class represents a user value
 * 
 * @property-read int $id the user id which is equal to the underlying content id
 * @property-read string $login 
 * @property-read string $email
 * @property-read string $passwordHash 
 * @property-read string $hashAlgorithm Hash algorithm used to has the password
 * @property-read boolean $enabled User can not login if false
 * @property-read int maxLogin Max number of time user is allowed to login
 */
abstract class User extends Content
{
    /**
     * User ID
     *
     * @var mixed
     */
    protected $id;

    /**
     * User login
     *
     * @var string
     */
    protected $login;

    /**
     * User E-Mail address
     *
     * @var string
     */
    protected $email;

    /**
     * User password hash
     *
     * @var string
     */
    protected $passwordHash;

    /**
     * Hash algorithm used to has the password
     *
     * @var int
     */
    protected $hashAlgorithm;

    /**
     * Flag to signal if user is enabled or not
     *
     * User can not login if false
     *
     * @var boolean
     */
    protected $isEnabled = false;

    /**
     * Max number of time user is allowed to login
     *
     * @todo: Not used in kernel, should probably be a number of login allowed before changing password.
     *        But new users gets 0 before they activate, admin has 10, and anonymous has 1000 in clean data.
     *
     * @var int
     */
    protected $maxLogin;
}
