<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;

/**
 * Base criterion handler for field criteria.
 */
abstract class FieldBase extends CriterionHandler
{
    /**
     * Content Type handler.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    protected $contentTypeHandler;

    /**
     * Language handler.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Language\Handler
     */
    protected $languageHandler;

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __construct(
        Connection $connection,
        ContentTypeHandler $contentTypeHandler,
        LanguageHandler $languageHandler
    ) {
        parent::__construct($connection);

        $this->contentTypeHandler = $contentTypeHandler;
        $this->languageHandler = $languageHandler;
    }

    /**
     * Returns a field language join condition for the given $languageSettings.
     *
     * @param array $languageSettings
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    protected function getFieldCondition(QueryBuilder $query, array $languageSettings): string
    {
        // 1. Use main language(s) by default
        $expr = $query->expr();
        if (empty($languageSettings['languages'])) {
            return $expr->gt(
                $this->dbPlatform->getBitAndComparisonExpression(
                    'c.initial_language_id',
                    'f_def.language_id'
                ),
                $query->createNamedParameter(0, ParameterType::INTEGER)
            );
        }

        // 2. Otherwise use prioritized languages
        $leftSide = $this->dbPlatform->getBitAndComparisonExpression(
            sprintf(
                'c.language_mask - %s',
                $this->dbPlatform->getBitAndComparisonExpression(
                    'c.language_mask',
                    'f_def.language_id'
                )
            ),
            $query->createNamedParameter(1, ParameterType::INTEGER)
        );
        $rightSide = $this->dbPlatform->getBitAndComparisonExpression(
            'f_def.language_id',
            $query->createNamedParameter(1, ParameterType::INTEGER)
        );

        for (
            $index = count($languageSettings['languages']) - 1,
            $multiplier = 2;
            $index >= 0;
            $index--, $multiplier *= 2
        ) {
            $languageId = $this->languageHandler
                ->loadByLanguageCode($languageSettings['languages'][$index])->id;

            $addToLeftSide = $this->dbPlatform->getBitAndComparisonExpression(
                sprintf(
                    'c.language_mask - %s',
                    $this->dbPlatform->getBitAndComparisonExpression(
                        'c.language_mask',
                        'f_def.language_id'
                    )
                ),
                $languageId
            );
            $addToRightSide = $this->dbPlatform->getBitAndComparisonExpression(
                'f_def.language_id',
                $languageId
            );

            if ($multiplier > $languageId) {
                $factor = $multiplier / $languageId;
                for ($shift = 0; $factor > 1; $factor = $factor / 2, $shift++);
                $factorTerm = ' << ' . $shift;
                $addToLeftSide .= $factorTerm;
                $addToRightSide .= $factorTerm;
            } elseif ($multiplier < $languageId) {
                $factor = $languageId / $multiplier;
                for ($shift = 0; $factor > 1; $factor = $factor / 2, $shift++);
                $factorTerm = ' >> ' . $shift;
                $addToLeftSide .= $factorTerm;
                $addToRightSide .= $factorTerm;
            }

            $leftSide = "$leftSide + ($addToLeftSide)";
            $rightSide = "$rightSide + ($addToRightSide)";
        }

        return $expr->andX(
            $expr->gt(
                $this->dbPlatform->getBitAndComparisonExpression(
                    'c.language_mask',
                    'f_def.language_id'
                ),
                $query->createNamedParameter(0, ParameterType::INTEGER)
            ),
            $expr->lt($leftSide, $rightSide)
        );
    }

    /**
     * @param array $languageSettings
     * @param array $fieldWhereExpressions
     * @param array $fieldsInformation
     *
     * @return string
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotImplementedException
     */
    protected function getInExpressionWithFieldConditions(
        QueryBuilder $query,
        QueryBuilder $subSelect,
        array $languageSettings,
        array $fieldWhereExpressions,
        array $fieldsInformation
    ): string {
        if (empty($fieldWhereExpressions)) {
            throw new NotImplementedException(
                sprintf(
                    'The following Field Types are not searchable in the Legacy search engine: %s',
                    implode(', ', array_keys($fieldsInformation))
                )
            );
        }

        $expr = $subSelect->expr();
        $subSelect->where(
            $expr->andX(
                'f_def.version = c.current_version',
                $expr->orX(...$fieldWhereExpressions),
                // pass main Query Builder to set query parameters
                $this->getFieldCondition($query, $languageSettings)
            )
        );

        return $query->expr()->in(
            'c.id',
            $subSelect->getSQL()
        );
    }
}
