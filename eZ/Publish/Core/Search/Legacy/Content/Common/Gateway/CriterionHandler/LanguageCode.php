<?php

/**
 * File containing the DoctrineDatabase language code criterion handler class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;

use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Database\DatabaseHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator;
use eZ\Publish\Core\Persistence\Database\SelectQuery;

/**
 * LanguageCode criterion handler.
 */
class LanguageCode extends CriterionHandler
{
    /** @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator */
    private $maskGenerator;

    /**
     * Construct from language mask generator.
     *
     * @param \eZ\Publish\Core\Persistence\Database\DatabaseHandler $dbHandler
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator $maskGenerator
     */
    public function __construct(DatabaseHandler $dbHandler, MaskGenerator $maskGenerator)
    {
        $this->maskGenerator = $maskGenerator;
        parent::__construct($dbHandler);
    }

    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return bool
     */
    public function accept(Criterion $criterion)
    {
        return $criterion instanceof Criterion\LanguageCode;
    }

    /**
     * Generate query expression for a Criterion this handler accepts.
     *
     * accept() must be called before calling this method.
     *
     * @param \eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter $converter
     * @param \eZ\Publish\Core\Persistence\Database\SelectQuery $query
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param array $languageSettings
     *
     * @return \eZ\Publish\Core\Persistence\Database\Expression
     */
    public function handle(
        CriteriaConverter $converter,
        SelectQuery $query,
        Criterion $criterion,
        array $languageSettings
    ) {
        /* @var $criterion \eZ\Publish\API\Repository\Values\Content\Query\Criterion\LanguageCode */
        return $query->expr->gt(
            $query->expr->bitAnd(
                $this->dbHandler->quoteColumn('language_mask', 'ezcontentobject'),
                $this->maskGenerator->generateLanguageMaskFromLanguageCodes(
                    $criterion->value,
                    $criterion->matchAlwaysAvailable
                )
            ),
            0
        );
    }
}
