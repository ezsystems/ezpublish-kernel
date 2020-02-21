<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway;

use eZ\Publish\Core\Base\Exceptions\DatabaseException;
use eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway;
use eZ\Publish\SPI\Persistence\Content\ObjectState;
use eZ\Publish\SPI\Persistence\Content\ObjectState\Group;
use Doctrine\DBAL\DBALException;
use PDOException;

/**
 * @internal Internal exception conversion layer.
 */
final class ExceptionConversion extends Gateway
{
    /**
     * The wrapped gateway.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway
     */
    private $innerGateway;

    /**
     * Creates a new exception conversion gateway around $innerGateway.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway $innerGateway
     */
    public function __construct(Gateway $innerGateway)
    {
        $this->innerGateway = $innerGateway;
    }

    public function loadObjectStateData(int $stateId): array
    {
        try {
            return $this->innerGateway->loadObjectStateData($stateId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadObjectStateDataByIdentifier(string $identifier, int $groupId): array
    {
        try {
            return $this->innerGateway->loadObjectStateDataByIdentifier($identifier, $groupId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadObjectStateListData(int $groupId): array
    {
        try {
            return $this->innerGateway->loadObjectStateListData($groupId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadObjectStateGroupData(int $groupId): array
    {
        try {
            return $this->innerGateway->loadObjectStateGroupData($groupId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadObjectStateGroupDataByIdentifier(string $identifier): array
    {
        try {
            return $this->innerGateway->loadObjectStateGroupDataByIdentifier($identifier);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadObjectStateGroupListData(int $offset, int $limit): array
    {
        try {
            return $this->innerGateway->loadObjectStateGroupListData($offset, $limit);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function insertObjectState(ObjectState $objectState, int $groupId): void
    {
        try {
            $this->innerGateway->insertObjectState($objectState, $groupId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function updateObjectState(ObjectState $objectState): void
    {
        try {
            $this->innerGateway->updateObjectState($objectState);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function deleteObjectState(int $stateId): void
    {
        try {
            $this->innerGateway->deleteObjectState($stateId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function updateObjectStateLinks(int $oldStateId, int $newStateId): void
    {
        try {
            $this->innerGateway->updateObjectStateLinks($oldStateId, $newStateId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function deleteObjectStateLinks(int $stateId): void
    {
        try {
            $this->innerGateway->deleteObjectStateLinks($stateId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function insertObjectStateGroup(Group $objectStateGroup): void
    {
        try {
            $this->innerGateway->insertObjectStateGroup($objectStateGroup);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function updateObjectStateGroup(Group $objectStateGroup): void
    {
        try {
            $this->innerGateway->updateObjectStateGroup($objectStateGroup);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function deleteObjectStateGroup(int $groupId): void
    {
        try {
            $this->innerGateway->deleteObjectStateGroup($groupId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function setContentState(int $contentId, int $groupId, int $stateId): void
    {
        try {
            $this->innerGateway->setContentState($contentId, $groupId, $stateId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadObjectStateDataForContent(int $contentId, int $stateGroupId): array
    {
        try {
            return $this->innerGateway->loadObjectStateDataForContent($contentId, $stateGroupId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function getContentCount(int $stateId): int
    {
        try {
            return $this->innerGateway->getContentCount($stateId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function updateObjectStatePriority(int $stateId, int $priority): void
    {
        try {
            $this->innerGateway->updateObjectStatePriority($stateId, $priority);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }
}
