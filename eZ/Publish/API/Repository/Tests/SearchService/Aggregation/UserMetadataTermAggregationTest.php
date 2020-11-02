<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests\SearchService\Aggregation;

use eZ\Publish\API\Repository\Tests\SearchService\Aggregation\DataSetBuilder\TermAggregationDataSetBuilder;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\UserMetadataTermAggregation;

final class UserMetadataTermAggregationTest extends AbstractAggregationTest
{
    public function dataProviderForTestFindContentWithAggregation(): iterable
    {
        yield '::OWNER' => $this->createOwnerTermAggregationDataSet();
        yield '::GROUP' => $this->createGroupTermAggregationDataSet();
        yield '::MODIFIER' => $this->createModifierTermAggregationDataSet();
    }

    public function dataProviderForTestFindLocationWithAggregation(): iterable
    {
        yield from $this->dataProviderForTestFindContentWithAggregation();
    }

    private function createOwnerTermAggregationDataSet(): array
    {
        $aggregation = new UserMetadataTermAggregation('owner', UserMetadataTermAggregation::OWNER);

        $builder = new TermAggregationDataSetBuilder($aggregation);
        $builder->setExpectedEntries(['admin' => 18]);
        $builder->setEntryMapper([$this->getRepository()->getUserService(), 'loadUserByLogin']);

        return $builder->build();
    }

    private function createGroupTermAggregationDataSet(): array
    {
        $aggregation = new UserMetadataTermAggregation('user_group', UserMetadataTermAggregation::GROUP);

        $builder = new TermAggregationDataSetBuilder($aggregation);
        $builder->setExpectedEntries([
            12 => 18,
            14 => 18,
            4 => 18,
        ]);
        $builder->setEntryMapper([$this->getRepository()->getUserService(), 'loadUserGroup']);

        return $builder->build();
    }

    private function createModifierTermAggregationDataSet(): array
    {
        $aggregation = new UserMetadataTermAggregation('modifier', UserMetadataTermAggregation::MODIFIER);

        $builder = new TermAggregationDataSetBuilder($aggregation);
        $builder->setExpectedEntries(['admin' => 18]);
        $builder->setEntryMapper([$this->getRepository()->getUserService(), 'loadUserByLogin']);

        return $builder->build();
    }
}
