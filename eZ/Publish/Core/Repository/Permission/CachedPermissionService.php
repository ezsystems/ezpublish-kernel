<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Permission;

use eZ\Publish\API\Repository\PermissionResolver as APIPermissionResolver;
use eZ\Publish\API\Repository\PermissionCriterionResolver as APIPermissionCriterionResolver;
use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\API\Repository\Values\User\LookupLimitationResult;
use eZ\Publish\API\Repository\Values\User\UserReference;
use eZ\Publish\API\Repository\Values\ValueObject;
use Exception;

/**
 * Cache implementation of PermissionResolver and PermissionCriterionResolver interface.
 *
 * Implements both interfaces as the cached permission criterion lookup needs to be
 * expired when a different user is set as current users in the system.
 *
 * Cache is only done for content/read policy, as that is the one needed by search service.
 *
 * The logic here uses a cache TTL of a few seconds, as this is in-memory cache we are not
 * able to know if any other concurrent user might be changing permissions.
 */
class CachedPermissionService implements APIPermissionResolver, APIPermissionCriterionResolver
{
    /** @var \eZ\Publish\API\Repository\PermissionResolver */
    private $permissionResolver;

    /** @var \eZ\Publish\API\Repository\PermissionCriterionResolver */
    private $permissionCriterionResolver;

    /** @var int */
    private $cacheTTL;

    /**
     * Counter for the current sudo nesting level {@see sudo()}.
     *
     * @var int
     */
    private $sudoNestingLevel = 0;

    /**
     * Cached value for current user's getCriterion() result.
     *
     * Value is null if not yet set or cleared.
     *
     * @var bool|\eZ\Publish\API\Repository\Values\Content\Query\Criterion
     */
    private $permissionCriterion;

    /**
     * Cache time stamp.
     *
     * @var int
     */
    private $permissionCriterionTs;

    /**
     * CachedPermissionService constructor.
     *
     * @param \eZ\Publish\API\Repository\PermissionResolver $permissionResolver
     * @param \eZ\Publish\API\Repository\PermissionCriterionResolver $permissionCriterionResolver
     * @param int $cacheTTL By default set to 5 seconds, should be low to avoid to many permission exceptions on long running requests / processes (even if tolerant search service should handle that)
     */
    public function __construct(
        APIPermissionResolver $permissionResolver,
        APIPermissionCriterionResolver $permissionCriterionResolver,
        $cacheTTL = 5
    ) {
        $this->permissionResolver = $permissionResolver;
        $this->permissionCriterionResolver = $permissionCriterionResolver;
        $this->cacheTTL = $cacheTTL;
    }

    public function getCurrentUserReference()
    {
        return $this->permissionResolver->getCurrentUserReference();
    }

    public function setCurrentUserReference(UserReference $userReference)
    {
        $this->permissionCriterion = null;

        return $this->permissionResolver->setCurrentUserReference($userReference);
    }

    public function hasAccess($module, $function, UserReference $userReference = null)
    {
        return $this->permissionResolver->hasAccess($module, $function, $userReference);
    }

    public function canUser($module, $function, ValueObject $object, array $targets = [])
    {
        return $this->permissionResolver->canUser($module, $function, $object, $targets);
    }

    /**
     * {@inheritdoc}
     */
    public function lookupLimitations(
        string $module,
        string $function,
        ValueObject $object,
        array $targets = [],
        array $limitations = []
    ): LookupLimitationResult {
        return $this->permissionResolver->lookupLimitations($module, $function, $object, $targets, $limitations);
    }

    public function getPermissionsCriterion($module = 'content', $function = 'read', ?array $targets = null)
    {
        // We only cache content/read lookup as those are the once frequently done, and it's only one we can safely
        // do that won't harm the system if it becomes stale (but user might experience permissions exceptions if it do)
        if ($module !== 'content' || $function !== 'read' || $this->sudoNestingLevel > 0) {
            return $this->permissionCriterionResolver->getPermissionsCriterion($module, $function, $targets);
        }

        if ($this->permissionCriterion !== null) {
            // If we are still within the cache TTL, then return the cached value
            if ((time() - $this->permissionCriterionTs) < $this->cacheTTL) {
                return $this->permissionCriterion;
            }
        }

        $this->permissionCriterionTs = time();
        $this->permissionCriterion = $this->permissionCriterionResolver->getPermissionsCriterion($module, $function, $targets);

        return $this->permissionCriterion;
    }

    /**
     * @internal For internal use only, do not depend on this method.
     */
    public function sudo(callable $callback, RepositoryInterface $outerRepository)
    {
        ++$this->sudoNestingLevel;
        try {
            $returnValue = $this->permissionResolver->sudo($callback, $outerRepository);
        } catch (Exception $e) {
            --$this->sudoNestingLevel;
            throw $e;
        }
        --$this->sudoNestingLevel;

        return $returnValue;
    }
}
