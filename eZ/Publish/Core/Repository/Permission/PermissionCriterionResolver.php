<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Permission;

use eZ\Publish\API\Repository\PermissionCriterionResolver as APIPermissionCriterionResolver;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOr;
use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\API\Repository\PermissionResolver as PermissionResolverInterface;
use eZ\Publish\Core\Repository\Helper\LimitationService;
use RuntimeException;

/**
 * Implementation of Permissions Criterion Resolver.
 */
class PermissionCriterionResolver implements APIPermissionCriterionResolver
{
    /**
     * @var \eZ\Publish\API\Repository\PermissionResolver
     */
    private $permissionResolver;

    /**
     * @var \eZ\Publish\Core\Repository\Helper\LimitationService
     */
    private $limitationService;

    /**
     * Constructor.
     *
     * @param \eZ\Publish\API\Repository\PermissionResolver $permissionResolver
     * @param \eZ\Publish\Core\Repository\Helper\LimitationService $limitationService
     */
    public function __construct(
        PermissionResolverInterface $permissionResolver,
        LimitationService $limitationService
    ) {
        $this->permissionResolver = $permissionResolver;
        $this->limitationService = $limitationService;
    }

    /**
     * Get content-read Permission criteria if needed and return false if no access at all.
     *
     * @uses \eZ\Publish\API\Repository\PermissionResolver::getCurrentUserReference()
     * @uses \eZ\Publish\API\Repository\PermissionResolver::hasAccess()
     *
     * @throws \RuntimeException If empty array of limitations are provided from hasAccess()
     *
     * @param string $module
     * @param string $function
     *
     * @return bool|\eZ\Publish\API\Repository\Values\Content\Query\Criterion
     */
    public function getPermissionsCriterion($module = 'content', $function = 'read')
    {
        $permissionSets = $this->permissionResolver->hasAccess($module, $function);
        if (is_bool($permissionSets)) {
            return $permissionSets;
        }

        if (empty($permissionSets)) {
            throw new RuntimeException("Got an empty array of limitations from hasAccess( '{$module}', '{$function}' )");
        }

        /*
         * RoleAssignment is a OR condition, so is policy, while limitations is a AND condition
         *
         * If RoleAssignment has limitation then policy OR conditions are wrapped in a AND condition with the
         * role limitation, otherwise it will be merged into RoleAssignment's OR condition.
         */
        $currentUserRef = $this->permissionResolver->getCurrentUserReference();
        $roleAssignmentOrCriteria = [];
        foreach ($permissionSets as $permissionSet) {
            // $permissionSet is a RoleAssignment, but in the form of role limitation & role policies hash
            $policyOrCriteria = [];
            /**
             * @var \eZ\Publish\API\Repository\Values\User\Policy
             */
            foreach ($permissionSet['policies'] as $policy) {
                $limitations = $policy->getLimitations();
                if ($limitations === '*' || empty($limitations)) {
                    // Given policy gives full access, optimize away all role policies (but not role limitation if any)
                    // This should be optimized on create/update of Roles, however we keep this here for bc with older data
                    $policyOrCriteria = [];
                    break;
                }

                $limitationsAndCriteria = [];
                foreach ($limitations as $limitation) {
                    $type = $this->limitationService->getLimitationType($limitation->getIdentifier());
                    $limitationsAndCriteria[] = $type->getCriterion($limitation, $currentUserRef);
                }

                $policyOrCriteria[] = isset($limitationsAndCriteria[1]) ?
                    new LogicalAnd($limitationsAndCriteria) :
                    $limitationsAndCriteria[0];
            }

            /**
             * Apply role limitations if there is one.
             *
             * @var \eZ\Publish\API\Repository\Values\User\Limitation[]
             */
            if ($permissionSet['limitation'] instanceof Limitation) {
                // We need to match both the limitation AND *one* of the policies, aka; roleLimit AND policies(OR)
                $type = $this->limitationService->getLimitationType($permissionSet['limitation']->getIdentifier());
                if (!empty($policyOrCriteria)) {
                    $roleAssignmentOrCriteria[] = new LogicalAnd(
                        [
                            $type->getCriterion($permissionSet['limitation'], $currentUserRef),
                            isset($policyOrCriteria[1]) ? new LogicalOr($policyOrCriteria) : $policyOrCriteria[0],
                        ]
                    );
                } else {
                    $roleAssignmentOrCriteria[] = $type->getCriterion($permissionSet['limitation'], $currentUserRef);
                }
            } elseif (!empty($policyOrCriteria)) {
                // Otherwise merge $policyOrCriteria into $roleAssignmentOrCriteria
                // There is no role limitation, so any of the policies can globally match in the returned OR criteria
                $roleAssignmentOrCriteria = empty($roleAssignmentOrCriteria) ?
                    $policyOrCriteria :
                    array_merge($roleAssignmentOrCriteria, $policyOrCriteria);
            }
        }

        if (empty($roleAssignmentOrCriteria)) {
            return false;
        }

        return isset($roleAssignmentOrCriteria[1]) ?
            new LogicalOr($roleAssignmentOrCriteria) :
            $roleAssignmentOrCriteria[0];
    }
}
