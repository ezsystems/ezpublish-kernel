<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Limitation;

use eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface;
use eZ\Publish\API\Repository\Values\User\Limitation as APILimitationValue;
use eZ\Publish\API\Repository\Values\User\UserReference as APIUserReference;
use eZ\Publish\SPI\Limitation\Type as LimitationTypeInterface;

/**
 * Limitation type which doesn't take $object into consideration while evaluation.
 *
 * @see \eZ\Publish\Core\Repository\Permission\PermissionCriterionResolver::getPermissionsCriterion
 */
interface TargetOnlyLimitationType extends LimitationTypeInterface
{
    /**
     * Returns criterion based on given $target for use in find() query.
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $value
     * @param \eZ\Publish\API\Repository\Values\User\UserReference $currentUser
     * @param array|null $targets
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function getCriterionByTarget(APILimitationValue $value, APIUserReference $currentUser, ?array $targets): CriterionInterface;
}
