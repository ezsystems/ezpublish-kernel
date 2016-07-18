<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\PermissionResolver;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\API\Repository\Values\User\UserReference;
use eZ\Publish\Core\Repository\Helper\LimitationService;
use eZ\Publish\SPI\Limitation\Type as LimitationType;
use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\SPI\Persistence\User\Handler as UserHandler;
use eZ\Publish\Core\Repository\Helper\RoleDomainMapper;

/**
 * todo
 */
class PermissionResolver
{
    /**
     * @var \eZ\Publish\Core\Repository\Helper\RoleDomainMapper
     */
    private $roleDomainMapper;

    /**
     * @var \eZ\Publish\Core\Repository\Helper\LimitationService
     */
    private $limitationService;

    /**
     * @var \eZ\Publish\SPI\Persistence\User\Handler
     */
    private $userHandler;

    /**
     * @param \eZ\Publish\Core\Repository\Helper\RoleDomainMapper $roleDomainMapper
     * @param \eZ\Publish\Core\Repository\Helper\LimitationService $limitationService
     * @param \eZ\Publish\SPI\Persistence\User\Handler $userHandler
     */
    public function __construct(
        RoleDomainMapper $roleDomainMapper,
        LimitationService $limitationService,
        UserHandler $userHandler
    ) {
        $this->roleDomainMapper = $roleDomainMapper;
        $this->limitationService = $limitationService;
        $this->userHandler = $userHandler;
    }

    /**
     * todo
     *
     * @param string $module
     * @param string $function
     * @param \eZ\Publish\Core\Repository\PermissionResolver\Permission[] $permissions
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object
     * @param \eZ\Publish\API\Repository\Values\User\UserReference $userReference
     * @param array $targets
     *
     * @return bool
     */
    public function resolvePermissions(
        $module,
        $function,
        $permissions,
        ValueObject $object,
        UserReference $userReference,
        array $targets = []
    ) {
        foreach ($permissions as $permission) {
            $access = $this->resolvePermission(
                $module,
                $function,
                $permission,
                $object,
                $userReference,
                $targets
            );

            if ($access === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $module
     * @param string $function
     * @param \eZ\Publish\Core\Repository\PermissionResolver\Permission $permission
     * @param \eZ\Publish\API\Repository\Values\ValueObject $object
     * @param \eZ\Publish\API\Repository\Values\User\UserReference $userReference
     * @param array $targets
     *
     * @return bool
     */
    public function resolvePermission(
        $module,
        $function,
        Permission $permission,
        ValueObject $object,
        UserReference $userReference,
        array $targets = []
    ) {
        if (empty($targets)) {
            $targets = null;
        }

        /**
         * First deal with Role limitation if any.
         *
         * Here we accept ACCESS_GRANTED and ACCESS_ABSTAIN, the latter in cases where $object and $targets
         * are not supported by limitation.
         *
         * @var \eZ\Publish\API\Repository\Values\User\Limitation[]
         */
        if ($permission->limitation instanceof Limitation) {
            $type = $this->limitationService->getLimitationType($permission->limitation->getIdentifier());
            $accessVote = $type->evaluate($permission->limitation, $userReference, $object, $targets);
            if ($accessVote === LimitationType::ACCESS_DENIED) {
                return false;
            }
        }

        /**
         * Loop over all policies.
         *
         * These are already filtered by hasAccess and given hasAccess did not return boolean
         * there must be some, so only return true if one of them says yes.
         *
         * @var \eZ\Publish\API\Repository\Values\User\Policy $policy
         */
        foreach ($permission->policies as $policy) {
            if (!($policy->module === $module || $policy->module === '*')) {
                continue;
            }

            if (!($policy->function === $function || $policy->function === '*')) {
                continue;
            }

            $limitations = $policy->getLimitations();

            /*
             * Return true if policy gives full access (aka no limitations)
             */
            if ($limitations === '*') {
                return true;
            }

            /*
             * Loop over limitations, all must return ACCESS_GRANTED for policy to pass.
             * If limitations was empty array this means same as '*'
             */
            $limitationsPass = true;
            foreach ($limitations as $limitation) {
                $type = $this->limitationService->getLimitationType($limitation->getIdentifier());
                $accessVote = $type->evaluate($limitation, $userReference, $object, $targets);
                /*
                 * For policy limitation atm only support ACCESS_GRANTED
                 *
                 * Reasoning: Right now, use of a policy limitation not valid for a policy is per definition a
                 * BadState. To reach this you would have to configure the "policyMap" wrongly, like using
                 * Node (Location) limitation on state/assign. So in this case Role Limitations will return
                 * ACCESS_ABSTAIN (== no access here), and other limitations will throw InvalidArgument above,
                 * both cases forcing dev to investigate to find miss configuration. This might be relaxed in
                 * the future if valid use cases for ACCESS_ABSTAIN on policy limitations becomes known.
                 */
                if ($accessVote !== LimitationType::ACCESS_GRANTED) {
                    $limitationsPass = false;
                    break;// Break to next policy, all limitations must pass
                }
            }
            if ($limitationsPass) {
                return true;
            }
        }

        return false;
    }

    /**
     * todo
     *
     * @param \eZ\Publish\API\Repository\Values\User\UserReference $userReference
     * @param string $module
     * @param string $function
     *
     * @return \eZ\Publish\Core\Repository\PermissionResolver\Permission[]
     */
    public function getPermissions(UserReference $userReference, $module = '*', $function = '*')
    {
        // Uses SPI to avoid triggering permission checks in Role/User service
        $permissionSets = [];
        $spiRoleAssignments = $this->userHandler->loadRoleAssignmentsByGroupId($userReference->getUserId(), true);
        foreach ($spiRoleAssignments as $spiRoleAssignment) {
            $permissionSet = ['limitation' => null, 'policies' => []];

            $spiRole = $this->userHandler->loadRole($spiRoleAssignment->roleId);
            foreach ($spiRole->policies as $spiPolicy) {
                if (!($spiPolicy->module === $module || $spiPolicy->module === '*' || $module === '*')) {
                    continue;
                }

                if (!($spiPolicy->function === $function || $spiPolicy->function === '*' || $function === '*')) {
                    continue;
                }

                $permissionSet['policies'][] = $this->roleDomainMapper->buildDomainPolicyObject($spiPolicy);
            }

            if (!empty($permissionSet['policies'])) {
                if ($spiRoleAssignment->limitationIdentifier !== null) {
                    $permissionSet['limitation'] = $this->limitationService
                        ->getLimitationType($spiRoleAssignment->limitationIdentifier)
                        ->buildValue($spiRoleAssignment->values);
                }

                $permissionSets[] = new Permission($permissionSet);
            }
        }

        return $permissionSets;
    }
}
