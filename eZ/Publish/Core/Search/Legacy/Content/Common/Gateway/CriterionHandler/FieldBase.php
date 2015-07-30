<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;

use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as ContentTypeHandler;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;
use eZ\Publish\Core\Persistence\Database\SelectQuery;

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
     * Returns the bitmask for the list languages in given $fieldFilters.
     *
     * The method will return null if no languages are contained in $fieldFilters.
     * Note: 'useAlwaysAvailable' filter is ignored here.
     *
     * @param array $fieldFilters
     *
     * @return null|int
     */
    protected function getFieldFiltersLanguageMask(array $fieldFilters)
    {
        if (empty($fieldFilters['languages'])) {
            return null;
        }

        $mask = 0;

        foreach ($fieldFilters['languages'] as $languageCode) {
            $mask |= $this->languageHandler->loadByLanguageCode($languageCode)->id;
        }

        return $mask;
    }

    /**
     * Adds field filters condition to the WHERE clause of the given $query.
     *
     * Conditions are combined with existing ones using logical AND operator.
     *
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param array $fieldFilters
     */
    protected function addFieldFiltersConditions(SelectQuery $query, array $fieldFilters)
    {
        $languageMask = $this->getFieldFiltersLanguageMask($fieldFilters);

        // Only apply if languages are defined in $fieldFilters;
        // 'useAlwaysAvailable' does not make sense on its own
        if ($languageMask === null) {
            return;
        }

        // Condition for the language part of $fieldFilters
        $languageMaskExpression = $query->expr->gt(
            $query->expr->bitAnd(
                $this->dbHandler->quoteColumn('language_id', 'ezcontentobject_attribute'),
                $query->bindValue($languageMask, null, \PDO::PARAM_INT)
            ),
            $query->bindValue(0, null, \PDO::PARAM_INT)
        );

        // If 'useAlwaysAvailable' is set to true, additionally factor in condition for the
        // Content's main language that is marked as always available
        if (
            !isset($fieldFilters['useAlwaysAvailable'])
            || $fieldFilters['useAlwaysAvailable'] === true
        ) {
            $query->where(
                $query->expr->lOr(
                    // Matching on a given list of languages
                    $languageMaskExpression,
                    // Matching always available in main language
                    $query->expr->lAnd(
                        // 1. match main language - since ezcontentobject.initial_language_id column
                        // does not use first bit (always available bit), we can ignore the fact
                        // that ezcontentobject_attribute.language_id does
                        $query->expr->gt(
                            $query->expr->bitAnd(
                                $this->dbHandler->quoteColumn(
                                    'language_id',
                                    'ezcontentobject_attribute'
                                ),
                                $this->dbHandler->quoteColumn(
                                    'initial_language_id',
                                    'ezcontentobject'
                                )
                            ),
                            $query->bindValue(0, null, \PDO::PARAM_INT)
                        ),
                        // 2. ensure it is always available - ezcontentobject_attribute.language_id
                        // uses first bit (always available bit)
                        $query->expr->gt(
                            $query->expr->bitAnd(
                                $this->dbHandler->quoteColumn(
                                    'language_id',
                                    'ezcontentobject_attribute'
                                ),
                                $query->bindValue(1, null, \PDO::PARAM_INT)
                            ),
                            $query->bindValue(0, null, \PDO::PARAM_INT)
                        )
                    )
                )
            );
        } else {
            // Matching on a given list of languages
            $query->where($languageMaskExpression);
        }
    }
}
