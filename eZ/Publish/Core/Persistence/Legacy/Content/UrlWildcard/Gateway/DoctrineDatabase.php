<?php

/**
 * File containing the DoctrineDatabase UrlWildcard Gateway class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway;
use eZ\Publish\SPI\Persistence\Content\UrlWildcard;

/**
 * URL wildcard gateway implementation using the Doctrine database.
 *
 * @internal Gateway implementation is considered internal. Use Persistence UrlWildcard Handler instead.
 *
 * @see \eZ\Publish\SPI\Persistence\Content\UrlWildcard\Handler
 */
final class DoctrineDatabase extends Gateway
{
    /**
     * 2^30, since PHP_INT_MAX can cause overflows in DB systems, if PHP is run
     * on 64 bit systems.
     */
    private const MAX_LIMIT = 1073741824;

    /** @var \Doctrine\DBAL\Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Inserts the given UrlWildcard.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\UrlWildcard $urlWildcard
     *
     * @return mixed
     */
    public function insertUrlWildcard(UrlWildcard $urlWildcard)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->insert(self::URL_WILDCARD_TABLE)
            ->values(
                [
                    'destination_url' => $query->createPositionalParameter(
                        trim($urlWildcard->destinationUrl, '/ '),
                        ParameterType::STRING
                    ),
                    'source_url' => $query->createPositionalParameter(
                        trim($urlWildcard->sourceUrl, '/ '),
                        ParameterType::STRING
                    ),
                    'type' => $query->createPositionalParameter(
                        $urlWildcard->forward ? 1 : 2,
                        ParameterType::INTEGER
                    ),
                ]
            );

        $query->execute();

        return (int)$this->connection->lastInsertId(self::URL_WILDCARD_SEQ);
    }

    /**
     * Deletes the UrlWildcard with given $id.
     *
     * @param mixed $id
     */
    public function deleteUrlWildcard($id)
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->delete(self::URL_WILDCARD_TABLE)
            ->where(
                $query->expr()->eq(
                    'id',
                    $query->createPositionalParameter($id, ParameterType::INTEGER)
                )
            );
        $query->execute();
    }

    private function buildLoadUrlWildcardDataQuery(): QueryBuilder
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('id', 'destination_url', 'source_url', 'type')
            ->from(self::URL_WILDCARD_TABLE);

        return $query;
    }

    /**
     * Loads an array with data about UrlWildcard with $id.
     *
     * @param mixed $id
     *
     * @return array
     */
    public function loadUrlWildcardData($id)
    {
        $query = $this->buildLoadUrlWildcardDataQuery();
        $query
            ->where(
                $query->expr()->eq(
                    'id',
                    $query->createPositionalParameter($id, ParameterType::INTEGER)
                )
            );
        $result = $query->execute()->fetch(FetchMode::ASSOCIATIVE);

        return false !== $result ? $result : [];
    }

    /**
     * Loads an array with data about UrlWildcards (paged).
     *
     * @param mixed $offset
     * @param mixed $limit
     *
     * @return array
     */
    public function loadUrlWildcardsData($offset = 0, $limit = -1)
    {
        $query = $this->buildLoadUrlWildcardDataQuery();
        $query
            ->setMaxResults($limit > 0 ? $limit : self::MAX_LIMIT)
            ->setFirstResult($offset);

        $stmt = $query->execute();

        return $stmt->fetchAll(FetchMode::ASSOCIATIVE);
    }

    /**
     * Loads the UrlWildcard with given $sourceUrl.
     *
     * @param string $sourceUrl
     *
     * @return array
     */
    public function loadUrlWildcardBySourceUrl(string $sourceUrl): array
    {
        $query = $this->buildLoadUrlWildcardDataQuery();
        $expr = $query->expr();
        $query
            ->where(
                $expr->eq(
                    'source_url',
                    $query->createPositionalParameter($sourceUrl)
                )
            );

        $result = $query->execute()->fetch(FetchMode::ASSOCIATIVE);

        return false !== $result ? $result : [];
    }
}
