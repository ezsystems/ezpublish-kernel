<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\ParameterType;
use eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway;

/**
 * @internal Gateway implementation is considered internal. Use Persistence Section Handler instead.
 *
 * @see \eZ\Publish\SPI\Persistence\Content\Section\Handler
 */
final class DoctrineDatabase extends Gateway
{
    /** @var \Doctrine\DBAL\Connection */
    private $connection;

    /** @var \Doctrine\DBAL\Platforms\AbstractPlatform */
    private $dbPlatform;

    /**
     * Creates a new DoctrineDatabase Section Gateway.
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->dbPlatform = $this->connection->getDatabasePlatform();
    }

    /**
     * Inserts a new section with $name and $identifier.
     *
     * @param string $name
     * @param string $identifier
     *
     * @return int The ID of the new section
     */
    public function insertSection(string $name, string $identifier): int
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->insert('ezsection')
            ->values(
                [
                    'name' => $query->createPositionalParameter($name),
                    'identifier' => $query->createPositionalParameter($identifier),
                ]
            );

        $query->execute();

        return (int)$this->connection->lastInsertId(Gateway::CONTENT_SECTION_SEQ);
    }

    /**
     * Updates section with $id to have $name and $identifier.
     *
     * @param int $id
     * @param string $name
     * @param string $identifier
     */
    public function updateSection(int $id, string $name, string $identifier): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->update('ezsection')
            ->set('name', $query->createPositionalParameter($name))
            ->set('identifier', $query->createPositionalParameter($identifier))
            ->where(
                $query->expr()->eq(
                    'id',
                    $query->createPositionalParameter($id, ParameterType::INTEGER)
                )
            );

        $query->execute();
    }

    /**
     * Loads data for section with $id.
     *
     * @param int $id
     *
     * @return string[][]
     */
    public function loadSectionData(int $id): array
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(['id', 'identifier', 'name'])
            ->from('ezsection')
            ->where(
                $query->expr()->eq(
                    'id',
                    $query->createPositionalParameter($id, ParameterType::INTEGER)
                )
            );

        $statement = $query->execute();

        return $statement->fetchAll(FetchMode::ASSOCIATIVE);
    }

    /**
     * Loads data for all sections.
     *
     * @return string[][]
     */
    public function loadAllSectionData(): array
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(['id', 'identifier', 'name'])
            ->from('ezsection');

        $statement = $query->execute();

        return $statement->fetchAll(FetchMode::ASSOCIATIVE);
    }

    /**
     * Loads data for section with $identifier.
     *
     * @param string $identifier
     *
     * @return string[][]
     */
    public function loadSectionDataByIdentifier(string $identifier): array
    {
        $query = $this->connection->createQueryBuilder();
        $query->select(
            'id',
            'identifier',
            'name'
        )->from(
            'ezsection'
        )->where(
            $query->expr()->eq(
                'identifier',
                $query->createPositionalParameter($identifier, ParameterType::STRING)
            )
        );

        $statement = $query->execute();

        return $statement->fetchAll(FetchMode::ASSOCIATIVE);
    }

    /**
     * Counts the number of content objects assigned to section with $id.
     *
     * @param int $id
     *
     * @return int
     */
    public function countContentObjectsInSection(int $id): int
    {
        $query = $this->connection->createQueryBuilder();
        $query->select(
            $this->dbPlatform->getCountExpression('id')
        )->from(
            'ezcontentobject'
        )->where(
            $query->expr()->eq(
                'section_id',
                $query->createPositionalParameter($id, ParameterType::INTEGER)
            )
        );

        $statement = $query->execute();

        return (int)$statement->fetchColumn();
    }

    /**
     * Counts the number of role policies using section with $id in their limitations.
     *
     * @param int $id
     *
     * @return int
     */
    public function countPoliciesUsingSection(int $id): int
    {
        $query = $this->connection->createQueryBuilder();
        $expr = $query->expr();
        $query
            ->select($this->dbPlatform->getCountExpression('l.id'))
            ->from('ezpolicy_limitation', 'l')
            ->join(
                'l',
                'ezpolicy_limitation_value',
                'lv',
                $expr->eq(
                    'l.id',
                    'lv.limitation_id'
                )
            )
            ->where(
                $expr->eq(
                    'l.identifier',
                    $query->createPositionalParameter('Section', ParameterType::STRING)
                )
            )
            ->andWhere(
                $expr->eq(
                    'lv.value',
                    $query->createPositionalParameter($id, ParameterType::INTEGER)
                )
            )
        ;

        return (int)$query->execute()->fetchColumn();
    }

    /**
     * Counts the number of role assignments using section with $id in their limitations.
     *
     * @param int $id
     *
     * @return int
     */
    public function countRoleAssignmentsUsingSection(int $id): int
    {
        $query = $this->connection->createQueryBuilder();
        $expr = $query->expr();
        $query
            ->select($this->dbPlatform->getCountExpression('ur.id'))
            ->from('ezuser_role', 'ur')
            ->where(
                $expr->eq(
                    'ur.limit_identifier',
                    $query->createPositionalParameter('Section', ParameterType::STRING)
                )
            )
            ->andWhere(
                $expr->eq(
                    'ur.limit_value',
                    $query->createPositionalParameter($id, ParameterType::INTEGER)
                )
            )
        ;

        return (int)$query->execute()->fetchColumn();
    }

    /**
     * Deletes the Section with $id.
     *
     * @param int $id
     */
    public function deleteSection(int $id): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->delete('ezsection')
            ->where(
                $query->expr()->eq(
                    'id',
                    $query->createPositionalParameter($id, ParameterType::INTEGER)
                )
            );

        $query->execute();
    }

    /**
     * Inserts the assignment of $contentId to $sectionId.
     *
     * @param int $sectionId
     * @param int $contentId
     */
    public function assignSectionToContent(int $sectionId, int $contentId): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->update('ezcontentobject')
            ->set(
                'section_id',
                $query->createPositionalParameter($sectionId, ParameterType::INTEGER)
            )
            ->where(
                $query->expr()->eq(
                    'id',
                    $query->createPositionalParameter($contentId, ParameterType::INTEGER)
                )
            );

        $query->execute();
    }
}
