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

    public function getStatementContentSince(DateTimeInterface $since, bool $count = false): ResultStatement
    {
        $q = $this->connection->createQueryBuilder()
            ->select($count ? 'count(c.id)' : 'c.id')
            ->from('ezcontentobject', 'c')
            ->where('c.status = :status')->andWhere('c.modified >= :since')
            ->orderBy('c.modified')
            ->setParameter('status', ContentInfo::STATUS_PUBLISHED, ParameterType::INTEGER)
            ->setParameter('since', $since->getTimestamp(), ParameterType::INTEGER);

        return $q->execute();
    }

    public function getStatementSubtree(string $locationPath, bool $count = false): ResultStatement
    {
        $q = $this->connection->createQueryBuilder()
            ->select($count ? 'count(DISTINCT c.id)' : 'DISTINCT c.id')
            ->from('ezcontentobject', 'c')
            ->innerJoin('c', 'ezcontentobject_tree', 't', 't.contentobject_id = c.id')
            ->where('c.status = :status')
            ->andWhere('t.path_string LIKE :path')
            ->setParameter('status', ContentInfo::STATUS_PUBLISHED, ParameterType::INTEGER)
            ->setParameter('path', $locationPath . '%', ParameterType::STRING);

        return $q->execute();
    }

    public function getStatementContentAll(bool $count = false): ResultStatement
    {
        $q = $this->connection->createQueryBuilder()
            ->select($count ? 'count(c.id)' : 'c.id')
            ->from('ezcontentobject', 'c')
            ->where('c.status = :status')
            ->setParameter('status', ContentInfo::STATUS_PUBLISHED, ParameterType::INTEGER);

        return $q->execute();
    }

    public function fetchIteration(ResultStatement $stmt, int $iterationCount): Generator
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
