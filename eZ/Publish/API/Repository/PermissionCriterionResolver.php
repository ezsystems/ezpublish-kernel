<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository;

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
    public function getPermissionsCriterion($module, $function, ?array $targets = null);
}
