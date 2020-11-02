<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests\SearchService\Aggregation;

use eZ\Publish\API\Repository\Tests\SearchService\Aggregation\DataSetBuilder\TermAggregationDataSetBuilder;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\ObjectStateTermAggregation;
use eZ\Publish\API\Repository\Values\ObjectState\ObjectState;

final class ObjectStateTermAggregationTest extends AbstractAggregationTest
{
    public function dataProviderForTestFindContentWithAggregation(): iterable
    {
        $aggregation = new ObjectStateTermAggregation('object_state', 'ez_lock');

        $builder = new TermAggregationDataSetBuilder($aggregation);
        $builder->setExpectedEntries([
            // TODO: Change the state of some content objects to have better test data
            'not_locked' => 18,
        ]);

        $builder->setEntryMapper(
            function (string $identifier): ObjectState {
                $objectStateService = $this->getRepository()->getObjectStateService();

                static $objectStateGroup = null;
                if ($objectStateGroup === null) {
                    $objectStateGroup = $objectStateService->loadObjectStateGroupByIdentifier('ez_lock');
                }

                return $objectStateService->loadObjectStateByIdentifier($objectStateGroup, $identifier);
            }
        );

        yield $builder->build();
    }
}
