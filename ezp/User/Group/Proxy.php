<?php
/**
 * File containing Proxy Group class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\User\Group;
use ezp\Base\Proxy\Model as ModelProxy,
    ezp\Base\ModelInterface,
    ezp\Base\Observable,
    ezp\Base\Observer,
    ezp\User\Group,
    ezp\User\Groupable,
    ezp\User\Service;

/**
 * This class represents a Proxy Group item
 *
 * Group is currently a facade for content objects of User Group type.
 * It requires that the User Group Content Type used has two attributes: name & description, both ezstring field types
 *
 * @property-read mixed $id
 * @property string $name
 * @property string description
 */
class Proxy extends ModelProxy implements Group, Groupable, Observable
{
    public function __construct( $id, Service $service )
    {
        parent::__construct( $id, $service );
    }

    protected function lazyLoad()
    {
        if ( $this->proxiedObject === null )
        {
            $this->proxiedObject = $this->service->loadGroup( $this->proxiedObjectId );
            $this->moveObservers();
        }
    }

    /**
     * @return \ezp\User\Group|null
     */
    public function getParent()
    {
        $this->lazyLoad();
        return $this->proxiedObject->getParent();
    }

    /**
     * Roles assigned to Group
     *
     * Use {@link \ezp\User\Service::assignRole} & {@link \ezp\User\Service::unassignRole} to change
     *
     * @return \ezp\User\Role[]
     */
    public function getRoles()
    {
        $this->lazyLoad();
        return $this->proxiedObject->getRoles();
    }

    /**
     * Return list of properties, where key is properties and value depends on type and is internal so should be ignored for now.
     *
     * @return array
     */
    public function properties()
    {
        $this->lazyLoad();
        return $this->proxiedObject->properties();
    }
}
