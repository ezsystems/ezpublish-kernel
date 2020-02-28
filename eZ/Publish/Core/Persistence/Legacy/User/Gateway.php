<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\User;

use eZ\Publish\SPI\Persistence\User\UserTokenUpdateStruct;

/**
 * User Gateway.
 *
 * @internal For internal use by Persistence Handlers.
 */
abstract class Gateway
{
    /**
     * Load a User by User ID.
     */
    abstract public function load(int $userId): array;

    /**
     * Load a User by User login.
     */
    abstract public function loadByLogin(string $login): array;

    /**
     * Load a User by User e-mail.
     */
    abstract public function loadByEmail(string $email): array;

    /**
     * Load a User by User token.
     */
    abstract public function loadUserByToken(string $hash): array;

    /**
     * Update a User token specified by UserTokenUpdateStruct.
     *
     * @see \eZ\Publish\SPI\Persistence\User\UserTokenUpdateStruct
     */
    abstract public function updateUserToken(UserTokenUpdateStruct $userTokenUpdateStruct): void;

    /**
     * Expire the given User token.
     */
    abstract public function expireUserToken(string $hash): void;

    /**
     * Assign, with the given Limitation, a Role to a User.
     *
     * @param array $limitation a map of the Limitation identifiers to raw Limitation values.
     */
    abstract public function assignRole(int $contentId, int $roleId, array $limitation): void;

    /**
     * Remove a Role from User or User group.
     */
    abstract public function removeRole(int $contentId, int $roleId): void;

    /**
     * Remove a Role from User or User group, by assignment ID.
     */
    abstract public function removeRoleAssignmentById(int $roleAssignmentId): void;
}
