<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\User;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class represents User's permission information for a module, function and a set
 * of limitations.
 *
 * This value object does not represent an entity, but the information calculated resolving User's
 * policies and Role limitations against a given module, function and a set of limitations.
 *
 * @see \eZ\Publish\API\Repository\PermissionResolver::lookup()
 *
 * @property-read string $access One of ACCESS_GRANTED, ACCESS_LIMITED or ACCESS_DENIED constants.
 * @property-read \eZ\Publish\API\Repository\Values\User\Limitation[][] $limitationSets
 */
class PermissionInfo extends ValueObject
{
    /**
     * Indicates full access.
     */
    const ACCESS_GRANTED = true;

    /**
     * Indicates limited access, described by limitation sets.
     *
     * @see \eZ\Publish\API\Repository\Values\User\PermissionInfo::$limitationSets
     */
    const ACCESS_LIMITED = null;

    /**
     * Indicates denied access.
     */
    const ACCESS_DENIED = false;

    /**
     * Indicates access rights for the user on a value object on the current module function.
     *
     * @var mixed One of ACCESS_GRANTED, ACCESS_LIMITED or ACCESS_DENIED constants.
     */
    protected $access;

    /**
     * Limitation Values here are combinations of limitation values on policies relevant to given
     * module and function. Limitations are grouped into sets, which are to be evaluated separately.
     * If any set matches your values, access should be granted.
     *
     * @return \eZ\Publish\API\Repository\Values\User\Limitation[][]
     */
    protected $limitationSets = [];
}
