<?php

/**
 * File containing the DoctrineDatabase Section Gateway class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;

/**
 * Section Handler.
 */
class DoctrineDatabase extends Gateway
{
    /**
     * Database handler.
     *
     * @var \eZ\Publish\Core\Persistence\Database\DatabaseHandler
     * @deprecated Start to use DBAL $connection instead.
     */
    protected $dbHandler;

    /**
     * Creates a new DoctrineDatabase Section Gateway.
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $dbHandler
     */
    public function __construct(DatabaseHandler $dbHandler)
    {
        $this->dbHandler = $dbHandler;
    }

    /**
     * Inserts a new section with $name and $identifier.
     *
     * @param string $name
     * @param string $identifier
     *
     * @return int The ID of the new section
     */
    public function insertSection($name, $identifier)
    {
        $query = $this->dbHandler->createInsertQuery();
        $query->insertInto(
            $this->dbHandler->quoteTable('ezsection')
        )->set(
            $this->dbHandler->quoteColumn('id'),
            $this->dbHandler->getAutoIncrementValue('ezsection', 'id')
        )->set(
            $this->dbHandler->quoteColumn('name'),
            $query->bindValue($name)
        )->set(
            $this->dbHandler->quoteColumn('identifier'),
            $query->bindValue($identifier)
        );

        $query->prepare()->execute();

        return $this->dbHandler->lastInsertId(
            $this->dbHandler->getSequenceName('ezsection', 'id')
        );
    }

    /**
     * Updates section with $id to have $name and $identifier.
     *
     * @param int $id
     * @param string $name
     * @param string $identifier
     */
    public function updateSection($id, $name, $identifier)
    {
        $query = $this->dbHandler->createUpdateQuery();
        $query->update(
            $this->dbHandler->quoteTable('ezsection')
        )->set(
            $this->dbHandler->quoteColumn('name'),
            $query->bindValue($name)
        )->set(
            $this->dbHandler->quoteColumn('identifier'),
            $query->bindValue($identifier)
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn('id'),
                $query->bindValue($id, null, \PDO::PARAM_INT)
            )
        );

        $query->prepare()->execute();
    }

    /**
     * Loads data for section with $id.
     *
     * @param int $id
     *
     * @return string[][]
     */
    public function loadSectionData($id)
    {
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $this->dbHandler->quoteColumn('id'),
            $this->dbHandler->quoteColumn('identifier'),
            $this->dbHandler->quoteColumn('name')
        )->from(
            $this->dbHandler->quoteTable('ezsection')
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn('id'),
                $query->bindValue($id, null, \PDO::PARAM_INT)
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Loads data for all sections.
     *
     * @return string[][]
     */
    public function loadAllSectionData()
    {
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $this->dbHandler->quoteColumn('id'),
            $this->dbHandler->quoteColumn('identifier'),
            $this->dbHandler->quoteColumn('name')
        )->from(
            $this->dbHandler->quoteTable('ezsection')
        );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Loads data for section with $identifier.
     *
     * @param int $identifier
     *
     * @return string[][]
     */
    public function loadSectionDataByIdentifier($identifier)
    {
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $this->dbHandler->quoteColumn('id'),
            $this->dbHandler->quoteColumn('identifier'),
            $this->dbHandler->quoteColumn('name')
        )->from(
            $this->dbHandler->quoteTable('ezsection')
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn('identifier'),
                $query->bindValue($identifier, null, \PDO::PARAM_STR)
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Counts the number of content objects assigned to section with $id.
     *
     * @param int $id
     *
     * @return int
     */
    public function countContentObjectsInSection($id)
    {
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $query->alias(
                $query->expr->count(
                    $this->dbHandler->quoteColumn('id')
                ),
                'content_count'
            )
        )->from(
            $this->dbHandler->quoteTable('ezcontentobject')
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn('section_id'),
                $query->bindValue($id, null, \PDO::PARAM_INT)
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return (int)$statement->fetchColumn();
    }

    /**
     * Counts the number of role policies using section with $id in their limitations.
     *
     * @param int $id
     *
     * @return int
     */
    public function countPoliciesUsingSection($id)
    {
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $query->expr->count(
                $this->dbHandler->quoteColumn('id', 'ezpolicy_limitation')
            )
        )->from(
            $this->dbHandler->quoteTable('ezpolicy_limitation'),
            $this->dbHandler->quoteTable('ezpolicy_limitation_value')
        )->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('id', 'ezpolicy_limitation'),
                    $this->dbHandler->quoteColumn('limitation_id', 'ezpolicy_limitation_value')
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('identifier', 'ezpolicy_limitation'),
                    $query->bindValue('Section', null, \PDO::PARAM_STR)
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('value', 'ezpolicy_limitation_value'),
                    $query->bindValue($id, null, \PDO::PARAM_INT)
                )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return (int)$statement->fetchColumn();
    }

    /**
     * Counts the number of role assignments using section with $id in their limitations.
     *
     * @param int $id
     *
     * @return int
     */
    public function countRoleAssignmentsUsingSection($id)
    {
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $query->expr->count(
                $this->dbHandler->quoteColumn('id', 'ezuser_role')
            )
        )->from(
            $this->dbHandler->quoteTable('ezuser_role')
        )->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('limit_identifier', 'ezuser_role'),
                    $query->bindValue('Section', null, \PDO::PARAM_STR)
                ),
                $query->expr->eq(
                    $this->dbHandler->quoteColumn('limit_value', 'ezuser_role'),
                    $query->bindValue($id, null, \PDO::PARAM_INT)
                )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        return (int)$statement->fetchColumn();
    }

    /**
     * Deletes the Section with $id.
     *
     * @param int $id
     */
    public function deleteSection($id)
    {
        $query = $this->dbHandler->createDeleteQuery();
        $query->deleteFrom(
            $this->dbHandler->quoteTable('ezsection')
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn('id'),
                $query->bindValue($id, null, \PDO::PARAM_INT)
            )
        );

        $query->prepare()->execute();
    }

    /**
     * Inserts the assignment of $contentId to $sectionId.
     *
     * @param int $sectionId
     * @param int $contentId
     */
    public function assignSectionToContent($sectionId, $contentId)
    {
        $query = $this->dbHandler->createUpdateQuery();
        $query->update(
            $this->dbHandler->quoteTable('ezcontentobject')
        )->set(
            $this->dbHandler->quoteColumn('section_id'),
            $query->bindValue($sectionId, null, \PDO::PARAM_INT)
        )->where(
            $query->expr->eq(
                $this->dbHandler->quoteColumn('id'),
                $query->bindValue($contentId, null, \PDO::PARAM_INT)
            )
        );

        $query->prepare()->execute();
    }
}
