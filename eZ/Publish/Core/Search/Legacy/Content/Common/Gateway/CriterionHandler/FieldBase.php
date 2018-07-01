<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;

use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;
use eZ\Publish\Core\Persistence\Database\SelectQuery;
use PDO;

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
     * Construct from handler handler.
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $dbHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $contentTypeHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Language\Handler $languageHandler
     */
    public function __construct(
        DatabaseHandler $dbHandler,
        ContentTypeHandler $contentTypeHandler,
        LanguageHandler $languageHandler
    ) {
        parent::__construct($dbHandler);

        $this->contentTypeHandler = $contentTypeHandler;
        $this->languageHandler = $languageHandler;
    }

    /**
     * Returns a field language join condition for the given $languageSettings.
     *
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param array $languageSettings
     *
     * @return string
     */
    protected function getFieldCondition(SelectQuery $query, array $languageSettings)
    {
        // 1. Use main language(s) by default
        if (empty($languageSettings['languages'])) {
            return $query->expr->gt(
                $query->expr->bitAnd(
                    $this->dbHandler->quoteColumn('initial_language_id', 'ezcontentobject'),
                    $this->dbHandler->quoteColumn('language_id', 'ezcontentobject_attribute')
                ),
                $query->bindValue(0, null, PDO::PARAM_INT)
            );
        }

        // 2. Otherwise use prioritized languages
        $leftSide = $query->expr->bitAnd(
            $query->expr->sub(
                $this->dbHandler->quoteColumn('language_mask', 'ezcontentobject'),
                $query->expr->bitAnd(
                    $this->dbHandler->quoteColumn('language_mask', 'ezcontentobject'),
                    $this->dbHandler->quoteColumn('language_id', 'ezcontentobject_attribute')
                )
            ),
            $query->bindValue(1, null, PDO::PARAM_INT)
        );
        $rightSide = $query->expr->bitAnd(
            $this->dbHandler->quoteColumn('language_id', 'ezcontentobject_attribute'),
            $query->bindValue(1, null, PDO::PARAM_INT)
        );

        for ($index = count($languageSettings['languages']) - 1, $multiplier = 2; $index >= 0; $index--, $multiplier *= 2) {
            $languageId = $this->languageHandler
                ->loadByLanguageCode($languageSettings['languages'][$index])->id;

            $addToLeftSide = $query->expr->bitAnd(
                $query->expr->sub(
                    $this->dbHandler->quoteColumn('language_mask', 'ezcontentobject'),
                    $query->expr->bitAnd(
                        $this->dbHandler->quoteColumn('language_mask', 'ezcontentobject'),
                        $this->dbHandler->quoteColumn('language_id', 'ezcontentobject_attribute')
                    )
                ),
                $languageId
            );
            $addToRightSide = $query->expr->bitAnd(
                $this->dbHandler->quoteColumn('language_id', 'ezcontentobject_attribute'),
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

            $leftSide = $query->expr->add($leftSide, "($addToLeftSide)");
            $rightSide = $query->expr->add($rightSide, "($addToRightSide)");
        }

        return $query->expr->lAnd(
            $query->expr->gt(
                $query->expr->bitAnd(
                    $this->dbHandler->quoteColumn('language_mask', 'ezcontentobject'),
                    $this->dbHandler->quoteColumn('language_id', 'ezcontentobject_attribute')
                ),
                $query->bindValue(0, null, PDO::PARAM_INT)
            ),
            $query->expr->lt($leftSide, $rightSide)
        );
    }
}
