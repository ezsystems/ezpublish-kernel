<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\UserPreference\Gateway;

use Doctrine\DBAL\DBALException;
use eZ\Publish\Core\Persistence\Legacy\UserPreference\Gateway;
use eZ\Publish\SPI\Persistence\UserPreference\UserPreferenceSetStruct;
use PDOException;
use RuntimeException;

class ExceptionConversion extends Gateway
{
    /**
     * The wrapped gateway.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\UserPreference\Gateway
     */
    protected $innerGateway;

    /**
     * ExceptionConversion constructor.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\UserPreference\Gateway $innerGateway
     */
    public function __construct(Gateway $innerGateway)
    {
        $this->innerGateway = $innerGateway;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserPreferenceByUserIdAndName(int $userId, string $name): array
    {
        try {
            return $this->innerGateway->getUserPreferenceByUserIdAndName($userId, $name);
        } catch (DBALException | PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function countUserPreferences(int $userId): int
    {
        try {
            return $this->innerGateway->countUserPreferences($userId);
        } catch (DBALException | PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserPreferences(int $userId, int $offset = 0, int $limit = -1): array
    {
        try {
            return $this->innerGateway->loadUserPreferences($userId, $offset, $limit);
        } catch (DBALException | PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setUserPreference(UserPreferenceSetStruct $setStruct): int
    {
        try {
            return $this->innerGateway->setUserPreference($setStruct);
        } catch (DBALException | PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }
}
