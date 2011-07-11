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
class User
{
    /**
     */
    public $id;

    /**
     */
    public $login;

    /**
     */
    public $pwd;

    /**
     */
    public $hashAlg;

    /**
     * @var Content
     */
    public $profile;
}
?>
