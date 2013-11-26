<?php
/**
 * File containing the eZ\Publish\SPI\Persistence\User\UpdateStruct class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */


namespace eZ\Publish\SPI\Persistence\User;

/**
 * this class is used to update a user
 *
 * @package eZ\Publish\SPI\Persistence\User
 */
class UpdateStruct
{
    /**
     * if set the User login is changed
     *
     * @var string
     */
    public $login;

    /**
     * if set the User E-Mail address
     *
     * @var string
     */
    public $email;

    /**
     * if set the User password hash is changed
     *
     * @var string
     */
    public $passwordHash;

    /**
     * if set the Hash algorithm used to encrypt the password  is changed
     *
     * @var int
     */
    public $hashAlgorithm;

    /**
     * if set the Flag to signal if user is enabled or not is changed
     *
     * User can not login if false
     *
     * @var boolean
     */
    public $isEnabled = false;

    /**
     * if set, the maximal number of time user is allowed to login before he must change his password, is changed
     *
     * @var int
     */
    public $maxLogin = 0;

    /**
     * not clear
     *
     * @var int
     */
    public $currentVisitTime;

    /**
     * if set the timestamp of the last login is changed
     *
     * @var int
     */
    public $lastVisitDate;

    /**
     * if set the number of failed login attempts is changed
     *
     * @var int
     */
    public $failedLoginAttempts;

    /**
     * if set the number of sessions is changed
     *
     * @var int
     */
    public $loginCount;
}
