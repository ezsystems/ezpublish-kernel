<?php
/**
 * File containing the User class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence;

/**
 */
class User extends ValueObject
{
    /**
     * User ID
     *
     * @var mixed
     */
    public $id;

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
     * Max number of time user is allowed to login
     *
     * @todo: Not used in kernel, should probably be a number of login allowed before changing password.
     *        But new users gets 0 before they activate, admin has 10, and anonymous has 1000 in clean data.
     *
     * @var int
     */
    public $maxLogin = 0;
}
