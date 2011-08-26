<?php
/**
 * File containing User object
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp;
use ezp\Base\Model,
    ezp\Base\Collection\Type as TypeCollection,
    ezp\Persistence\User as UserValue;

/**
 * This class represents a User item
 *
 * @property-read mixed $id
 * @property string $login
 * @property string $email
 * @property string $password
 * @property int $hashAlgorithm
 */
class User extends Model
{
    /**
     * @var array Readable of properties on this object
     */
    protected $readWriteProperties = array(
        'id' => false,
        'login' => true,
        'email' => true,
        'password' => true,
        'hashAlgorithm' => false,
    );

    /**
     * @var array Dynamic properties on this object
     */
    protected $dynamicProperties = array(
        //'groups' => false,
        //'policies' => false,
        //'roles' => false,
    );

    /**
     * Creates and setups User object
     */
    public function __construct()
    {
        $this->properties = new UserValue();
    }
}
?>
