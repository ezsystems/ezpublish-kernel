<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway as LocationGateway;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use RuntimeException;

/**
 * User metadata criterion handler.
 */
class UserMetadata extends CriterionHandler
{
    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return bool
     */
    public function accept(Criterion $criterion)
    {
        return $criterion instanceof Criterion\UserMetadata;
    }

    public function handle(
        CriteriaConverter $converter,
        QueryBuilder $queryBuilder,
        Criterion $criterion,
        array $languageSettings
    ) {
        $value = (array)$criterion->value;
        switch ($criterion->target) {
            case Criterion\UserMetadata::MODIFIER:
                return $queryBuilder->expr()->in(
                    'v.creator_id',
                    $queryBuilder->createNamedParameter($value, Connection::PARAM_INT_ARRAY)
                );

            case Criterion\UserMetadata::GROUP:
                $subSelect = $this->connection->createQueryBuilder();
                $subSelect
                    ->select(
                        't1.contentobject_id'
                    )->from(
                        LocationGateway::CONTENT_TREE_TABLE, 't1'
                    )->innerJoin(
                        't1',
                        LocationGateway::CONTENT_TREE_TABLE,
                        't2',
                        $queryBuilder->expr()->like(
                            't1.path_string',
                            $this->dbPlatform->getConcatExpression(
                                't2.path_string',
                                $queryBuilder->createNamedParameter('%', ParameterType::STRING)
                            )
                        )
                    )->where(
                        $queryBuilder->expr()->in(
                            't2.contentobject_id',
                            $queryBuilder->createNamedParameter($value, Connection::PARAM_INT_ARRAY)
                        )
                    );

                return $queryBuilder->expr()->in(
                    'c.owner_id',
                    $subSelect->getSQL()
                );

            case Criterion\UserMetadata::OWNER:
                return $queryBuilder->expr()->in(
                    'c.owner_id',
                    $queryBuilder->createNamedParameter($value, Connection::PARAM_INT_ARRAY)
                );
            default:
                break;
        }

        throw new RuntimeException("Invalid target Criterion: '" . $criterion->target . "'");
    }
}
