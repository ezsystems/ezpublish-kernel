<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

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

    public function insertSection(string $name, string $identifier): int
    {
        try {
            return $this->innerGateway->insertSection($name, $identifier);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function updateSection(int $id, string $name, string $identifier): void
    {
        try {
            $this->innerGateway->updateSection($id, $name, $identifier);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadSectionData(int $id): array
    {
        try {
            return $this->innerGateway->loadSectionData($id);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadAllSectionData(): array
    {
        try {
            return $this->innerGateway->loadAllSectionData();
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadSectionDataByIdentifier(string $identifier): array
    {
        try {
            return $this->innerGateway->loadSectionDataByIdentifier($identifier);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function countContentObjectsInSection(int $id): int
    {
        try {
            return $this->innerGateway->countContentObjectsInSection($id);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function countPoliciesUsingSection(int $id): int
    {
        try {
            return $this->innerGateway->countPoliciesUsingSection($id);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function countRoleAssignmentsUsingSection(int $id): int
    {
        try {
            return $this->innerGateway->countRoleAssignmentsUsingSection($id);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function deleteSection(int $id): void
    {
        try {
            $this->innerGateway->deleteSection($id);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function assignSectionToContent(int $sectionId, int $contentId): void
    {
        try {
            $this->innerGateway->assignSectionToContent($sectionId, $contentId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }
}
