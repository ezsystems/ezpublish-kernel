<?php
/**
 * File containing the ezp\User\Role\Proxy class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\User\Role;
use ezp\Base\Proxy\Observable as ObservableProxy,
    ezp\User\Policy,
    ezp\User\Service,
    ezp\User\Role;

/**
 * This class represents a Proxy Role item
 *
 * @property-read mixed $id
 * @property string $name
 * @property-read mixed[] $groupIds Use {@link \ezp\User\Service::addGroup} & {@link \ezp\User\Service::removeGroup}
 * @property-read \ezp\User\Policy[] $policies Use {@link \ezp\User\Service::addPolicy} & {@link \ezp\User\Service::removePolicy}
 */
class Proxy extends ObservableProxy implements Role
{
    public function __construct( $id, Service $service )
    {
        parent::__construct( $id, $service );
    }

    protected function lazyLoad()
    {
        if ( $this->proxiedObject === null )
        {
            $this->proxiedObject = $this->service->loadRole( $this->id );
        }
    }

    /**
     * Returns definition of the section object, atm: permissions
     *
     * @access private
     * @return array
     */
    public static function definition()
    {
        return Concrete::definition();
    }

    /**
     * @return \ezp\User\Policy[]
     */
    public function getPolicies()
    {
        $this->lazyLoad();
        $this->proxiedObject->getPolicies();
    }

    /**
     * @internal Use {@link \ezp\User\Service::addPolicy()}
     * @param Policy $policy
     * @return void
     */
    public function addPolicy( Policy $policy )
    {
        $this->lazyLoad();
        $this->proxiedObject->addPolicy( $policy );
    }

    /**
     * @internal Use {@link \ezp\User\Service::removePolicy()}
     * @param Policy $policy
     * @return void
     */
    public function removePolicy( Policy $policy )
    {
        $this->lazyLoad();
        $this->proxiedObject->removePolicy( $policy );
    }
}
