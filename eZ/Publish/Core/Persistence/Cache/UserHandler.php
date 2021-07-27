<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\SPI\Persistence\User\UserTokenUpdateStruct;
use eZ\Publish\SPI\Persistence\User\Handler as UserHandlerInterface;
use eZ\Publish\SPI\Persistence\User;
use eZ\Publish\SPI\Persistence\User\Role;
use eZ\Publish\SPI\Persistence\User\RoleAssignment;
use eZ\Publish\SPI\Persistence\User\RoleCreateStruct;
use eZ\Publish\SPI\Persistence\User\RoleUpdateStruct;
use eZ\Publish\SPI\Persistence\User\Policy;

/**
 * Cache handler for user module.
 */
class UserHandler extends AbstractInMemoryPersistenceHandler implements UserHandlerInterface
{
    /** @var callable */
    private $getUserTags;

    /** @var callable */
    private $getUserKeys;

    /** @var callable */
    private $getRoleTags;

    /** @var callable */
    private $getRoleKeys;

    /** @var callable */
    private $getRoleAssignmentTags;

    /** @var callable */
    private $getRoleAssignmentKeys;

    /**
     * Set callback functions for use in cache retrival.
     */
    public function init(): void
    {
        $this->getUserTags = static function (User $user) {
            return [
                TagIdentifiers::CONTENT . '-' . $user->id,
                TagIdentifiers::CONTENT . '-' . $user->id,
            ];
        };
        $this->getUserKeys = function (User $user) {
            return [
                TagIdentifiers::PREFIX . TagIdentifiers::CONTENT . '-' . $user->id,
                TagIdentifiers::PREFIX . TagIdentifiers::CONTENT . '-' . $this->escapeForCacheKey($user->login) . TagIdentifiers::BY_LOGIN_SUFFIX,
                //'ez-user-' . $hash . '-by-account-key',
            ];
        };
        $this->getRoleTags = static function (Role $role) {
            return [TagIdentifiers::ROLE . '-' . $role->id];
        };
        $this->getRoleKeys = function (Role $role) {
            return [
                TagIdentifiers::PREFIX . TagIdentifiers::CONTENT . '-' . $role->id,
                TagIdentifiers::PREFIX . TagIdentifiers::CONTENT . '-' . $this->escapeForCacheKey($role->identifier) . TagIdentifiers::BY_IDENTIFIER_SUFFIX,
            ];
        };
        $this->getRoleAssignmentTags = static function (RoleAssignment $roleAssignment) {
            return [
                TagIdentifiers::ROLE_ASSIGNMENT . '-' . $roleAssignment->id,
                TagIdentifiers::ROLE_ASSIGNMENT_GROUP_LIST . '-' . $roleAssignment->contentId,
                TagIdentifiers::ROLE_ASSIGNMENT_ROLE_LIST . '-' . $roleAssignment->roleId,
            ];
        };
        $this->getRoleAssignmentKeys = static function (RoleAssignment $roleAssignment) {
            return [
                TagIdentifiers::PREFIX . TagIdentifiers::ROLE_ASSIGNMENT . '-' . $roleAssignment->id,
            ];
        };
    }

