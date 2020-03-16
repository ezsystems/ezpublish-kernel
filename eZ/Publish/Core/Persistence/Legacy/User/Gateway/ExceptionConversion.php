<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\User\Gateway;

use eZ\Publish\Core\Base\Exceptions\DatabaseException;
use eZ\Publish\Core\Persistence\Legacy\User\Gateway;
use Doctrine\DBAL\DBALException;
use eZ\Publish\SPI\Persistence\User\UserTokenUpdateStruct;
use PDOException;

/**
 * @internal Internal exception conversion layer.
 */
final class ExceptionConversion extends Gateway
{
    /**
     * The wrapped gateway.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\User\Gateway
     */
    private $innerGateway;

    /**
     * Create a new exception conversion gateway around $innerGateway.
     *
     * @param Gateway $innerGateway
     */
    public function __construct(Gateway $innerGateway)
    {
        $this->innerGateway = $innerGateway;
    }

    public function load(int $userId): array
    {
        try {
            return $this->innerGateway->load($userId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadByLogin(string $login): array
    {
        try {
            return $this->innerGateway->loadByLogin($login);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadByEmail(string $email): array
    {
        try {
            return $this->innerGateway->loadByEmail($email);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadUserByToken(string $hash): array
    {
        try {
            return $this->innerGateway->loadUserByToken($hash);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function updateUserToken(UserTokenUpdateStruct $userTokenUpdateStruct): void
    {
        try {
            $this->innerGateway->updateUserToken($userTokenUpdateStruct);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function expireUserToken(string $hash): void
    {
        try {
            $this->innerGateway->expireUserToken($hash);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function assignRole(int $contentId, int $roleId, array $limitation): void
    {
        try {
            $this->innerGateway->assignRole($contentId, $roleId, $limitation);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function removeRole(int $contentId, int $roleId): void
    {
        try {
            $this->innerGateway->removeRole($contentId, $roleId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function removeRoleAssignmentById(int $roleAssignmentId): void
    {
        try {
            $this->innerGateway->removeRoleAssignmentById($roleAssignmentId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }
}
