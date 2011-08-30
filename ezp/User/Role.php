<?php
/**
 * File containing Role object
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\User;
use ezp\Base\Model,
    ezp\Base\Collection\Type as TypeCollection,
    ezp\Persistence\User\Role as RoleValue;

/**
 * This class represents a Role item
 *
 * @property-read mixed $id
 * @property string $name
 * @property-read mixed[] $groupIds Use {@link \ezp\User\Service::addGroup} & {@link \ezp\User\Service::removeGroup}
 * @property-read \ezp\User\Policy[] $policies Use {@link \ezp\User\Service::addPolicy} & {@link \ezp\User\Service::removePolicy}
 */
class Role extends Model
{
    /**
     * @var array Readable of properties on this object
     */
    protected $readWriteProperties = array(
        'id' => false,
        'name' => true,
        'groupIds' => false,
    );

    /**
     * @var array Dynamic properties on this object
     */
    protected $dynamicProperties = array(
        //'groups' => false,
        'policies' => true,
    );

    /**
     * @var \ezp\User\Policy[]
     */
    protected $policies = array();

    /**
     * Creates and setups User object
     */
    public function __construct()
    {
        $this->properties = new RoleValue();
    }

    /**
     * @return \ezp\User\Policy[]
     */
    protected function getPolicies()
    {
        return $this->policies;
    }

    /**
     * @internal Use {@link \ezp\User\Service::addPolicy()}
     * @param Policy $policy
     * @return void
     */
    public function addPolicy( Policy $policy )
    {
        if ( in_array( $policy, $this->policies, true ) )
            return;

        $this->policies[] = $policy;
        $this->properties->policies[] = $policy->getState( 'properties' );
    }

    /**
     * @internal Use {@link \ezp\User\Service::removePolicy()}
     * @param Policy $policy
     * @return void
     */
    public function removePolicy( Policy $policy )
    {
        $key = array_search( $policy, $this->policies, true );
        if ( $key === false )
            return;

        unset( $this->policies[$key] );
        unset( $this->properties->policies[$key] );//order should be same, so reusing key
    }
}
