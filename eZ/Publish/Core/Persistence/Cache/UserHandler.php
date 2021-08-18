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
    private const CONTENT_TAG = 'content';
    private const USER_TAG = 'user';
    private const USER_WITH_BY_LOGIN_SUFFIX_TAG = 'user_with_by_login_suffix';
    private const ROLE_TAG = 'role';
    private const POLICY_TAG = 'policy';
    private const ROLE_WITH_BY_ID_SUFFIX_TAG = 'role_with_by_id_suffix';
    private const ROLE_ASSIGNMENT_TAG = 'role_assignment';
    private const ROLE_ASSIGNMENT_GROUP_LIST_TAG = 'role_assignment_group_list';
    private const ROLE_ASSIGNMENT_ROLE_LIST_TAG = 'role_assignment_role_list';
    private const USER_WITH_BY_EMAIL_SUFFIX_TAG = 'user_with_by_email_suffix';
    private const BY_LOGIN_SUFFIX = 'by_login_suffix';
    private const BY_IDENTIFIER_SUFFIX = 'by_identifier_suffix';
    private const LOCATION_PATH_TAG = 'location_path';
    private const ROLE_ASSIGNMENT_WITH_BY_ROLE_SUFFIX_TAG = 'role_assignment_with_by_role_suffix';
    private const ROLE_ASSIGNMENT_WITH_BY_GROUP_INHERITED_SUFFIX_TAG = 'role_assignment_with_by_group_inherited_suffix';
    private const ROLE_ASSIGNMENT_WITH_BY_GROUP_SUFFIX_TAG = 'role_assignment_with_by_group_suffix';
    private const USER_WITH_ACCOUNT_KEY_SUFFIX_TAG = 'user_with_account_key_suffix';
    private const USER_WITH_BY_ACCOUNT_KEY_SUFFIX_TAG = 'user_with_by_account_key_suffix';
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
        $tagGenerator = $this->tagGenerator;

        $this->getUserTags = static function (User $user) use ($tagGenerator) {
            return [
                $tagGenerator->generate(self::CONTENT_TAG, [$user->id]),
                $tagGenerator->generate(self::USER_TAG, [$user->id]),
            ];
        };
        $this->getUserKeys = function (User $user) use ($tagGenerator) {
            return [
                $tagGenerator->generate(self::USER_TAG, [$user->id], true),
                $tagGenerator->generate(
                    self::USER_WITH_BY_LOGIN_SUFFIX_TAG,
                    [$this->escapeForCacheKey($user->login)],
                    true
                ),
                //'ez-user-' . $hash . '-by-account-key',
            ];
        };
        $this->getRoleTags = static function (Role $role) use ($tagGenerator) {
            return [
                $tagGenerator->generate(self::ROLE_TAG, [$role->id]),
            ];
        };
        $this->getRoleKeys = function (Role $role) use ($tagGenerator) {
            return [
                $tagGenerator->generate(self::ROLE_TAG, [$role->id]),
                $tagGenerator->generate(
                    self::ROLE_WITH_BY_ID_SUFFIX_TAG,
                    [$this->escapeForCacheKey($role->identifier)],
                    true
                ),
            ];
        };
        $this->getRoleAssignmentTags = static function (RoleAssignment $roleAssignment) use ($tagGenerator) {
            return [
                $tagGenerator->generate(self::ROLE_ASSIGNMENT_TAG, [$roleAssignment->id]),
                $tagGenerator->generate(self::ROLE_ASSIGNMENT_GROUP_LIST_TAG, [$roleAssignment->contentId]),
                $tagGenerator->generate(self::ROLE_ASSIGNMENT_ROLE_LIST_TAG, [$roleAssignment->roleId]),
            ];
        };
        $this->getRoleAssignmentKeys = static function (RoleAssignment $roleAssignment) use ($tagGenerator) {
            return [
                $tagGenerator->generate(self::ROLE_ASSIGNMENT_TAG, [$roleAssignment->id], true),
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
            $this->tagGenerator->generate(self::CONTENT_TAG, [$user->id]),
        ]);

        $this->cache->deleteItems([
            $this->tagGenerator->generate(self::USER_TAG, [$user->id], true),
            $this->tagGenerator->generate(self::USER_WITH_BY_LOGIN_SUFFIX_TAG, [$this->escapeForCacheKey($user->login)], true),
            $this->tagGenerator->generate(self::USER_WITH_BY_EMAIL_SUFFIX_TAG, [$this->escapeForCacheKey($user->email)], true),
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
            $this->tagGenerator->generate(self::USER_TAG, [], true) . '-',
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
            $this->tagGenerator->generate(self::USER_TAG, [], true) . '-',
            function () use ($login) {
                return $this->persistenceHandler->userHandler()->loadByLogin($login);
            },
            $this->getUserTags,
            $this->getUserKeys,
            $this->tagGenerator->generate(self::BY_LOGIN_SUFFIX)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadByEmail($email)
    {
        // As load by email can return several items we threat it like a list here.
        return $this->getListCacheValue(
            $this->tagGenerator->generate(self::USER_WITH_BY_EMAIL_SUFFIX_TAG, [$this->escapeForCacheKey($email)], true) . '-',
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
        $tagGenerator = $this->tagGenerator;
        $getUserKeysFn = $this->getUserKeys;
        $getUserTagsFn = $this->getUserTags;

        return $this->getCacheValue(
            $hash,
            $tagGenerator->generate(self::USER_TAG, [], true) . '-',
            function ($hash) {
                return $this->persistenceHandler->userHandler()->loadUserByToken($hash);
            },
            static function (User $user) use ($getUserTagsFn, $tagGenerator) {
                $tags = $getUserTagsFn($user);
                // See updateUserToken()
                $tags[] = $tagGenerator->generate(self::USER_WITH_ACCOUNT_KEY_SUFFIX_TAG, [$user->id]);

                return $tags;
            },
            static function (User $user) use ($hash, $getUserKeysFn, $tagGenerator) {
                $keys = $getUserKeysFn($user);
                $tags[] = $tagGenerator->generate(self::USER_WITH_BY_ACCOUNT_KEY_SUFFIX_TAG, [$hash], true);

                return $keys;
            },
            $tagGenerator->generate(self::BY_ACCOUNT_KEY_SUFFIX)
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
            $this->tagGenerator->generate(self::CONTENT_TAG, [$user->id]),
            $this->tagGenerator->generate(self::USER_TAG, [$user->id]),
        ]);

        // Clear especially by email key as it might already be cached and this might represent change to email
        $this->cache->deleteItems([
            $this->tagGenerator->generate(
                self::USER_WITH_BY_EMAIL_SUFFIX_TAG,
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
            $this->tagGenerator->generate(self::USER_WITH_ACCOUNT_KEY_SUFFIX_TAG, [$userTokenUpdateStruct->userId]),
        ]);

        $this->cache->deleteItems([
            $this->tagGenerator->generate(
                self::USER_WITH_BY_ACCOUNT_KEY_SUFFIX_TAG,
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
            $this->tagGenerator->generate(self::USER_WITH_BY_ACCOUNT_KEY_SUFFIX_TAG, [$hash], true),
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
            $this->tagGenerator->generate(self::CONTENT_TAG, [$userId]),
            $this->tagGenerator->generate(self::USER_TAG, [$userId]),
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
            $this->tagGenerator->generate(self::ROLE_TAG, [], true) . '-',
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
            $this->tagGenerator->generate(self::ROLE_TAG, [], true) . '-',
            function () use ($identifier) {
                return $this->persistenceHandler->userHandler()->loadRoleByIdentifier($identifier);
            },
            $this->getRoleTags,
            $this->getRoleKeys,
            $this->tagGenerator->generate(self::BY_IDENTIFIER_SUFFIX)
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
            $this->tagGenerator->generate(self::ROLE_ASSIGNMENT_TAG, [], true) . '-',
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
        $tagGenerator = $this->tagGenerator;

        return $this->getListCacheValue(
            $tagGenerator->generate(self::ROLE_ASSIGNMENT_WITH_BY_ROLE_SUFFIX_TAG, [$roleId]) . '-',
            function () use ($roleId) {
                return $this->persistenceHandler->userHandler()->loadRoleAssignmentsByRoleId($roleId);
            },
            $this->getRoleAssignmentTags,
            $this->getRoleAssignmentKeys,
            /* Role update (policies) changes role assignment id, also need list tag in case of empty result */
            static function () use ($roleId, $tagGenerator) {
                return [
                    $tagGenerator->generate(self::ROLE_ASSIGNMENT_ROLE_LIST_TAG, [$roleId]),
                    $tagGenerator->generate(self::ROLE_TAG, [$roleId]),
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
        $tagGenerator = $this->tagGenerator;

        if ($inherit) {
            $key = $tagGenerator->generate(
                self::ROLE_ASSIGNMENT_WITH_BY_GROUP_INHERITED_SUFFIX_TAG,
                [$groupId],
                true
            );
        } else {
            $key = $tagGenerator->generate(
                self::ROLE_ASSIGNMENT_WITH_BY_GROUP_SUFFIX_TAG,
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
            static function () use ($groupId, $innerHandler, $tagGenerator) {
                // Tag needed for empty results, if not empty will alse be added by getRoleAssignmentTags().
                $cacheTags = [
                    $tagGenerator->generate(self::ROLE_ASSIGNMENT_GROUP_LIST_TAG, [$groupId]),
                ];
                // To make sure tree operations affecting this can clear the permission cache
                $locations = $innerHandler->locationHandler()->loadLocationsByContent($groupId);
                foreach ($locations as $location) {
                    foreach (explode('/', trim($location->pathString, '/')) as $pathId) {
                        $cacheTags[] = $tagGenerator->generate(self::LOCATION_PATH_TAG, [$pathId]);
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
            $this->tagGenerator->generate(self::ROLE_TAG, [$struct->id]),
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
                $this->tagGenerator->generate(self::ROLE_TAG, [$roleId]),
                $this->tagGenerator->generate(self::ROLE_ASSIGNMENT_ROLE_LIST_TAG, [$roleId]),
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
                $this->tagGenerator->generate(self::ROLE_TAG, [$roleDraft->originalId]),
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
            $this->tagGenerator->generate(self::ROLE_TAG, [$roleId]),
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
            $this->tagGenerator->generate(self::POLICY_TAG, [$policy->id]),
            $this->tagGenerator->generate(self::ROLE_TAG, [$policy->roleId]),
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
            $this->tagGenerator->generate(self::POLICY_TAG, [$policyId]),
            $this->tagGenerator->generate(self::ROLE_TAG, [$roleId]),
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
            $this->tagGenerator->generate(self::ROLE_ASSIGNMENT_GROUP_LIST_TAG, [$contentId]),
            $this->tagGenerator->generate(self::ROLE_ASSIGNMENT_ROLE_LIST_TAG, [$roleId]),
        ];

        $locations = $this->persistenceHandler->locationHandler()->loadLocationsByContent($contentId);
        foreach ($locations as $location) {
            $tags[] = $this->tagGenerator->generate(self::LOCATION_PATH_TAG, [$location->id]);
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
            $this->tagGenerator->generate(self::ROLE_ASSIGNMENT_GROUP_LIST_TAG, [$contentId]),
            $this->tagGenerator->generate(self::ROLE_ASSIGNMENT_ROLE_LIST_TAG, [$roleId]),
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
            $this->tagGenerator->generate(self::ROLE_ASSIGNMENT_TAG, [$roleAssignmentId]),
        ]);

        return $return;
    }
}
