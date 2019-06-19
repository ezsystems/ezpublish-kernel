<?php

/**
 * File containing the abstract CustomField criterion visitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitor;

use eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitorDispatcher as Dispatcher;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * Base class for CustomField criterion visitors.
 */
abstract class CustomField extends FieldFilterBase
{
    /**
     * Returns nested condition common for filter and query contexts.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return array
     */
    abstract protected function getCondition(Criterion $criterion);

    /**
     * Map field value to a proper Elasticsearch query representation.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If no searchable fields are found for the given criterion target.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitorDispatcher $dispatcher
     * @param array $languageFilter
     *
     * @return mixed
     */
    public function visitQuery(Criterion $criterion, Dispatcher $dispatcher, array $languageFilter)
    {
        $query = [
            'bool' => [
                'should' => $this->getCondition($criterion),
                'minimum_should_match' => 1,
            ],
        ];

        $fieldFilter = $this->getFieldFilter($languageFilter);

        if ($fieldFilter === null) {
            $query = [
                'nested' => [
                    'path' => 'fields_doc',
                    'query' => $query,
                ],
            ];
        } else {
            $query = [
                'nested' => [
                    'path' => 'fields_doc',
                    'query' => [
                        'filtered' => [
                            'query' => $query,
                            'filter' => $fieldFilter,
                        ],
                    ],
                ],
            ];
        }

        return $query;
    }
}
