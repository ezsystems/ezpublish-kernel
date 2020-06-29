<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Filter\CriterionQueryBuilder\User;

use eZ\Publish\Core\FieldType\User\UserStorage\Gateway\DoctrineStorage;
use eZ\Publish\Core\Persistence\TransformationProcessor;
use eZ\Publish\SPI\Persistence\Filter\Doctrine\FilteringQueryBuilder;
use eZ\Publish\SPI\Repository\Values\Filter\CriterionQueryBuilder;
use eZ\Publish\SPI\Repository\Values\Filter\FilteringCriterion;

/**
 * @internal for internal use by Repository Filtering
 */
abstract class BaseUserCriterionQueryBuilder implements CriterionQueryBuilder
{
    /** @var \eZ\Publish\Core\Persistence\TransformationProcessor */
    private $transformationProcessor;

    public function __construct(TransformationProcessor $transformationProcessor)
    {
        $this->transformationProcessor = $transformationProcessor;
    }

    public function buildQueryConstraint(
        FilteringQueryBuilder $queryBuilder,
        FilteringCriterion $criterion
    ): ?string {
        $queryBuilder
            ->joinOnce(
                'content',
                DoctrineStorage::USER_TABLE,
                'user_storage',
                'content.id = user_storage.contentobject_id'
            );

        return null;
    }

    protected function transformCriterionValueForLikeExpression(string $value): string
    {
        return str_replace(
            '*',
            '%',
            addcslashes(
                $this->transformationProcessor->transformByGroup(
                    $value,
                    'lowercase'
                ),
                '%_'
            )
        );
    }
}
