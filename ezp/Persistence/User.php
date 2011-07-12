<?php
/**
 * File containing the User class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage persistence_user
 */

namespace ezp\Persistence;

/**
 * @package ezp
 * @subpackage persistence
 */
class User extends AbstractValueObject
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
     * User password 
     *
     * @var string
     */
    public $pwd;

    /**
     * Hash algorithm used to has the password
     *
     * @var string
     */
    public $hashAlg;
}
?>
