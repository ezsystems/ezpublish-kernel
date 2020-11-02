<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests\SearchService\Aggregation;

use eZ\Publish\API\Repository\Tests\SearchService\Aggregation\DataSetBuilder\TermAggregationDataSetBuilder;
use eZ\Publish\API\Repository\Values\Content\Query\Aggregation\ContentTypeGroupTermAggregation;

final class ContentTypeGroupTermAggregationTest extends AbstractAggregationTest
{
    public function dataProviderForTestFindContentWithAggregation(): iterable
    {
        $aggregation = new ContentTypeGroupTermAggregation('content_type_group');

        $builder = new TermAggregationDataSetBuilder($aggregation);
        $builder->setExpectedEntries([
            'Content' => 8,
            'Users' => 8,
            'Setup' => 2,
        ]);

        $builder->setEntryMapper([
            $this->getRepository()->getContentTypeService(),
            'loadContentTypeGroupByIdentifier',
        ]);

        yield $builder->build();
    }
}
