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
    private const CONTENT_IDENTIFIER = 'content';
    private const USER_IDENTIFIER = 'user';
    private const USER_WITH_BY_LOGIN_SUFFIX_IDENTIFIER = 'user_with_by_login_suffix';
    private const ROLE_IDENTIFIER = 'role';
    private const POLICY_IDENTIFIER = 'policy';
    private const ROLE_WITH_BY_ID_SUFFIX_IDENTIFIER = 'role_with_by_id_suffix';
    private const ROLE_ASSIGNMENT_IDENTIFIER = 'role_assignment';
    private const ROLE_ASSIGNMENT_GROUP_LIST_IDENTIFIER = 'role_assignment_group_list';
    private const ROLE_ASSIGNMENT_ROLE_LIST_IDENTIFIER = 'role_assignment_role_list';
    private const USER_WITH_BY_EMAIL_SUFFIX_IDENTIFIER = 'user_with_by_email_suffix';
    private const BY_LOGIN_SUFFIX = 'by_login_suffix';
    private const BY_IDENTIFIER_SUFFIX = 'by_identifier_suffix';
    private const LOCATION_PATH_IDENTIFIER = 'location_path';
    private const ROLE_ASSIGNMENT_WITH_BY_ROLE_SUFFIX_IDENTIFIER = 'role_assignment_with_by_role_suffix';
    private const ROLE_ASSIGNMENT_WITH_BY_GROUP_INHERITED_SUFFIX_IDENTIFIER = 'role_assignment_with_by_group_inherited_suffix';
    private const ROLE_ASSIGNMENT_WITH_BY_GROUP_SUFFIX_IDENTIFIER = 'role_assignment_with_by_group_suffix';
    private const USER_WITH_ACCOUNT_KEY_SUFFIX_IDENTIFIER = 'user_with_account_key_suffix';
    private const USER_WITH_BY_ACCOUNT_KEY_SUFFIX_IDENTIFIER = 'user_with_by_account_key_suffix';
    private const BY_ACCOUNT_KEY_SUFFIX = 'by_account_key_suffix';

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
        $this->getUserTags = function (User $user) {
            return [
                $this->cacheIdentifierGenerator->generateTag(self::CONTENT_IDENTIFIER, [$user->id]),
                $this->cacheIdentifierGenerator->generateTag(self::USER_IDENTIFIER, [$user->id]),
            ];
        };
        $this->getUserKeys = function (User $user) {
            return [
                $this->cacheIdentifierGenerator->generateKey(self::USER_IDENTIFIER, [$user->id], true),
                $this->cacheIdentifierGenerator->generateKey(
                    self::USER_WITH_BY_LOGIN_SUFFIX_IDENTIFIER,
                    [$this->escapeForCacheKey($user->login)],
                    true
                ),
                //'ez-user-' . $hash . '-by-account-key',
            ];
        };
        $this->getRoleTags = function (Role $role) {
            return [
                $this->cacheIdentifierGenerator->generateTag(self::ROLE_IDENTIFIER, [$role->id]),
            ];
        };
        $this->getRoleKeys = function (Role $role) {
            return [
                $this->cacheIdentifierGenerator->generateKey(self::ROLE_IDENTIFIER, [$role->id]),
                $this->cacheIdentifierGenerator->generateKey(
                    self::ROLE_WITH_BY_ID_SUFFIX_IDENTIFIER,
                    [$this->escapeForCacheKey($role->identifier)],
                    true
                ),
            ];
        };
        $this->getRoleAssignmentTags = function (RoleAssignment $roleAssignment) {
            return [
                $this->cacheIdentifierGenerator->generateTag(self::ROLE_ASSIGNMENT_IDENTIFIER, [$roleAssignment->id]),
                $this->cacheIdentifierGenerator->generateTag(self::ROLE_ASSIGNMENT_GROUP_LIST_IDENTIFIER, [$roleAssignment->contentId]),
                $this->cacheIdentifierGenerator->generateTag(self::ROLE_ASSIGNMENT_ROLE_LIST_IDENTIFIER, [$roleAssignment->roleId]),
            ];
        };
        $this->getRoleAssignmentKeys = function (RoleAssignment $roleAssignment) {
            return [
                $this->cacheIdentifierGenerator->generateKey(self::ROLE_ASSIGNMENT_IDENTIFIER, [$roleAssignment->id], true),
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
        $this->cache->invalidateTags([
            $this->cacheIdentifierGenerator->generateTag(self::CONTENT_IDENTIFIER, [$user->id]),
        ]);

        $this->cache->deleteItems([
            $this->cacheIdentifierGenerator->generateKey(self::USER_IDENTIFIER, [$user->id], true),
            $this->cacheIdentifierGenerator->generateKey(self::USER_WITH_BY_LOGIN_SUFFIX_IDENTIFIER, [$this->escapeForCacheKey($user->login)], true),
            $this->cacheIdentifierGenerator->generateKey(self::USER_WITH_BY_EMAIL_SUFFIX_IDENTIFIER, [$this->escapeForCacheKey($user->email)], true),
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
            $this->cacheIdentifierGenerator->generateKey(self::USER_IDENTIFIER, [], true) . '-',
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
            $this->cacheIdentifierGenerator->generateKey(self::USER_IDENTIFIER, [], true) . '-',
            function () use ($login) {
                return $this->persistenceHandler->userHandler()->loadByLogin($login);
            },
            $this->getUserTags,
            $this->getUserKeys,
            '-' . $this->cacheIdentifierGenerator->generateKey(self::BY_LOGIN_SUFFIX)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadByEmail($email)
    {
        // As load by email can return several items we threat it like a list here.
        return $this->getListCacheValue(
            $this->cacheIdentifierGenerator->generateKey(self::USER_WITH_BY_EMAIL_SUFFIX_IDENTIFIER, [$this->escapeForCacheKey($email)], true),
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
            $this->cacheIdentifierGenerator->generateKey(self::USER_IDENTIFIER, [], true) . '-',
            function ($hash) {
                return $this->persistenceHandler->userHandler()->loadUserByToken($hash);
            },
            function (User $user) use ($getUserTagsFn) {
                $tags = $getUserTagsFn($user);
                // See updateUserToken()
                $tags[] = $this->cacheIdentifierGenerator->generateTag(self::USER_WITH_ACCOUNT_KEY_SUFFIX_IDENTIFIER, [$user->id]);

                return $tags;
            },
            function (User $user) use ($hash, $getUserKeysFn) {
                $keys = $getUserKeysFn($user);
                $keys[] = $this->cacheIdentifierGenerator->generateKey(self::USER_WITH_BY_ACCOUNT_KEY_SUFFIX_IDENTIFIER, [$hash], true);

                return $keys;
            },
            $this->cacheIdentifierGenerator->generateKey(self::BY_ACCOUNT_KEY_SUFFIX)
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
            $this->cacheIdentifierGenerator->generateTag(self::CONTENT_IDENTIFIER, [$user->id]),
            $this->cacheIdentifierGenerator->generateTag(self::USER_IDENTIFIER, [$user->id]),
        ]);

        // Clear especially by email key as it might already be cached and this might represent change to email
        $this->cache->deleteItems([
            $this->cacheIdentifierGenerator->generateKey(
                self::USER_WITH_BY_EMAIL_SUFFIX_IDENTIFIER,
                [$this->escapeForCacheKey($user->email)],
                true
            ),
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
        $this->cache->invalidateTags([
            $this->cacheIdentifierGenerator->generateTag(self::USER_WITH_ACCOUNT_KEY_SUFFIX_IDENTIFIER, [$userTokenUpdateStruct->userId]),
        ]);

        $this->cache->deleteItems([
            $this->cacheIdentifierGenerator->generateKey(
                self::USER_WITH_BY_ACCOUNT_KEY_SUFFIX_IDENTIFIER,
                [$userTokenUpdateStruct->hashKey],
                true
            ),
        ]);

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function expireUserToken($hash)
    {
        $this->logger->logCall(__METHOD__, ['hash' => $hash]);
        $return = $this->persistenceHandler->userHandler()->expireUserToken($hash);

        $this->cache->deleteItems([
            $this->cacheIdentifierGenerator->generateKey(self::USER_WITH_BY_ACCOUNT_KEY_SUFFIX_IDENTIFIER, [$hash], true),
        ]);

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
            $this->cacheIdentifierGenerator->generateTag(self::CONTENT_IDENTIFIER, [$userId]),
            $this->cacheIdentifierGenerator->generateTag(self::USER_IDENTIFIER, [$userId]),
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
            $this->cacheIdentifierGenerator->generateKey(self::ROLE_IDENTIFIER, [], true) . '-',
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
            $this->cacheIdentifierGenerator->generateKey(self::ROLE_IDENTIFIER, [], true) . '-',
            function () use ($identifier) {
                return $this->persistenceHandler->userHandler()->loadRoleByIdentifier($identifier);
            },
            $this->getRoleTags,
            $this->getRoleKeys,
            $this->cacheIdentifierGenerator->generateKey(self::BY_IDENTIFIER_SUFFIX)
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
            $this->cacheIdentifierGenerator->generateKey(self::ROLE_ASSIGNMENT_IDENTIFIER, [], true) . '-',
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
            $this->cacheIdentifierGenerator->generateKey(self::ROLE_ASSIGNMENT_WITH_BY_ROLE_SUFFIX_IDENTIFIER, [$roleId], true),
            function () use ($roleId) {
                return $this->persistenceHandler->userHandler()->loadRoleAssignmentsByRoleId($roleId);
            },
            $this->getRoleAssignmentTags,
            $this->getRoleAssignmentKeys,
            /* Role update (policies) changes role assignment id, also need list tag in case of empty result */
            function () use ($roleId) {
                return [
                    $this->cacheIdentifierGenerator->generateTag(self::ROLE_ASSIGNMENT_ROLE_LIST_IDENTIFIER, [$roleId]),
                    $this->cacheIdentifierGenerator->generateTag(self::ROLE_IDENTIFIER, [$roleId]),
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
        $cacheIdentifierGenerator = $this->cacheIdentifierGenerator;

        if ($inherit) {
            $key = $cacheIdentifierGenerator->generateKey(
                self::ROLE_ASSIGNMENT_WITH_BY_GROUP_INHERITED_SUFFIX_IDENTIFIER,
                [$groupId],
                true
            );
        } else {
            $key = $cacheIdentifierGenerator->generateKey(
                self::ROLE_ASSIGNMENT_WITH_BY_GROUP_SUFFIX_IDENTIFIER,
                [$groupId],
                true
            );
        }

        return $this->getListCacheValue(
            $key,
            function () use ($groupId, $inherit) {
                return $this->persistenceHandler->userHandler()->loadRoleAssignmentsByGroupId($groupId, $inherit);
            },
            $this->getRoleAssignmentTags,
            $this->getRoleAssignmentKeys,
            static function () use ($groupId, $innerHandler, $cacheIdentifierGenerator) {
                // Tag needed for empty results, if not empty will alse be added by getRoleAssignmentTags().
                $cacheTags = [
                    $cacheIdentifierGenerator->generateTag(self::ROLE_ASSIGNMENT_GROUP_LIST_IDENTIFIER, [$groupId]),
                ];
                // To make sure tree operations affecting this can clear the permission cache
                $locations = $innerHandler->locationHandler()->loadLocationsByContent($groupId);
                foreach ($locations as $location) {
                    foreach (explode('/', trim($location->pathString, '/')) as $pathId) {
                        $cacheTags[] = $cacheIdentifierGenerator->generateTag(self::LOCATION_PATH_IDENTIFIER, [$pathId]);
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

        $this->cache->invalidateTags([
            $this->cacheIdentifierGenerator->generateTag(self::ROLE_IDENTIFIER, [$struct->id]),
        ]);
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
                $this->cacheIdentifierGenerator->generateTag(self::ROLE_IDENTIFIER, [$roleId]),
                $this->cacheIdentifierGenerator->generateTag(self::ROLE_ASSIGNMENT_ROLE_LIST_IDENTIFIER, [$roleId]),
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
            $this->cache->invalidateTags([
                $this->cacheIdentifierGenerator->generateTag(self::ROLE_IDENTIFIER, [$roleDraft->originalId]),
            ]);
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

        $this->cache->invalidateTags([
            $this->cacheIdentifierGenerator->generateTag(self::ROLE_IDENTIFIER, [$roleId]),
        ]);

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
            $this->cacheIdentifierGenerator->generateTag(self::POLICY_IDENTIFIER, [$policy->id]),
            $this->cacheIdentifierGenerator->generateTag(self::ROLE_IDENTIFIER, [$policy->roleId]),
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
            $this->cacheIdentifierGenerator->generateTag(self::POLICY_IDENTIFIER, [$policyId]),
            $this->cacheIdentifierGenerator->generateTag(self::ROLE_IDENTIFIER, [$roleId]),
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
            $this->cacheIdentifierGenerator->generateTag(self::ROLE_ASSIGNMENT_GROUP_LIST_IDENTIFIER, [$contentId]),
            $this->cacheIdentifierGenerator->generateTag(self::ROLE_ASSIGNMENT_ROLE_LIST_IDENTIFIER, [$roleId]),
        ];

        $locations = $this->persistenceHandler->locationHandler()->loadLocationsByContent($contentId);
        foreach ($locations as $location) {
            $tags[] = $this->cacheIdentifierGenerator->generateTag(self::LOCATION_PATH_IDENTIFIER, [$location->id]);
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
            $this->cacheIdentifierGenerator->generateTag(self::ROLE_ASSIGNMENT_GROUP_LIST_IDENTIFIER, [$contentId]),
            $this->cacheIdentifierGenerator->generateTag(self::ROLE_ASSIGNMENT_ROLE_LIST_IDENTIFIER, [$roleId]),
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

        $this->cache->invalidateTags([
            $this->cacheIdentifierGenerator->generateTag(self::ROLE_ASSIGNMENT_IDENTIFIER, [$roleAssignmentId]),
        ]);

        return $return;
    }
}
