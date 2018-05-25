<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Bookmark\Gateway;

use Doctrine\DBAL\DBALException;
use eZ\Publish\Core\Persistence\Legacy\Bookmark\Gateway;
use eZ\Publish\SPI\Persistence\Bookmark\Bookmark;
use PDOException;
use RuntimeException;

class ExceptionConversion extends Gateway
{
    /**
     * The wrapped gateway.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Bookmark\Gateway
     */
    protected $innerGateway;

    /**
     * ExceptionConversion constructor.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Bookmark\Gateway $innerGateway
     */
    public function __construct(Gateway $innerGateway)
    {
        $this->innerGateway = $innerGateway;
    }

    /**
     * {@inheritdoc}
     */
    public function insertBookmark(Bookmark $bookmark): int
    {
        try {
            return $this->innerGateway->insertBookmark($bookmark);
        } catch (DBALException | PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteBookmark(int $id): void
    {
        try {
            $this->innerGateway->deleteBookmark($id);
        } catch (DBALException | PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function loadBookmarkDataByUserIdAndLocationId(int $userId, array $locationId): array
    {
        try {
            return $this->innerGateway->loadBookmarkDataByUserIdAndLocationId($userId, $locationId);
        } catch (DBALException | PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserBookmarks(int $userId, int $offset = 0, int $limit = -1): array
    {
        try {
            return $this->innerGateway->loadUserBookmarks($userId, $offset, $limit);
        } catch (DBALException | PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function countUserBookmarks(int $userId): int
    {
        try {
            return $this->innerGateway->countUserBookmarks($userId);
        } catch (DBALException | PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function locationSwapped(int $location1Id, int $location2Id): void
    {
        try {
            $this->innerGateway->locationSwapped($location1Id, $location2Id);
        } catch (DBALException | PDOException $e) {
            throw new RuntimeException('Database error', 0, $e);
        }
    }
}
