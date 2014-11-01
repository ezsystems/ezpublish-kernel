<?php
/**
 * File containing the FullText criterion visitor class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitor;

use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitorDispatcher as Dispatcher;
use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitor;
use eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FieldMap;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\CustomFieldInterface;

/**
 * Visits the FullText criterion
 */
class FullText extends FieldFilterBase
{
    /**
     * Field map
     *
     * @var \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FieldMap
     */
    protected $fieldMap;

    /**
     * Create from field map
     *
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\FieldMap $fieldMap
     */
    public function __construct( FieldMap $fieldMap )
    {
        $this->fieldMap = $fieldMap;
    }

    /**
     * Get field type information
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\CustomFieldInterface $criterion
     * @return array
     */
    protected function getFieldTypes( CustomFieldInterface $criterion )
    {
        return $this->fieldMap->getFieldTypes( $criterion );
    }

    /**
     * Check if visitor is applicable to current criterion
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return boolean
     */
    public function canVisit( Criterion $criterion )
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
    protected function getCondition( Criterion $criterion )
    {
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion\FullText $criterion */
        $fields = $this->getFieldTypes( $criterion );

        // Add field document custom _all field
        $queryFields = array(
            "fields_doc.meta_all_*",
        );

        // Add boosted fields if any
        foreach ( $criterion->boost as $field => $boost )
        {
            if ( !isset( $fields[$field] ) )
            {
                continue;
            }

            foreach ( $fields[$field] as $fieldNames )
            {
                foreach ( $fieldNames as $fieldName )
                {
                    $queryFields[] = sprintf( "fields_doc.{$fieldName}^%.1f", $boost );
                }
            }
        }

        $condition = array(
            "query_string" => array(
                "query" => $criterion->value . ( $criterion->fuzziness < 1 ? "~" : "" ),
                "fields" => $queryFields,
                "fuzziness" => $criterion->fuzziness,
                // This one will be heavy, enabled per FullText criterion spec
                "allow_leading_wildcard" => true,
                // Might make sense to use percentage in addition
                "minimum_should_match" => 1,
                // Default is OR, changed per FullText criterion spec
                "default_operator" => "AND",
            ),
        );

        return $condition;
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
