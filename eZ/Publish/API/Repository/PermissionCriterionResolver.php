<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * This service provides methods for resolving criterion permissions.
 *
 * @since 6.7.7
 */
interface PermissionCriterionResolver
{
    /**
     * Get criteria representation for a permission.
     *
     * Will return a criteria if current user has limited access to the given module/function,
     * however if user has either full or no access then boolean is returned.
     *
     * @param string $module
     * @param string $function
     * @param array|null $targets
     *
     * @return bool|\eZ\Publish\API\Repository\Values\Content\Query\Criterion
     */
    public function getPermissionsCriterion(string $module = 'content', string $function = 'read', ?array $targets = null);

    /**
     * Get composite Criterion for Querying permissions.
     *
     * {@see \eZ\Publish\API\Repository\Values\Content\Query\Criterion\MatchAll}
     * and {@see \eZ\Publish\API\Repository\Values\Content\Query\Criterion\MatchNone} are returned
     * for a user with full and no access respectively.
     */
    public function getQueryPermissionsCriterion(): Criterion;
}
