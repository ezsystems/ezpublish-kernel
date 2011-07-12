<?php
/**
 * File containing the Role class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage persistence_user
 */

namespace ezp\Persistence\User;

/**
 * @package ezp
 * @subpackage persistence_user
 */
class Role extends \ezp\Persistence\AbstractValueObject
{
    /**
     * Name of the role
     *
     * @var string
     */
    public $name;

    /**
     * ID of the user rule
     *
     * @var mixed
     */
    public $id;

    /**
     * Policies associated with the role
     *
     * @var user\Policy[]
     */
    public $policies = array();
}
?>
