<?php
/**
 * File containing the ezp\User\Proxy class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\User;
use ezp\Base\Proxy\Model as ModelProxy,
    ezp\User;

/**
 * This class represents a Proxy User object
 *
 * @property-read mixed $id
 * @property string $login
 * @property string $email
 * @property string $password
 * @property int $hashAlgorithm
 * @property \ezp\User\Group[] $groups
 * @property \ezp\User\Role[] $roles
 * @property \ezp\User\Policy[] $policies
 */
class Proxy extends ModelProxy implements User
{
    public function __construct( $id, Service $service )
    {
        parent::__construct( $id, $service );
    }

    /**
     * Returns definition of the user object, atm: permissions
     *
     * @access private
     * @return array
     */
    public static function definition()
    {
        return Concrete::definition();
    }

    /**
     * List of assigned groups
     *
     * @return \ezp\User\Group[]
     */
    public function getGroups()
    {
        $this->lazyLoad();
        return $this->proxiedObject->getGroups();
    }

    /**
     * List of assigned Roles
     *
     * @return array|User\Role[]
     */
    public function getRoles()
    {
        $this->lazyLoad();
        return $this->proxiedObject->getRoles();
    }

    /**
     * List of assigned and inherited policies (via assigned and inherited roles)
     *
     * @return array|User\Policy[]
     */
    public function getPolicies()
    {
        $this->lazyLoad();
        return $this->proxiedObject->getPolicies();
    }

    /**
     * Checks if user has access to a specific module/function
     *
     * Return array of limitations if user has access to a certain function
     * but limited by the returned limitations.
     * If you have the model instance you want to check permissions against, then
     * use {@link \ezp\Base\Repository::canUser()}.
     *
     * @param string $module
     * @param string $function
     * @return array|bool
     */
    public function hasAccessTo( $module, $function )
    {
        $this->lazyLoad();
        return $this->proxiedObject->hasAccessTo( $module, $function );
    }
}
