<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\User\PermissionInfo class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\User;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class represents permission info for a given User and a given Value object.
 *
 * This value object does not represent an entity, but permission info for the given user, by the arguments
 * provided to {@link \eZ\Publish\API\Repository\PermissionResolver::getUserPermissionInfo()}
 *
 * @property-read string $module  Name of the module permissions was queried on.
 * @property-read string $function  Name of the module function permissions was queried on.
 * @property-read string $access  One of ACCESS_GRANTED, ACCESS_LIMITED or ACCESS_DENIED constants.
 */
abstract class PermissionInfo extends ValueObject
{
    const ACCESS_GRANTED = true;
    const ACCESS_LIMITED = null;
    const ACCESS_DENIED = false;

    /**
     * Indicates access rights for the user on a value object on the current module function.
     *
     * @var mixed One of ACCESS_GRANTED, ACCESS_LIMITED or ACCESS_DENIED constants.
     */
    protected $access;

    /**
     * Sets of limitation values that gives access of the given module/function.
     *
     * Only contains values if $access equals ACCESS_LIMITED. Values returned here represents policies
     * that may give access. Those that did not give accesses have been omitted, and if anyone had already
     * given full access $access would have been set to ACCESS_GRANTED.
     *
     * @return \eZ\Publish\API\Repository\Values\User\PermissionLimitationSet[]
     */
    abstract public function getPermissionLimitationSets();
}
