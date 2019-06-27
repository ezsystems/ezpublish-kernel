<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Permission;

use eZ\Publish\API\Repository\PermissionCriterionResolver as APIPermissionCriterionResolver;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalAnd;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOr;
use eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface;
use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\API\Repository\PermissionResolver as PermissionResolverInterface;
use eZ\Publish\API\Repository\Values\User\UserReference;
use eZ\Publish\Core\Repository\Helper\LimitationService;
use eZ\Publish\Core\Limitation\TargetOnlyLimitationType;
use RuntimeException;

/**
 * Implementation of Permissions Criterion Resolver.
 */
class PermissionCriterionResolver implements APIPermissionCriterionResolver
{
    /** @var \eZ\Publish\API\Repository\PermissionResolver */
    private $permissionResolver;

    /** @var \eZ\Publish\Core\Repository\Helper\LimitationService */
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
     * Get permission criteria if needed and return false if no access at all.
     *
     * @uses \eZ\Publish\API\Repository\PermissionResolver::getCurrentUserReference()
     * @uses \eZ\Publish\API\Repository\PermissionResolver::hasAccess()
     *
     * @param string $module
     * @param string $function
     * @param array $targets
     *
     * @return bool|\eZ\Publish\API\Repository\Values\Content\Query\Criterion
     */
    public function getPermissionsCriterion($module = 'content', $function = 'read', ?array $targets = null)
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
            /** @var \eZ\Publish\API\Repository\Values\User\Policy */
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
                    $limitationsAndCriteria[] = $this->getCriterionForLimitation($limitation, $currentUserRef, $targets);
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
                if (!empty($policyOrCriteria)) {
                    $criterion = $this->getCriterionForLimitation($permissionSet['limitation'], $currentUserRef, $targets);
                    $roleAssignmentOrCriteria[] = new LogicalAnd(
                        [
                            $criterion,
                            isset($policyOrCriteria[1]) ? new LogicalOr($policyOrCriteria) : $policyOrCriteria[0],
                        ]
                    );
                } else {
                    $roleAssignmentOrCriteria[] = $this->getCriterionForLimitation(
                        $permissionSet['limitation'], $currentUserRef, $targets
                    );
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

    /**
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $limitation
     * @param \eZ\Publish\API\Repository\Values\User\UserReference $currentUserRef
     * @param array|null $targets
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface|\eZ\Publish\API\Repository\Values\Content\Query\Criterion\LogicalOperator
     */
    private function getCriterionForLimitation(Limitation $limitation, UserReference $currentUserRef, ?array $targets): CriterionInterface
    {
        $type = $this->limitationService->getLimitationType($limitation->getIdentifier());
        if ($type instanceof TargetOnlyLimitationType) {
            return $type->getCriterionByTarget($limitation, $currentUserRef, $targets);
        }

        return $type->getCriterion($limitation, $currentUserRef);
    }
}
