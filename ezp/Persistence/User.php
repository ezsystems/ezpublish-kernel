<?php
/**
 * File containing the User class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence;

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
     * User password
     *
     * @var string
     */
    public $password;

    /**
     * Hash algorithm used to has the password
     *
     * @var string
     */
    public $hashAlgorithm;
}
?>
