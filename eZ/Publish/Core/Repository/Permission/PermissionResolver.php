<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Permission;

use eZ\Publish\API\Repository\PermissionResolver as PermissionResolverInterface;
use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\API\Repository\Values\User\UserReference as APIUserReference;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
use eZ\Publish\Core\Repository\Helper\LimitationService;
use eZ\Publish\Core\Repository\Helper\RoleDomainMapper;
use eZ\Publish\SPI\Limitation\Type as LimitationType;
use eZ\Publish\SPI\Persistence\User\Handler as UserHandler;
use Closure;
use Exception;

/**
 * Core implementation of PermissionResolver interface.
 */
class PermissionResolver implements PermissionResolverInterface
{
    /**
     * Counter for the current sudo nesting level {@see sudo()}.
     *
     * @var int
     */
    private $sudoNestingLevel = 0;

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
     * Currently logged in user reference for permission purposes.
     *
     * @var \eZ\Publish\API\Repository\Values\User\UserReference
     */
    private $currentUserRef;

    /**
     * Map of system configured policies, for validation usage.
     *
     * @var array
     */
    private $policyMap;

    /**
     * @param \eZ\Publish\Core\Repository\Helper\RoleDomainMapper $roleDomainMapper
     * @param \eZ\Publish\Core\Repository\Helper\LimitationService $limitationService
     * @param \eZ\Publish\SPI\Persistence\User\Handler $userHandler
     * @param \eZ\Publish\API\Repository\Values\User\UserReference $userReference
     * @param array $policyMap Map of system configured policies, for validation usage.
     */
    public function __construct(
        RoleDomainMapper $roleDomainMapper,
        LimitationService $limitationService,
        UserHandler $userHandler,
        APIUserReference $userReference,
        array $policyMap = []
    ) {
        $this->roleDomainMapper = $roleDomainMapper;
        $this->limitationService = $limitationService;
        $this->userHandler = $userHandler;
        $this->currentUserRef = $userReference;
        $this->policyMap = $policyMap;
    }

    public function getCurrentUserReference()
    {
        return $this->currentUserRef;
    }

    public function setCurrentUserReference(APIUserReference $userReference)
    {
        $id = $userReference->getUserId();
        if (!$id) {
            throw new InvalidArgumentValue('$user->getUserId()', $id);
        }

        $this->currentUserRef = $userReference;
    }

    public function hasAccess($module, $function, APIUserReference $userReference = null)
    {
        if (!isset($this->policyMap[$module])) {
            throw new InvalidArgumentValue('module', "module: {$module}/ function: {$function}");
        } elseif (!array_key_exists($function, $this->policyMap[$module])) {
            throw new InvalidArgumentValue('function', "module: {$module}/ function: {$function}");
        }

        // Full access if sudo nesting level is set by {@see sudo()}
        if ($this->sudoNestingLevel > 0) {
            return true;
        }

        if ($userReference === null) {
            $userReference = $this->getCurrentUserReference();
        }

        // Uses SPI to avoid triggering permission checks in Role/User service
        $permissionSets = array();
        $spiRoleAssignments = $this->userHandler->loadRoleAssignmentsByGroupId($userReference->getUserId(), true);
        foreach ($spiRoleAssignments as $spiRoleAssignment) {
            $permissionSet = array('limitation' => null, 'policies' => array());

            $spiRole = $this->userHandler->loadRole($spiRoleAssignment->roleId);
            foreach ($spiRole->policies as $spiPolicy) {
                if ($spiPolicy->module === '*' && $spiRoleAssignment->limitationIdentifier === null) {
                    return true;
                }

                if ($spiPolicy->module !== $module && $spiPolicy->module !== '*') {
                    continue;
                }

                if ($spiPolicy->function === '*' && $spiRoleAssignment->limitationIdentifier === null) {
                    return true;
                }

                if ($spiPolicy->function !== $function && $spiPolicy->function !== '*') {
                    continue;
                }

                if ($spiPolicy->limitations === '*' && $spiRoleAssignment->limitationIdentifier === null) {
                    return true;
                }

                $permissionSet['policies'][] = $this->roleDomainMapper->buildDomainPolicyObject($spiPolicy);
            }

            if (!empty($permissionSet['policies'])) {
                if ($spiRoleAssignment->limitationIdentifier !== null) {
                    $permissionSet['limitation'] = $this->limitationService
                        ->getLimitationType($spiRoleAssignment->limitationIdentifier)
                        ->buildValue($spiRoleAssignment->values);
                }

                $permissionSets[] = $permissionSet;
            }
        }

        if (!empty($permissionSets)) {
            return $permissionSets;
        }

        return false; // No policies matching $module and $function, or they contained limitations
    }

    public function canUser($module, $function, ValueObject $object, array $targets = [])
    {
        $permissionSets = $this->hasAccess($module, $function);
        if ($permissionSets === false || $permissionSets === true) {
            return $permissionSets;
        }

        if (empty($targets)) {
            $targets = null;
        }

        $currentUserRef = $this->getCurrentUserReference();
        foreach ($permissionSets as $permissionSet) {
            /**
             * First deal with Role limitation if any.
             *
             * Here we accept ACCESS_GRANTED and ACCESS_ABSTAIN, the latter in cases where $object and $targets
             * are not supported by limitation.
             *
             * @var \eZ\Publish\API\Repository\Values\User\Limitation[]
             */
            if ($permissionSet['limitation'] instanceof Limitation) {
                $type = $this->limitationService->getLimitationType($permissionSet['limitation']->getIdentifier());
                $accessVote = $type->evaluate($permissionSet['limitation'], $currentUserRef, $object, $targets);
                if ($accessVote === LimitationType::ACCESS_DENIED) {
                    continue;
                }
            }

            /**
             * Loop over all policies.
             *
             * These are already filtered by hasAccess and given hasAccess did not return boolean
             * there must be some, so only return true if one of them says yes.
             *
             * @var \eZ\Publish\API\Repository\Values\User\Policy
             */
            foreach ($permissionSet['policies'] as $policy) {
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
                    $accessVote = $type->evaluate($limitation, $currentUserRef, $object, $targets);
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
                        break; // Break to next policy, all limitations must pass
                    }
                }
                if ($limitationsPass) {
                    return true;
                }
            }
        }

        return false; // None of the limitation sets wanted to let you in, sorry!
    }

    /**
     * @internal For internal use only, do not depend on this method.
     *
     * Allows API execution to be performed with full access sand-boxed.
     *
     * The closure sandbox will do a catch all on exceptions and rethrow after
     * re-setting the sudo flag.
     *
     * Example use:
     *     $location = $repository->sudo(
     *         function ( Repository $repo ) use ( $locationId )
     *         {
     *             return $repo->getLocationService()->loadLocation( $locationId )
     *         }
     *     );
     *
     *
     * @param \Closure $callback
     * @param \eZ\Publish\API\Repository\Repository $outerRepository
     *
     * @throws \RuntimeException Thrown on recursive sudo() use.
     * @throws \Exception Re throws exceptions thrown inside $callback
     *
     * @return mixed
     */
    public function sudo(Closure $callback, RepositoryInterface $outerRepository)
    {
        ++$this->sudoNestingLevel;
        try {
            $returnValue = $callback($outerRepository);
        } catch (Exception $e) {
            --$this->sudoNestingLevel;
            throw $e;
        }

        --$this->sudoNestingLevel;

        return $returnValue;
    }
}
