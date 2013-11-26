<?php
/**
 * File containing the eZ\Publish\SPI\Persistence\User\CreateStruct class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */


namespace eZ\Publish\SPI\Persistence\User;

/**
 * this class is used to create a user
 * @package eZ\Publish\SPI\Persistence\User
 */
class CreateStruct
{
    /**
     * the associated content id
     *
     * @var mixed
     */
    public $contentId;

    /**
     * User login
     *
     * @var string
     */
    public $login;

    /**
     * User E-Mail address
     *
     * @var string
     */
    public $email;

    /**
     * User password hash
     *
     * @var string
     */
    public $passwordHash;

    /**
     * Hash algorithm used to has the password
     *
     * @var int
     */
    public $hashAlgorithm;

    /**
     * Flag to signal if user is enabled or not
     *
     * User can not login if false
     *
     * @var boolean
     */
    public $isEnabled = false;

    /**
     * Max number of time user is allowed to login before he must change his password
     * (e.g. new user get 0 to force them to change password before first login)
     *
     * @var int
     */
    public $maxLogin = 0;

}
