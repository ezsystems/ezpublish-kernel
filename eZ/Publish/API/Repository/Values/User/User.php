<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\User\User class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\API\Repository\Values\User;

use eZ\Publish\API\Repository\Values\Content\Content;

/**
 * This class represents a user value.
 *
 * @property-read string $login
 * @property-read string $email
 * @property-read string $passwordHash
 * @property-read string $hashAlgorithm Hash algorithm used to hash the password
 * @property-read boolean $enabled User can not login if false
 * @property-read int $maxLogin Max number of time user is allowed to login
 */
abstract class User extends Content implements UserReference
{
    /**
     * User login.
     *
     * @var string
     */
    protected $login;

    /**
     * User E-Mail address.
     *
     * @var string
     */
    protected $email;

    /**
     * User password hash.
     *
     * @var string
     */
    protected $passwordHash;

    /**
     * Hash algorithm used to hash the password.
     *
     * @var int
     */
    protected $hashAlgorithm;

    /**
     * Flag to signal if user is enabled or not.
     *
     * User can not login if false
     *
     * @var bool
     */
    protected $enabled = false;

    /**
     * Max number of time user is allowed to login.
     *
     * @todo: Not used in kernel, should probably be a number of login allowed before changing password.
     *        But new users gets 0 before they activate, admin has 10, and anonymous has 1000 in clean data.
     *
     * @var int
     */
    protected $maxLogin;

    /**
     * The User id of the User.
     *
     * @return mixed
     */
    public function getUserId()
    {
        return $this->id;
    }
}