    /**
     * {@inheritdoc}
     */
    public function create(User $user)
    {
        $this->logger->logCall(__METHOD__, ['struct' => $user]);
        $return = $this->persistenceHandler->userHandler()->create($user);

        // Clear corresponding content cache as creation of the User changes it's external data
        $this->cache->invalidateTags([TagIdentifiers::CONTENT . '-' . $user->id]);
        $this->cache->deleteItems([
            TagIdentifiers::PREFIX . TagIdentifiers::USER . '-' . $user->id,
            TagIdentifiers::PREFIX . TagIdentifiers::USER . '-' . $this->escapeForCacheKey($user->login) . TagIdentifiers::BY_LOGIN_SUFFIX,
            TagIdentifiers::PREFIX . TagIdentifiers::USER . '-' . $this->escapeForCacheKey($user->email) . TagIdentifiers::BY_EMAIL_SUFFIX,
        ]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function load($userId)
    {
        return $this->getCacheValue(
            $userId,
            TagIdentifiers::PREFIX . TagIdentifiers::USER . '-',
            function ($userId) {
                return $this->persistenceHandler->userHandler()->load($userId);
            },
            $this->getUserTags,
            $this->getUserKeys
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadByLogin($login)
    {
        return $this->getCacheValue(
            $this->escapeForCacheKey($login),
            TagIdentifiers::PREFIX . TagIdentifiers::USER . '-',
            function () use ($login) {
                return $this->persistenceHandler->userHandler()->loadByLogin($login);
            },
            $this->getUserTags,
            $this->getUserKeys,
            TagIdentifiers::BY_LOGIN_SUFFIX
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadByEmail($email)
    {
        // As load by email can return several items we threat it like a list here.
        return $this->getListCacheValue(
            TagIdentifiers::PREFIX . TagIdentifiers::USER . '-' . $this->escapeForCacheKey($email) . TagIdentifiers::BY_EMAIL_SUFFIX,
            function () use ($email) {
                return $this->persistenceHandler->userHandler()->loadByEmail($email);
            },
            $this->getUserTags,
            $this->getUserKeys
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByToken($hash)
    {
        $getUserKeysFn = $this->getUserKeys;
        $getUserTagsFn = $this->getUserTags;

        return $this->getCacheValue(
            $hash,
            TagIdentifiers::PREFIX . TagIdentifiers::USER . '-',
            function ($hash) {
                return $this->persistenceHandler->userHandler()->loadUserByToken($hash);
            },
            static function (User $user) use ($getUserTagsFn) {
                $tags = $getUserTagsFn($user);
                // See updateUserToken()
                $tags[] = TagIdentifiers::USER . '-' . $user->id . TagIdentifiers::ACCOUNT_KEY_SUFFIX;

                return $tags;
            },
            static function (User $user) use ($hash, $getUserKeysFn) {
                $keys = $getUserKeysFn($user);
                $keys[] = TagIdentifiers::PREFIX . TagIdentifiers::USER . '-' . $hash . TagIdentifiers::BY_ACCOUNT_KEY_SUFFIX;

                return $keys;
            },
            TagIdentifiers::BY_ACCOUNT_KEY_SUFFIX
        );
    }

    /**
     * {@inheritdoc}
     */
    public function update(User $user)
    {
        $this->logger->logCall(__METHOD__, ['struct' => $user]);
        $return = $this->persistenceHandler->userHandler()->update($user);

        // Clear corresponding content cache as update of the User changes it's external data
        $this->cache->invalidateTags([
            TagIdentifiers::CONTENT . '-' . $user->id,
            TagIdentifiers::USER . '-' . $user->id,
        ]);

        // Clear especially by email key as it might already be cached and this might represent change to email
        $this->cache->deleteItems([
            TagIdentifiers::PREFIX . TagIdentifiers::USER . '-' . $this->escapeForCacheKey($user->email) . TagIdentifiers::BY_EMAIL_SUFFIX,
        ]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function updateUserToken(UserTokenUpdateStruct $userTokenUpdateStruct)
    {
        $this->logger->logCall(__METHOD__, ['struct' => $userTokenUpdateStruct]);
        $return = $this->persistenceHandler->userHandler()->updateUserToken($userTokenUpdateStruct);

        // As we 1. don't know original hash, and 2. hash is not guaranteed to be unique, we do it like this for now
        $this->cache->invalidateTags([TagIdentifiers::USER . '-' . $userTokenUpdateStruct->userId . TagIdentifiers::ACCOUNT_KEY_SUFFIX]);
        $this->cache->deleteItems([TagIdentifiers::PREFIX . TagIdentifiers::USER . '-' . $userTokenUpdateStruct->hashKey . TagIdentifiers::BY_ACCOUNT_KEY_SUFFIX]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function expireUserToken($hash)
    {
        $this->logger->logCall(__METHOD__, ['hash' => $hash]);
        $return = $this->persistenceHandler->userHandler()->expireUserToken($hash);
        $this->cache->deleteItems([TagIdentifiers::PREFIX . TagIdentifiers::USER . '-' . $hash . TagIdentifiers::BY_ACCOUNT_KEY_SUFFIX]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($userId)
    {
        $this->logger->logCall(__METHOD__, ['user' => $userId]);
        $return = $this->persistenceHandler->userHandler()->delete($userId);

        // user id == content id == group id
        $this->cache->invalidateTags([
            TagIdentifiers::CONTENT . '-' . $userId,
            TagIdentifiers::USER . '-' . $userId,
        ]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function createRole(RoleCreateStruct $createStruct)
    {
        $this->logger->logCall(__METHOD__, ['struct' => $createStruct]);

        return $this->persistenceHandler->userHandler()->createRole($createStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function createRoleDraft($roleId)
    {
        $this->logger->logCall(__METHOD__, ['role' => $roleId]);

        return $this->persistenceHandler->userHandler()->createRoleDraft($roleId);
    }

    /**
     * {@inheritdoc}
     */
    public function loadRole($roleId, $status = Role::STATUS_DEFINED)
    {
        if ($status !== Role::STATUS_DEFINED) {
            $this->logger->logCall(__METHOD__, ['role' => $roleId]);

            return $this->persistenceHandler->userHandler()->loadRole($roleId, $status);
        }

        return $this->getCacheValue(
            $roleId,
            TagIdentifiers::PREFIX . TagIdentifiers::ROLE . '-',
            function ($roleId) {
                return $this->persistenceHandler->userHandler()->loadRole($roleId);
            },
            $this->getRoleTags,
            $this->getRoleKeys
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadRoleByIdentifier($identifier, $status = Role::STATUS_DEFINED)
    {
        if ($status !== Role::STATUS_DEFINED) {
            $this->logger->logCall(__METHOD__, ['role' => $identifier]);

            return $this->persistenceHandler->userHandler()->loadRoleByIdentifier($identifier, $status);
        }

        return $this->getCacheValue(
            $this->escapeForCacheKey($identifier),
            TagIdentifiers::PREFIX . TagIdentifiers::ROLE . '-',
            function () use ($identifier) {
                return $this->persistenceHandler->userHandler()->loadRoleByIdentifier($identifier);
            },
            $this->getRoleTags,
            $this->getRoleKeys,
            TagIdentifiers::BY_IDENTIFIER_SUFFIX
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadRoleDraftByRoleId($roleId)
    {
        $this->logger->logCall(__METHOD__, ['role' => $roleId]);

        return $this->persistenceHandler->userHandler()->loadRoleDraftByRoleId($roleId);
    }

    /**
     * {@inheritdoc}
     */
    public function loadRoles()
    {
        $this->logger->logCall(__METHOD__);

        return $this->persistenceHandler->userHandler()->loadRoles();
    }

    /**
     * {@inheritdoc}
     */
    public function loadRoleAssignment($roleAssignmentId)
    {
        return $this->getCacheValue(
            $roleAssignmentId,
            TagIdentifiers::PREFIX . TagIdentifiers::ROLE_ASSIGNMENT . '-',
            function ($roleAssignmentId) {
                return $this->persistenceHandler->userHandler()->loadRoleAssignment($roleAssignmentId);
            },
            $this->getRoleAssignmentTags,
            $this->getRoleAssignmentKeys
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadRoleAssignmentsByRoleId($roleId)
    {
        return $this->getListCacheValue(
            TagIdentifiers::PREFIX . TagIdentifiers::ROLE_ASSIGNMENT . '-' . $roleId . TagIdentifiers::BY_ROLE_SUFFIX,
            function () use ($roleId) {
                return $this->persistenceHandler->userHandler()->loadRoleAssignmentsByRoleId($roleId);
            },
            $this->getRoleAssignmentTags,
            $this->getRoleAssignmentKeys,
            /* Role update (policies) changes role assignment id, also need list tag in case of empty result */
            static function () use ($roleId) {
                return [
                    TagIdentifiers::ROLE_ASSIGNMENT_ROLE_LIST . '-' . $roleId,
                    TagIdentifiers::ROLE . '-' . $roleId,
                ];
            },
            [$roleId]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadRoleAssignmentsByGroupId($groupId, $inherit = false)
    {
        $innerHandler = $this->persistenceHandler;
        if ($inherit) {
            $key = TagIdentifiers::PREFIX . TagIdentifiers::ROLE_ASSIGNMENT . '-' . $groupId . TagIdentifiers::BY_GROUP_INHERITED_SUFFIX;
        } else {
            $key = TagIdentifiers::PREFIX . TagIdentifiers::ROLE_ASSIGNMENT . '-' . $groupId . TagIdentifiers::BY_GROUP_SUFFIX;
        }

        return $this->getListCacheValue(
            $key,
            function () use ($groupId, $inherit) {
                return $this->persistenceHandler->userHandler()->loadRoleAssignmentsByGroupId($groupId, $inherit);
            },
            $this->getRoleAssignmentTags,
            $this->getRoleAssignmentKeys,
            static function () use ($groupId, $innerHandler) {
                // Tag needed for empty results, if not empty will alse be added by getRoleAssignmentTags().
                $cacheTags = [TagIdentifiers::ROLE_ASSIGNMENT_GROUP_LIST . '-' . $groupId];
                // To make sure tree operations affecting this can clear the permission cache
                $locations = $innerHandler->locationHandler()->loadLocationsByContent($groupId);
                foreach ($locations as $location) {
                    foreach (explode('/', trim($location->pathString, '/')) as $pathId) {
                        $cacheTags[] = TagIdentifiers::LOCATION_PATH . '-' . $pathId;
                    }
                }

                return $cacheTags;
            },
            [$groupId, $inherit]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function updateRole(RoleUpdateStruct $struct)
    {
        $this->logger->logCall(__METHOD__, ['struct' => $struct]);
        $this->persistenceHandler->userHandler()->updateRole($struct);

        $this->cache->invalidateTags([TagIdentifiers::ROLE . '-' . $struct->id]);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRole($roleId, $status = Role::STATUS_DEFINED)
    {
        $this->logger->logCall(__METHOD__, ['role' => $roleId]);
        $return = $this->persistenceHandler->userHandler()->deleteRole($roleId, $status);

        if ($status === Role::STATUS_DEFINED) {
            $this->cache->invalidateTags([
                TagIdentifiers::ROLE . '-' . $roleId,
                TagIdentifiers::ROLE_ASSIGNMENT_ROLE_LIST . '-' . $roleId,
            ]);
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function publishRoleDraft($roleDraftId)
    {
        $this->logger->logCall(__METHOD__, ['role' => $roleDraftId]);
        $userHandler = $this->persistenceHandler->userHandler();
        $roleDraft = $userHandler->loadRole($roleDraftId, Role::STATUS_DRAFT);
        $return = $userHandler->publishRoleDraft($roleDraftId);

        // If there was a original role for the draft, then we clean cache for it
        if ($roleDraft->originalId > -1) {
            $this->cache->invalidateTags([TagIdentifiers::ROLE . '-' . $roleDraft->originalId]);
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function addPolicyByRoleDraft($roleId, Policy $policy)
    {
        $this->logger->logCall(__METHOD__, ['role' => $roleId, 'struct' => $policy]);

        return $this->persistenceHandler->userHandler()->addPolicyByRoleDraft($roleId, $policy);
    }

    /**
     * {@inheritdoc}
     */
    public function addPolicy($roleId, Policy $policy)
    {
        $this->logger->logCall(__METHOD__, ['role' => $roleId, 'struct' => $policy]);
        $return = $this->persistenceHandler->userHandler()->addPolicy($roleId, $policy);

        $this->cache->invalidateTags([TagIdentifiers::ROLE . '-' . $roleId]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function updatePolicy(Policy $policy)
    {
        $this->logger->logCall(__METHOD__, ['struct' => $policy]);
        $return = $this->persistenceHandler->userHandler()->updatePolicy($policy);

        $this->cache->invalidateTags([
            TagIdentifiers::POLICY . '-' . $policy->id,
            TagIdentifiers::ROLE . '-' . $policy->roleId,
        ]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function deletePolicy($policyId, $roleId)
    {
        $this->logger->logCall(__METHOD__, ['policy' => $policyId]);
        $this->persistenceHandler->userHandler()->deletePolicy($policyId, $roleId);

        $this->cache->invalidateTags([
            TagIdentifiers::POLICY . '-' . $policyId,
            TagIdentifiers::ROLE . '-' . $roleId,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function loadPoliciesByUserId($userId)
    {
        $this->logger->logCall(__METHOD__, ['user' => $userId]);

        return $this->persistenceHandler->userHandler()->loadPoliciesByUserId($userId);
    }

    /**
     * {@inheritdoc}
     */
    public function assignRole($contentId, $roleId, array $limitation = null)
    {
        $this->logger->logCall(__METHOD__, ['group' => $contentId, 'role' => $roleId, 'limitation' => $limitation]);
        $return = $this->persistenceHandler->userHandler()->assignRole($contentId, $roleId, $limitation);

        $tags = [
            TagIdentifiers::ROLE_ASSIGNMENT_GROUP_LIST . '-' . $contentId,
            TagIdentifiers::ROLE_ASSIGNMENT_ROLE_LIST . '-' . $roleId,
        ];

        $locations = $this->persistenceHandler->locationHandler()->loadLocationsByContent($contentId);
        foreach ($locations as $location) {
            $tags[] = TagIdentifiers::LOCATION_PATH . '-' . $location->id;
        }

        $this->cache->invalidateTags($tags);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function unassignRole($contentId, $roleId)
    {
        $this->logger->logCall(__METHOD__, ['group' => $contentId, 'role' => $roleId]);
        $return = $this->persistenceHandler->userHandler()->unassignRole($contentId, $roleId);

        $this->cache->invalidateTags([
            TagIdentifiers::ROLE_ASSIGNMENT_GROUP_LIST . '-' . $contentId,
            TagIdentifiers::ROLE_ASSIGNMENT_ROLE_LIST . '-' . $roleId,
        ]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function removeRoleAssignment($roleAssignmentId)
    {
        $this->logger->logCall(__METHOD__, ['assignment' => $roleAssignmentId]);
        $return = $this->persistenceHandler->userHandler()->removeRoleAssignment($roleAssignmentId);

        $this->cache->invalidateTags([TagIdentifiers::ROLE_ASSIGNMENT . '-' . $roleAssignmentId]);

        return $return;
    }
}
