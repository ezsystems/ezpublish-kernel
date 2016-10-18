<?php

/**
 * File containing the eZ\Publish\API\Repository\Values\User\PermissionLimitationSet class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\User;

use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class represents a permission limitation set, a part of PermissionInfo.
 *
 * A permission limitation set is a set of limitations which can give access to given module/function
 * if user action is done within the given limitations. Limitations are only the once that could not
 * be determined with the context provided to
 * {@link \eZ\Publish\API\Repository\PermissionResolver::getUserPermissionInfo()}, other limitations
 * not returned where successfully evaluated to true.
 *
 * @property-read string $policyId  The Policy this given set of limitations comes from.
 */
abstract class PermissionLimitationSet extends ValueObject
{
    /**
     * Policy ID where this limitations derive from.
     *
     * @var mixed
     */
    protected $policyId;

    /**
     * Sets of limitation values that can gives access to a given module/function.
     *
     * Values return reflect values on policies relevant to given module function that
     * are directly or indirectly assigned to current user.
     *
     * @return \eZ\Publish\API\Repository\Values\User\Limitation[]
     */
    abstract public function getLimitations();
}
