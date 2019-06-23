<?php

/**
 * File containing the eZ\Publish\Core\Repository\PermissionsCriterionHandler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\Core\Repository\Permission\PermissionCriterionResolver;

/**
 * Handler for permissions Criterion.
 *
 * @deprecated 6.7.7
 */
class PermissionsCriterionHandler extends PermissionCriterionResolver
{
    /**
     * Adds content, read Permission criteria if needed and return false if no access at all.
     *
     * @uses \eZ\Publish\Core\Repository\Permission\PermissionCriterionResolver::getPermissionsCriterion()
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return bool|\eZ\Publish\API\Repository\Values\Content\Query\Criterion
     */
    public function addPermissionsCriterion(Criterion &$criterion)
    {
        $permissionCriterion = $this->getPermissionsCriterion();
        if ($permissionCriterion === true || $permissionCriterion === false) {
            return $permissionCriterion;
        }

        // Merge with original $criterion
        if ($criterion instanceof LogicalAnd) {
            $criterion->criteria[] = $permissionCriterion;
        } else {
            $criterion = new LogicalAnd(
                [
                    $criterion,
                    $permissionCriterion,
                ]
            );
        }

        return true;
    }
}
