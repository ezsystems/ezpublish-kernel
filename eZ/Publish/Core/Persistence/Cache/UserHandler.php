<?php

/**
 * File containing a User Handler impl.
 *
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
            return ['content-' . $user->id, 'user-' . $user->id];
        };
        $this->getUserKeys = function (User $user) {
            return [
                'ez-user-' . $user->id,
                'ez-user-' . $this->escapeForCacheKey($user->login) . '-by-login',
                //'ez-user-' . $hash . '-by-account-key',
            ];
        };
        $this->getRoleTags = static function (Role $role) {
            return ['role-' . $role->id];
        };
        $this->getRoleKeys = static function (Role $role) {
            return [
                'ez-role-' . $role->id,
                'ez-role-' . $role->identifier . '-by-identifier',
            ];
        };
        $this->getRoleAssignmentTags = static function (RoleAssignment $roleAssignment) {
            return [
                'role-assignment-' . $roleAssignment->id,
                'role-assignment-group-list-' . $roleAssignment->contentId,
                'role-assignment-role-list-' . $roleAssignment->roleId,
            ];
        };
        $this->getRoleAssignmentKeys = static function (RoleAssignment $roleAssignment) {
            return [
                'ez-role-assignment-' . $roleAssignment->id,
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
        $this->cache->invalidateTags(['content-fields-' . $user->id]);
        $this->cache->deleteItems([
            'ez-user-' . $user->id,
            'ez-user-' . $this->escapeForCacheKey($user->login) . '-by-login',
            'ez-user-' . $this->escapeForCacheKey($user->email) . '-by-email',
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
            'ez-user-',
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
            'ez-user-',
            function ($escapedLogin) use ($login) {
                return $this->persistenceHandler->userHandler()->loadByLogin($login);
            },
            $this->getUserTags,
            $this->getUserKeys,
            '-by-login'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadByEmail($email)
    {
        // As load by email can return several items we threat it like a list here.
        return $this->getListCacheValue(
            'ez-user-' . $this->escapeForCacheKey($email) . '-by-email',
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
            'ez-user-',
            function ($hash) {
                return $this->persistenceHandler->userHandler()->loadUserByToken($hash);
            },
            static function (User $user) use ($getUserTagsFn) {
                $tags = $getUserTagsFn($user);
                // See updateUserToken()
                $tags[] = 'user-' . $user->id . '-account-key';

                return $tags;
            },
            static function (User $user) use ($hash, $getUserKeysFn) {
                $keys = $getUserKeysFn($user);
                $keys[] = 'ez-user-' . $hash . '-by-account-key';

                return $keys;
            },
            '-by-account-key'
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
        $this->cache->invalidateTags(['content-fields-' . $user->id, 'user-' . $user->id]);
        // Clear especially by email key as it might already be cached and this might represent change to email
        $this->cache->deleteItems(['ez-user-' . $this->escapeForCacheKey($user->email) . '-by-email']);

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
        $this->cache->invalidateTags(['user-' . $userTokenUpdateStruct->userId . '-account-key']);
        $this->cache->deleteItems(['ez-user-' . $userTokenUpdateStruct->hashKey . '-by-account-key']);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function expireUserToken($hash)
    {
        $this->logger->logCall(__METHOD__, ['hash' => $hash]);
        $return = $this->persistenceHandler->userHandler()->expireUserToken($hash);
        $this->cache->deleteItems(['ez-user-' . $hash . '-by-account-key']);

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
        $this->cache->invalidateTags(['content-fields-' . $userId, 'user-' . $userId]);

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
            'ez-role-',
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
            $identifier,
            'ez-role-',
            function ($identifier) {
                return $this->persistenceHandler->userHandler()->loadRoleByIdentifier($identifier);
            },
            $this->getRoleTags,
            $this->getRoleKeys,
            '-by-identifier'
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
            'ez-role-assignment-',
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
            "ez-role-assignment-${roleId}-by-role",
            function () use ($roleId) {
                return $this->persistenceHandler->userHandler()->loadRoleAssignmentsByRoleId($roleId);
            },
            $this->getRoleAssignmentTags,
            $this->getRoleAssignmentKeys,
            /* Role update (policies) changes role assignment id, also need list tag in case of empty result */
            static function () use ($roleId) {
                return ['role-assignment-role-list-' . $roleId, 'role-' . $roleId];
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
            $key = "ez-role-assignment-${groupId}-by-group-inherited";
        } else {
            $key = "ez-role-assignment-${groupId}-by-group";
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
                $cacheTags = ['role-assignment-group-list-' . $groupId];
                // To make sure tree operations affecting this can clear the permission cache
                $locations = $innerHandler->locationHandler()->loadLocationsByContent($groupId);
                foreach ($locations as $location) {
                    foreach (explode('/', trim($location->pathString, '/')) as $pathId) {
                        $cacheTags[] = 'location-path-' . $pathId;
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

        $this->cache->invalidateTags(['role-' . $struct->id]);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRole($roleId, $status = Role::STATUS_DEFINED)
    {
        $this->logger->logCall(__METHOD__, ['role' => $roleId]);
        $return = $this->persistenceHandler->userHandler()->deleteRole($roleId, $status);

        if ($status === Role::STATUS_DEFINED) {
            $this->cache->invalidateTags(['role-' . $roleId, 'role-assignment-role-list-' . $roleId]);
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
            $this->cache->invalidateTags(['role-' . $roleDraft->originalId]);
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

        $this->cache->invalidateTags(['role-' . $roleId]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function updatePolicy(Policy $policy)
    {
        $this->logger->logCall(__METHOD__, ['struct' => $policy]);
        $return = $this->persistenceHandler->userHandler()->updatePolicy($policy);

        $this->cache->invalidateTags(['policy-' . $policy->id, 'role-' . $policy->roleId]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function deletePolicy($policyId, $roleId)
    {
        $this->logger->logCall(__METHOD__, ['policy' => $policyId]);
        $this->persistenceHandler->userHandler()->deletePolicy($policyId, $roleId);

        $this->cache->invalidateTags(['policy-' . $policyId, 'role-' . $roleId]);
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

        $tags = ['role-assignment-group-list-' . $contentId, 'role-assignment-role-list-' . $roleId];
        $locations = $this->persistenceHandler->locationHandler()->loadLocationsByContent($contentId);
        foreach ($locations as $location) {
            $tags[] = 'location-path-' . $location->id;
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

        $this->cache->invalidateTags(['role-assignment-group-list-' . $contentId, 'role-assignment-role-list-' . $roleId]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function removeRoleAssignment($roleAssignmentId)
    {
        $this->logger->logCall(__METHOD__, ['assignment' => $roleAssignmentId]);
        $return = $this->persistenceHandler->userHandler()->removeRoleAssignment($roleAssignmentId);

        $this->cache->invalidateTags(['role-assignment-' . $roleAssignmentId]);

        return $return;
    }
}
