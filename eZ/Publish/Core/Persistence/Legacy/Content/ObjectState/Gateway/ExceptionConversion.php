<?php

/**
 * File containing the ObjectState Gateway class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway;

use eZ\Publish\Core\Base\Exceptions\DatabaseException;
use eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway;
use eZ\Publish\SPI\Persistence\Content\ObjectState;
use eZ\Publish\SPI\Persistence\Content\ObjectState\Group;
use Doctrine\DBAL\DBALException;
use PDOException;

class ExceptionConversion extends Gateway
{
    /**
     * The wrapped gateway.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway
     */
    protected $innerGateway;

    /**
     * Creates a new exception conversion gateway around $innerGateway.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway $innerGateway
     */
    public function __construct(Gateway $innerGateway)
    {
        $this->innerGateway = $innerGateway;
    }

    public function loadObjectStateData($stateId)
    {
        try {
            return $this->innerGateway->loadObjectStateData($stateId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadObjectStateDataByIdentifier($identifier, $groupId)
    {
        try {
            return $this->innerGateway->loadObjectStateDataByIdentifier($identifier, $groupId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadObjectStateListData($groupId)
    {
        try {
            return $this->innerGateway->loadObjectStateListData($groupId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadObjectStateGroupData($groupId)
    {
        try {
            return $this->innerGateway->loadObjectStateGroupData($groupId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadObjectStateGroupDataByIdentifier($identifier)
    {
        try {
            return $this->innerGateway->loadObjectStateGroupDataByIdentifier($identifier);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadObjectStateGroupListData($offset, $limit)
    {
        try {
            return $this->innerGateway->loadObjectStateGroupListData($offset, $limit);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function insertObjectState(ObjectState $objectState, $groupId)
    {
        try {
            return $this->innerGateway->insertObjectState($objectState, $groupId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function updateObjectState(ObjectState $objectState)
    {
        try {
            return $this->innerGateway->updateObjectState($objectState);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function deleteObjectState($stateId)
    {
        try {
            return $this->innerGateway->deleteObjectState($stateId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function updateObjectStateLinks($oldStateId, $newStateId)
    {
        try {
            return $this->innerGateway->updateObjectStateLinks($oldStateId, $newStateId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function deleteObjectStateLinks($stateId)
    {
        try {
            return $this->innerGateway->deleteObjectStateLinks($stateId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function insertObjectStateGroup(Group $objectStateGroup)
    {
        try {
            return $this->innerGateway->insertObjectStateGroup($objectStateGroup);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function updateObjectStateGroup(Group $objectStateGroup)
    {
        try {
            return $this->innerGateway->updateObjectStateGroup($objectStateGroup);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function deleteObjectStateGroup($groupId)
    {
        try {
            return $this->innerGateway->deleteObjectStateGroup($groupId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function setContentState($contentId, $groupId, $stateId)
    {
        try {
            return $this->innerGateway->setContentState($contentId, $groupId, $stateId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadObjectStateDataForContent($contentId, $stateGroupId)
    {
        try {
            return $this->innerGateway->loadObjectStateDataForContent($contentId, $stateGroupId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function getContentCount($stateId)
    {
        try {
            return $this->innerGateway->getContentCount($stateId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function updateObjectStatePriority($stateId, $priority)
    {
        try {
            return $this->innerGateway->updateObjectStatePriority($stateId, $priority);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }
}
