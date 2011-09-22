<?php
/**
 * File containing Group interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\User;
use ezp\Base\Collection\Type as TypeCollection,
    ezp\Base\Exception\PropertyNotFound,
    ezp\Base\Exception\PropertyPermission,
    ezp\Base\Observer,
    ezp\Content,
    ezp\Content\Type,
    ezp\Content\Type\FieldDefinition;

/**
 * This interface represents a Group item
 *
 * Group is currently a facade for content objects of User Group type.
 * It requires that the User Group Content Type used has two attributes: name & description, both ezstring field types
 *
 * @property-read mixed $id
 * @property string $name
 * @property string description
 */
interface Group
{
    /**
     * @return \ezp\User\Group|null
     */
    public function getParent();

    /**
     * Roles assigned to Group
     *
     * Use {@link \ezp\User\Service::assignRole} & {@link \ezp\User\Service::unassignRole} to change
     *
     * @return \ezp\User\Role[]
     */
    public function getRoles();

    /**
     * Return list of properties, where key is properties and value depends on type and is internal so should be ignored for now.
     *
     * @return array
     */
    public function properties();
}
