<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Content\Query\CriterionHandler;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use Doctrine\DBAL\Query\QueryBuilder;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator;
use eZ\Publish\Core\Persistence\Legacy\Content\Query\CriteriaConverter;
use eZ\Publish\Core\Persistence\Legacy\Content\Query\CriterionHandler;

class LanguageCode implements CriterionHandler
{
    /** @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator */
    private $maskGenerator;

    /**
     * Construct from language mask generator.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator $maskGenerator
     */
    public function __construct( MaskGenerator $maskGenerator)
    {
        $this->maskGenerator = $maskGenerator;
    }


    /**
     * {@inheritdoc}
     */
    public function accept(Criterion $criterion): bool
    {
        return $criterion instanceof Criterion\LanguageCode;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(CriteriaConverter $converter, QueryBuilder $query, Criterion $criterion): string
    {
        $dbPlatform = $query->getConnection()->getDatabasePlatform();
        /* @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion\LanguageCode $criterion */
        $mask = $this->maskGenerator->generateLanguageMaskFromLanguageCodes(
            $criterion->value,
            $criterion->matchAlwaysAvailable
        );

        return $query->expr()->gt(
            $dbPlatform->getBitAndComparisonExpression('content.language_mask', $mask),
            0
        );
    }
}
