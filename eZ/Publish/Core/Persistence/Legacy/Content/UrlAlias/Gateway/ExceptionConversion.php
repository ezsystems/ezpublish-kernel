<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway;

use eZ\Publish\Core\Base\Exceptions\DatabaseException;
use eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway;
use Doctrine\DBAL\DBALException;
use PDOException;

class ExceptionConversion extends Gateway
{
    /**
     * The wrapped gateway.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway
     */
    protected $innerGateway;

    /**
     * Creates a new exception conversion gateway around $innerGateway.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway $innerGateway
     */
    public function __construct(Gateway $innerGateway)
    {
        $this->innerGateway = $innerGateway;
    }

    public function setTable($name)
    {
        try {
            return $this->innerGateway->setTable($name);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadAllLocationEntries(int $locationId): array
    {
        try {
            return $this->innerGateway->loadAllLocationEntries($locationId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadLocationEntries($locationId, $custom = false, $languageId = false)
    {
        try {
            return $this->innerGateway->loadLocationEntries($locationId, $custom);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function isRootEntry($id)
    {
        try {
            return $this->innerGateway->isRootEntry($id);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function cleanupAfterPublish(
        string $action,
        int $languageId,
        int $newId,
        int $parentId,
        string $textMD5
    ): void {
        try {
            $this->innerGateway->cleanupAfterPublish(
                $action,
                $languageId,
                $newId,
                $parentId,
                $textMD5
            );
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function historizeBeforeSwap($action, $languageMask)
    {
        try {
            $this->innerGateway->historizeBeforeSwap($action, $languageMask);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function historizeId($id, $link)
    {
        try {
            $this->innerGateway->historizeId($id, $link);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function reparent($oldParentId, $newParentId)
    {
        try {
            $this->innerGateway->reparent($oldParentId, $newParentId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function updateRow($parentId, $textMD5, array $values)
    {
        try {
            $this->innerGateway->updateRow($parentId, $textMD5, $values);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function insertRow(array $values)
    {
        try {
            return $this->innerGateway->insertRow($values);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadRow(int $parentId, string $textMD5): array
    {
        try {
            return $this->innerGateway->loadRow($parentId, $textMD5);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadAutogeneratedEntry($action, $parentId = null)
    {
        try {
            return $this->innerGateway->loadAutogeneratedEntry($action, $parentId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function remove($action, $id = null)
    {
        try {
            $this->innerGateway->remove($action, $id);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function listGlobalEntries($languageCode = null, $offset = 0, $limit = -1)
    {
        try {
            return $this->innerGateway->listGlobalEntries($languageCode, $offset, $limit);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function removeCustomAlias($parentId, $textMD5)
    {
        try {
            return $this->innerGateway->removeCustomAlias($parentId, $textMD5);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadUrlAliasData(array $urlHashes, ?int $languageMask = null)
    {
        try {
            return $this->innerGateway->loadUrlAliasData($urlHashes, $languageMask);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadPathData($id)
    {
        try {
            return $this->innerGateway->loadPathData($id);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadPathDataByHierarchy(array $hierarchyData)
    {
        try {
            return $this->innerGateway->loadPathDataByHierarchy($hierarchyData);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function loadAutogeneratedEntries($parentId, $includeHistory = false)
    {
        try {
            return $this->innerGateway->loadAutogeneratedEntries($parentId, $includeHistory);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function getNextId()
    {
        try {
            return $this->innerGateway->getNextId();
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function getLocationContentMainLanguageId($locationId)
    {
        try {
            return $this->innerGateway->getLocationContentMainLanguageId($locationId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function bulkRemoveTranslation($languageId, $actions)
    {
        try {
            return $this->innerGateway->bulkRemoveTranslation($languageId, $actions);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function archiveUrlAliasesForDeletedTranslations($locationId, $parentId, array $languageIds)
    {
        try {
            $this->innerGateway->archiveUrlAliasesForDeletedTranslations($locationId, $parentId, $languageIds);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function deleteUrlAliasesWithoutLocation()
    {
        try {
            return $this->innerGateway->deleteUrlAliasesWithoutLocation();
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function deleteUrlAliasesWithoutParent()
    {
        try {
            return $this->innerGateway->deleteUrlAliasesWithoutParent();
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function deleteUrlAliasesWithBrokenLink()
    {
        try {
            return $this->innerGateway->deleteUrlAliasesWithBrokenLink();
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function repairBrokenUrlAliasesForLocation(int $locationId)
    {
        try {
            return $this->innerGateway->repairBrokenUrlAliasesForLocation($locationId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function deleteUrlNopAliasesWithoutChildren(): int
    {
        try {
            return $this->innerGateway->deleteUrlNopAliasesWithoutChildren();
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }

    public function getAllChildrenAliases(int $parentId): array
    {
        try {
            return $this->innerGateway->getAllChildrenAliases($parentId);
        } catch (DBALException | PDOException $e) {
            throw DatabaseException::wrap($e);
        }
    }
}
