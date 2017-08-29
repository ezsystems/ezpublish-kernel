<?php

/**
 * File containing the abstract CustomField criterion visitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitor;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion\CriterionInterface;
use eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitorDispatcher as Dispatcher;

/**
 * Base class for CustomField criterion visitors.
 */
abstract class CustomField extends FieldFilterBase
{
    /**
     * Returns nested condition common for filter and query contexts.
     *
     * @param CriterionInterface $criterion
     *
     * @return array
     */
    abstract protected function getCondition(CriterionInterface $criterion);

    /**
     * Map field value to a proper Elasticsearch query representation.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If no searchable fields are found for the given criterion target.
     *
     * @param CriterionInterface $criterion
     * @param \eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitorDispatcher $dispatcher
     * @param array $languageFilter
     *
     * @return mixed
     */
    public function visitQuery(CriterionInterface $criterion, Dispatcher $dispatcher, array $languageFilter)
    {
        $query = array(
            'bool' => array(
                'should' => $this->getCondition($criterion),
                'minimum_should_match' => 1,
            ),
        );

        $fieldFilter = $this->getFieldFilter($languageFilter);

        if ($fieldFilter === null) {
            $query = array(
                'nested' => array(
                    'path' => 'fields_doc',
                    'query' => $query,
                ),
            );
        } else {
            $query = array(
                'nested' => array(
                    'path' => 'fields_doc',
                    'query' => array(
                        'filtered' => array(
                            'query' => $query,
                            'filter' => $fieldFilter,
                        ),
                    ),
                ),
            );
        }

        return $query;
    }
}
