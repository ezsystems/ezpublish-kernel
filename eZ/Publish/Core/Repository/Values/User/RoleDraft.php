<?php

/**
 * File containing the eZ\Publish\Core\Repository\Values\User\RoleDraft class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Values\User;

use eZ\Publish\API\Repository\Values\User\RoleDraft as APIRoleDraft;

/**
 * This class represents a draft of a role.
 *
 * @internal Meant for internal use by Repository, type hint against API object instead.
 */
class RoleDraft extends APIRoleDraft
{
    /**
     * Holds internal role object.
     *
     * @var \eZ\Publish\API\Repository\Values\User\Role
     *
     * @todo document
     */
    protected $innerRole;

    /**
     * Magic getter for routing get calls to innerRole.
     *
     * @param string $property The name of the property to retrieve
     *
     * @return mixed
     */
    public function __get($property)
    {
        return $this->innerRole->$property;
    }

    /**
     * Magic set for routing set calls to innerRole.
     *
     * @param string $property
     * @param mixed $propertyValue
     */
    public function __set($property, $propertyValue)
    {
        $this->innerRole->$property = $propertyValue;
    }

    /**
     * Magic isset for routing isset calls to innerRole.
     *
     * @param string $property
     *
     * @return bool
     */
    public function __isset($property)
    {
        return $this->innerRole->__isset($property);
    }

    /**
     * Returns the list of policies of this role.
     *
     * @return \eZ\Publish\API\Repository\Values\User\PolicyDraft[]
     */
    public function getPolicies()
    {
        return $this->innerRole->getPolicies();
    }
}
