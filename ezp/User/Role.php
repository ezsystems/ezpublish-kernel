<?php
/**
 * File containing the ezp\User\Role interface.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\User;
use ezp\Base\ModelDefinition,
    ezp\User;

/**
 * This class represents a Proxy Role item
 *
 * @property-read mixed $id
 * @property string $name
 * @property-read mixed[] $groupIds Use {@link \ezp\User\Service::addGroup} & {@link \ezp\User\Service::removeGroup}
 * @property-read \ezp\User\Policy[] $policies Use {@link \ezp\User\Service::addPolicy} & {@link \ezp\User\Service::removePolicy}
 */
interface Role
{
    /**
     * @return \ezp\User\Policy[]
     */
    public function getPolicies();

    /**
     * @internal Use {@link \ezp\User\Service::addPolicy()}
     * @param Policy $policy
     * @return void
     */
    public function addPolicy( Policy $policy );

    /**
     * @internal Use {@link \ezp\User\Service::removePolicy()}
     * @param Policy $policy
     * @return void
     */
    public function removePolicy( Policy $policy );
}
