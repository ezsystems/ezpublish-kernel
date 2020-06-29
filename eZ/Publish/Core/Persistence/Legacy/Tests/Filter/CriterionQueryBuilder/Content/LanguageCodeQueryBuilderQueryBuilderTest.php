<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Filter\CriterionQueryBuilder\Content;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\Persistence\Legacy\Filter\CriterionQueryBuilder\Content\LanguageCodeQueryBuilder;
use eZ\Publish\Core\Persistence\Legacy\Tests\Filter\BaseCriterionVisitorQueryBuilderTestCase;

/**
 * @covers \eZ\Publish\Core\Persistence\Legacy\Filter\CriterionQueryBuilder\Content\LanguageCodeQueryBuilder::buildQueryConstraint
 * @covers \eZ\Publish\Core\Persistence\Legacy\Filter\CriterionQueryBuilder\Content\LanguageCodeQueryBuilder::accepts
 */
final class LanguageCodeQueryBuilderQueryBuilderTest extends BaseCriterionVisitorQueryBuilderTestCase
{
    public function getFilteringCriteriaQueryData(): iterable
    {
        yield 'Language Code IN (eng-GB, eng-US), match always available' => [
            new Criterion\LanguageCode(['eng-GB', 'eng-US']),
            '(language.locale IN (:dcValue1)) OR (version.language_mask & 1 = 1)',
            ['dcValue1' => ['eng-GB', 'eng-US']],
        ];

        yield 'Language Code=pol-PL, don\'t match always available' => [
            new Criterion\LanguageCode('pol-PL', false),
            'language.locale IN (:dcValue1)',
            ['dcValue1' => ['pol-PL']],
        ];
    }

    protected function getCriterionQueryBuilders(): iterable
    {
        return [new LanguageCodeQueryBuilder()];
    }
}
