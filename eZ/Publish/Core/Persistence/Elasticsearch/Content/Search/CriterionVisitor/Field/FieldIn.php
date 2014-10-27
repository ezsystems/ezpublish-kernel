<?php
/**
 * File containing the FieldIn Field criterion visitor class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitor\Field;

use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitorDispatcher as Dispatcher;
use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitor;
use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitor\Field;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

/**
 * Visits the Field criterion with IN or EQ operator.
 */
class FieldIn extends Field
{
    /**
     * Check if visitor is applicable to current criterion
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return boolean
     */
    public function canVisit( Criterion $criterion )
    {
        return
            $criterion instanceof Criterion\Field &&
            (
                ( $criterion->operator ?: Operator::IN ) === Operator::IN ||
                $criterion->operator === Operator::EQ ||
                $criterion->operator === Operator::CONTAINS
            );
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
    protected function getCondition( Criterion $criterion )
    {
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion\Field $criterion */
        $fieldTypes = $this->getFieldTypes( $criterion );

        $values = (array)$criterion->value;

        if ( !isset( $fieldTypes[$criterion->target] ) )
        {
            throw new InvalidArgumentException(
                "\$criterion->target",
                "No searchable fields found for the given criterion target '{$criterion->target}'."
            );
        }

        $terms = array();
        foreach ( $fieldTypes[$criterion->target] as $type => $names )
        {
            // TODO possibly we'll need to dispatch by $type, need more tests
            $fields = array();

            foreach ( $names as $name )
            {
                $fields[] = "fields_doc." . $name;
            }

            foreach ( $values as $value )
            {
                $terms[] = array(
                    "multi_match" => array(
                        "query" => $value,
                        "fields" => $fields,
                    ),
                );
            }
        }

        return array(
            "bool" => array(
                "should" => $terms,
                "minimum_should_match" => 1,
            ),
        );
    }

    /**
     * Map field value to a proper Elasticsearch filter representation
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If no searchable fields are found for the given criterion target.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitorDispatcher $dispatcher
     * @param array $fieldFilters
     *
     * @return mixed
     */
    public function visitFilter( Criterion $criterion, Dispatcher $dispatcher, array $fieldFilters )
    {
        $filter = array(
            "nested" => array(
                "path" => "fields_doc",
                "filter" => array(
                    "query" => $this->getCondition( $criterion ),
                ),
            ),
        );

        $fieldFilter = $this->getFieldFilter( $fieldFilters );

        if ( $fieldFilters !== null )
        {
            $filter["nested"]["filter"] = array(
                "bool" => array(
                    "must" => array(
                        $fieldFilter,
                        $filter["nested"]["filter"],
                    ),
                ),
            );
        }

        return $filter;
    }

    /**
     * Map field value to a proper Elasticsearch query representation
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If no searchable fields are found for the given criterion target.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitorDispatcher $dispatcher
     * @param array $fieldFilters
     *
     * @return mixed
     */
    public function visitQuery( Criterion $criterion, Dispatcher $dispatcher, array $fieldFilters )
    {
        $fieldFilter = $this->getFieldFilter( $fieldFilters );

        if ( $fieldFilter === null )
        {
            $query = array(
                "nested" => array(
                    "path" => "fields_doc",
                    "query" => $this->getCondition( $criterion ),
                ),
            );
        }
        else
        {
            $query = array(
                "nested" => array(
                    "path" => "fields_doc",
                    "query" => array(
                        "filtered" => array(
                            "query" => $this->getCondition( $criterion ),
                            "filter" => $fieldFilter,
                        ),
                    ),
                ),
            );
        }

        return $query;
    }
}
