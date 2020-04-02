<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Location\Gateway;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\ParameterType;
use eZ\Publish\Core\Persistence\Legacy\Content\Gateway as ContentGateway;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseConverter;
use eZ\Publish\Core\Search\Legacy\Content\Location\Gateway;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;
use RuntimeException;

/**
 * Location gateway implementation using the Doctrine database.
 */
final class DoctrineDatabase extends Gateway
{
    /**
     * 2^30, since PHP_INT_MAX can cause overflows in DB systems, if PHP is run
     * on 64 bit systems.
     */
    const MAX_LIMIT = 1073741824;

    /** @var \Doctrine\DBAL\Connection */
    private $connection;

    /** @var \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter */
    private $criteriaConverter;

    /** @var \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\SortClauseConverter */
    private $sortClauseConverter;

    /**
     * Language handler.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Language\Handler
     */
    private $languageHandler;

    /** @var \Doctrine\DBAL\Platforms\AbstractPlatform */
    private $dbPlatform;

    /**
     * Construct from database handler.
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __construct(
        Connection $connection,
        CriteriaConverter $criteriaConverter,
        SortClauseConverter $sortClauseConverter,
        LanguageHandler $languageHandler
    ) {
        $this->connection = $connection;
        $this->dbPlatform = $connection->getDatabasePlatform();
        $this->criteriaConverter = $criteriaConverter;
        $this->sortClauseConverter = $sortClauseConverter;
        $this->languageHandler = $languageHandler;
    }

    public function find(
        Criterion $criterion,
        $offset,
        $limit,
        array $sortClauses = null,
        array $languageFilter = [],
        $doCount = true
    ): array {
        $count = $doCount ? $this->getTotalCount($criterion, $languageFilter) : null;

        if (!$doCount && $limit === 0) {
            throw new RuntimeException('Invalid query. Cannot disable count and request 0 items at the same time.');
        }

        if ($limit === 0 || ($count !== null && $count <= $offset)) {
            return ['count' => $count, 'rows' => []];
        }

        $selectQuery = $this->connection->createQueryBuilder();
        $selectQuery->select(
            't.*',
            'c.language_mask',
            'c.initial_language_id'
        );

        if ($sortClauses !== null) {
            $this->sortClauseConverter->applySelect($selectQuery, $sortClauses);
        }

        $selectQuery
            ->from('ezcontentobject_tree', 't')
            ->innerJoin(
                't',
                'ezcontentobject',
                'c',
                't.contentobject_id = c.id'
            )
            ->innerJoin(
                'c',
                'ezcontentobject_version',
                'v',
                'c.id = v.contentobject_id',
            );

        if ($sortClauses !== null) {
            $this->sortClauseConverter->applyJoin($selectQuery, $sortClauses, $languageFilter);
        }

        $selectQuery->where(
            $this->criteriaConverter->convertCriteria($selectQuery, $criterion, $languageFilter),
            $selectQuery->expr()->eq(
                'c.status',
                //ContentInfo::STATUS_PUBLISHED
                $selectQuery->createNamedParameter(1, ParameterType::INTEGER)
            ),
            $selectQuery->expr()->eq(
                'v.status',
                //VersionInfo::STATUS_PUBLISHED
                $selectQuery->createNamedParameter(1, ParameterType::INTEGER)
            ),
            $selectQuery->expr()->neq(
                't.depth',
                $selectQuery->createNamedParameter(0, ParameterType::INTEGER)
            )
        );

        // If not main-languages query
        if (!empty($languageFilter['languages'])) {
            $selectQuery->andWhere(
                $selectQuery->expr()->gt(
                    $this->dbPlatform->getBitAndComparisonExpression(
                        'c.language_mask',
                        $selectQuery->createNamedParameter(
                            $this->getLanguageMask($languageFilter),
                            ParameterType::INTEGER
                        )
                    ),
                    $selectQuery->createNamedParameter(0, ParameterType::INTEGER)
                )
            );
        }

        if ($sortClauses !== null) {
            $this->sortClauseConverter->applyOrderBy($selectQuery);
        }

        $selectQuery->setMaxResults($limit);
        $selectQuery->setFirstResult($offset);

        $statement = $selectQuery->execute();

        return [
            'count' => $count,
            'rows' => $statement->fetchAll(FetchMode::ASSOCIATIVE),
        ];
    }

    /**
     * Returns total results count for $criterion and $sortClauses.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param array $languageFilter
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    private function getTotalCount(Criterion $criterion, array $languageFilter): int
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select($this->dbPlatform->getCountExpression('*'))
            ->from('ezcontentobject_tree', 't')
            ->innerJoin(
                't',
                ContentGateway::CONTENT_ITEM_TABLE,
                'c',
                't.contentobject_id = c.id'
            )
            ->innerJoin(
                'c',
                ContentGateway::CONTENT_VERSION_TABLE,
                'v',
                'c.id = v.contentobject_id'
            );

        $query->where(
            $this->criteriaConverter->convertCriteria($query, $criterion, $languageFilter),
            $query->expr()->eq(
                'c.status',
                //ContentInfo::STATUS_PUBLISHED
                $query->createNamedParameter(1, ParameterType::INTEGER)
            ),
            $query->expr()->eq(
                'v.status',
                //VersionInfo::STATUS_PUBLISHED
                $query->createNamedParameter(1, ParameterType::INTEGER)
            ),
            $query->expr()->neq(
                't.depth',
                $query->createNamedParameter(0, ParameterType::INTEGER)
            )
        );

        // If not main-languages query
        if (!empty($languageFilter['languages'])) {
            $query->andWhere(
                $query->expr()->gt(
                    $this->dbPlatform->getBitAndComparisonExpression(
                        'c.language_mask',
                        $query->createNamedParameter(
                            $this->getLanguageMask($languageFilter),
                            ParameterType::INTEGER
                        )
                    ),
                    $query->createNamedParameter(0, ParameterType::INTEGER)
                )
            );
        }

        $statement = $query->execute();

        return (int)$statement->fetchColumn();
    }

    /**
     * Generates a language mask from the given $languageFilter.
     *
     * @param array $languageFilter
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    private function getLanguageMask(array $languageFilter): int
    {
        if (!isset($languageFilter['languages'])) {
            $languageFilter['languages'] = [];
        }

        if (!isset($languageFilter['useAlwaysAvailable'])) {
            $languageFilter['useAlwaysAvailable'] = true;
        }

        $mask = 0;
        if ($languageFilter['useAlwaysAvailable']) {
            $mask |= 1;
        }

        foreach ($languageFilter['languages'] as $languageCode) {
            $mask |= $this->languageHandler->loadByLanguageCode($languageCode)->id;
        }

        return $mask;
    }
}
