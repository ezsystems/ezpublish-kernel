<?php

/**
 * File containing the FullText criterion visitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitor;

use eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitorDispatcher as Dispatcher;
use eZ\Publish\Core\Search\Common\FieldNameResolver;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * Visits the FullText criterion.
 */
class FullText extends FieldFilterBase
{
    /**
     * Field map.
     *
     * @var \eZ\Publish\Core\Search\Common\FieldNameResolver
     */
    protected $fieldNameResolver;

    /**
     * Create from field map.
     *
     * @param \eZ\Publish\Core\Search\Common\FieldNameResolver $fieldNameResolver
     */
    public function __construct(FieldNameResolver $fieldNameResolver)
    {
        $this->fieldNameResolver = $fieldNameResolver;
    }

    /**
     * Get field type information.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param string $fieldDefinitionIdentifier
     *
     * @return array
     */
    protected function getFieldNames(Criterion $criterion, $fieldDefinitionIdentifier)
    {
        return $this->fieldNameResolver->getFieldNames($criterion, $fieldDefinitionIdentifier);
    }

    /**
     * Check if visitor is applicable to current criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return bool
     */
    public function canVisit(Criterion $criterion)
    {
        return $criterion instanceof Criterion\FullText;
    }

    /**
     * Returns nested condition common for filter and query contexts.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If no searchable fields are found for the given criterion target.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return array
     */
    protected function getCondition(Criterion $criterion)
    {
        // Add field document custom _all field
        $queryFields = array(
            'fields_doc.meta_all_*',
        );

        // Add boosted fields if any
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion\FullText $criterion */
        foreach ($criterion->boost as $field => $boost) {
            $fieldNames = $this->getFieldNames($criterion, $field);

            foreach ($fieldNames as $fieldName) {
                $queryFields[] = sprintf("fields_doc.{$fieldName}^%.1f", $boost);
            }
        }

        $condition = array(
            'query_string' => array(
                'query' => $criterion->value . ($criterion->fuzziness < 1 ? '~' : ''),
                'fields' => $queryFields,
                'fuzziness' => $criterion->fuzziness,
                // This one will be heavy, enabled per FullText criterion spec
                'allow_leading_wildcard' => true,
                // Might make sense to use percentage in addition
                'minimum_should_match' => 1,
                // Default is OR, changed per FullText criterion spec
                'default_operator' => 'AND',
            ),
        );

        return $condition;
    }

    /**
     * Map field value to a proper Elasticsearch filter representation.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If no searchable fields are found for the given criterion target.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \eZ\Publish\Core\Search\Elasticsearch\Content\CriterionVisitorDispatcher $dispatcher
     * @param array $languageFilter
     *
     * @return mixed
     */
    public function visitFilter(Criterion $criterion, Dispatcher $dispatcher, array $languageFilter)
    {
        $filter = array(
            'nested' => array(
                'path' => 'fields_doc',
                'filter' => array(
                    'query' => $this->getCondition($criterion),
                ),
            ),
        );

        $fieldFilter = $this->getFieldFilter($languageFilter);

        if ($languageFilter !== null) {
            $filter['nested']['filter'] = array(
                'bool' => array(
                    'must' => array(
                        $fieldFilter,
                        $filter['nested']['filter'],
                    ),
                ),
            );
        }

        return $filter;
    }

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
        $fieldFilter = $this->getFieldFilter($languageFilter);

        if ($fieldFilter === null) {
            $query = array(
                'nested' => array(
                    'path' => 'fields_doc',
                    'query' => $this->getCondition($criterion),
                ),
            );
        } else {
            $query = array(
                'nested' => array(
                    'path' => 'fields_doc',
                    'query' => array(
                        'filtered' => array(
                            'query' => $this->getCondition($criterion),
                            'filter' => $fieldFilter,
                        ),
                    ),
                ),
            );
        }

        return $query;
    }
}
