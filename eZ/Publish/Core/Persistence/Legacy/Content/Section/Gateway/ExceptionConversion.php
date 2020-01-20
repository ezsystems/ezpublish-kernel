<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway;

use eZ\Publish\Core\Base\Exceptions\DatabaseException;
use eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway;
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
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway
     */
    private $innerGateway;

    /**
     * Creates a new exception conversion gateway around $innerGateway.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway $innerGateway
     */
    public function __construct(Gateway $innerGateway)
    {
        $this->innerGateway = $innerGateway;
    }

    public function insertSection($name, $identifier)
    {
        try {
            return $this->innerGateway->insertSection($name, $identifier);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function updateSection($id, $name, $identifier)
    {
        try {
            return $this->innerGateway->updateSection($id, $name, $identifier);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadSectionData($id)
    {
        try {
            return $this->innerGateway->loadSectionData($id);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadAllSectionData()
    {
        try {
            return $this->innerGateway->loadAllSectionData();
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadSectionDataByIdentifier($identifier)
    {
        try {
            return $this->innerGateway->loadSectionDataByIdentifier($identifier);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function countContentObjectsInSection($id)
    {
        try {
            return $this->innerGateway->countContentObjectsInSection($id);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function countPoliciesUsingSection($id)
    {
        try {
            return $this->innerGateway->countPoliciesUsingSection($id);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function countRoleAssignmentsUsingSection($id)
    {
        try {
            return $this->innerGateway->countRoleAssignmentsUsingSection($id);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function deleteSection($id)
    {
        try {
            return $this->innerGateway->deleteSection($id);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function assignSectionToContent($sectionId, $contentId)
    {
        try {
            return $this->innerGateway->assignSectionToContent($sectionId, $contentId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }
}
