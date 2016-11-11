<?php

/**
 * File containing the RestRole class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Values;

use eZ\Publish\API\Repository\Values\User\Role;
use eZ\Publish\Core\REST\Common\Value as RestValue;

/**
 * REST Role, as received by /roles/<ID>.
 */
class RestRole extends RestValue
{
    /**
     * Holds internal role object.
     *
     * @var \eZ\Publish\API\Repository\Values\User\Role
     */
    protected $innerRole;

    /**
     * Construct.
     *
     * @param \eZ\Publish\API\Repository\Values\User\Role $role
     */
    public function __construct(Role $role)
    {
        $this->innerRole = $role;
    }

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
}
