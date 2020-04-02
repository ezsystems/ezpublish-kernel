<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriterionHandler;
use eZ\Publish\Core\Search\Legacy\Content\Common\Gateway\CriteriaConverter;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator;

/**
 * LanguageCode criterion handler.
 */
class LanguageCode extends CriterionHandler
{
    /** @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator */
    private $maskGenerator;

    public function __construct(Connection $connection, MaskGenerator $maskGenerator)
    {
        parent::__construct($connection);

        $this->maskGenerator = $maskGenerator;
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

    public function handle(
        CriteriaConverter $converter,
        QueryBuilder $queryBuilder,
        Criterion $criterion,
        array $languageSettings
    ) {
        /* @var $criterion \eZ\Publish\API\Repository\Values\Content\Query\Criterion\LanguageCode */
        return $queryBuilder->expr()->gt(
            $this->dbPlatform->getBitAndComparisonExpression(
                'c.language_mask',
                $this->maskGenerator->generateLanguageMaskFromLanguageCodes(
                    $criterion->value,
                    $criterion->matchAlwaysAvailable
                )
            ),
            0
        );
    }
}
