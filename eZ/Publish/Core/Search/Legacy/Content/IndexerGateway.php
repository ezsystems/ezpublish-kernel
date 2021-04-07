<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Search\Legacy\Content;

use DateTimeInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\ResultStatement;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Search\Content\IndexerGateway as SPIIndexerGateway;
use Generator;

/**
 * @internal
 */
final class IndexerGateway implements SPIIndexerGateway
{
    /** @var \Doctrine\DBAL\Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getContentSince(DateTimeInterface $since, int $iterationCount): Generator
    {
        $query = $this->buildQueryForContentSince($since);
        $query->orderBy('c.modified');

        yield from $this->fetchIteration($query->execute(), $iterationCount);
    }

    public function countContentSince(DateTimeInterface $since): int
    {
        $query = $this->buildCountingQuery(
            $this->buildQueryForContentSince($since)
        );

        return (int)$query->execute()->fetchOne();
    }

    public function getContentInSubtree(string $locationPath, int $iterationCount): Generator
    {
        $query = $this->buildQueryForContentInSubtree($locationPath);

        yield from $this->fetchIteration($query->execute(), $iterationCount);
    }

    public function countContentInSubtree(string $locationPath): int
    {
        $query = $this->buildCountingQuery(
            $this->buildQueryForContentInSubtree($locationPath)
        );

        return (int)$query->execute()->fetchOne();
    }

    public function getAllContent(int $iterationCount): Generator
    {
        $query = $this->buildQueryForAllContent();

        yield from $this->fetchIteration($query->execute(), $iterationCount);
    }

    public function countAllContent(): int
    {
        $query = $this->buildCountingQuery(
            $this->buildQueryForAllContent()
        );

        return (int)$query->execute()->fetchOne();
    }

    private function buildQueryForContentSince(DateTimeInterface $since): QueryBuilder
    {
        return $this->connection->createQueryBuilder()
            ->select('c.id')
            ->from('ezcontentobject', 'c')
            ->where('c.status = :status')->andWhere('c.modified >= :since')
            ->setParameter('status', ContentInfo::STATUS_PUBLISHED, ParameterType::INTEGER)
            ->setParameter('since', $since->getTimestamp(), ParameterType::INTEGER);
    }

    private function buildQueryForContentInSubtree(string $locationPath): QueryBuilder
    {
        return $this->connection->createQueryBuilder()
            ->select('DISTINCT c.id')
            ->from('ezcontentobject', 'c')
            ->innerJoin('c', 'ezcontentobject_tree', 't', 't.contentobject_id = c.id')
            ->where('c.status = :status')
            ->andWhere('t.path_string LIKE :path')
            ->setParameter('status', ContentInfo::STATUS_PUBLISHED, ParameterType::INTEGER)
            ->setParameter('path', $locationPath . '%', ParameterType::STRING);
    }

    private function buildQueryForAllContent(): QueryBuilder
    {
        return $this->connection->createQueryBuilder()
            ->select('c.id')
            ->from('ezcontentobject', 'c')
            ->where('c.status = :status')
            ->setParameter('status', ContentInfo::STATUS_PUBLISHED, ParameterType::INTEGER);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function buildCountingQuery(QueryBuilder $query): QueryBuilder
    {
        $databasePlatform = $this->connection->getDatabasePlatform();

        // wrap existing select part in count expression
        $query->select(
            $databasePlatform->getCountExpression(
                $query->getQueryPart('select')[0]
            )
        );

        return $query;
    }

    private function fetchIteration(ResultStatement $stmt, int $iterationCount): Generator
    {
        do {
            $contentIds = [];
            for ($i = 0; $i < $iterationCount; ++$i) {
                if ($contentId = $stmt->fetchOne()) {
                    $contentIds[] = $contentId;
                } elseif (empty($contentIds)) {
                    return;
                } else {
                    break;
                }
            }

            yield $contentIds;
        } while (!empty($contentId));
    }
}
