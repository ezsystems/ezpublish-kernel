<?php
/**
 * File containing the Content Search handler class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor\Field;

use eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor;
use eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor\Field;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

/**
 * Visits the Field criterion
 */
class FieldRange extends Field
{
    /**
     * CHeck if visitor is applicable to current criterion
     *
     * @param Criterion $criterion
     *
     * @return boolean
     */
    public function canVisit( Criterion $criterion )
    {
        return
            $criterion instanceof Criterion\Field &&
            ( $criterion->operator === Operator::LT ||
              $criterion->operator === Operator::LTE ||
              $criterion->operator === Operator::GT ||
              $criterion->operator === Operator::GTE ||
              $criterion->operator === Operator::BETWEEN );
    }

    /**
     * Map field value to a proper Solr representation
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If no searchable fields are found for the given criterion target.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor $subVisitor
     * @param bool $isChildQuery
     *
     * @return string
     */
    public function visit( Criterion $criterion, CriterionVisitor $subVisitor = null, $isChildQuery = false )
    {
        $start = $criterion->value[0];
        $end   = isset( $criterion->value[1] ) ? $criterion->value[1] : null;

        if ( ( $criterion->operator === Operator::LT ) ||
             ( $criterion->operator === Operator::LTE ) )
        {
            $end = $start;
            $start = null;
        }

        $fieldTypes = $this->getFieldTypes( $criterion );
        $criterion->value = (array)$criterion->value;

        if ( !isset( $fieldTypes[$criterion->target] ) )
        {
            throw new InvalidArgumentException(
                "\$criterion->target",
                "No searchable fields found for the given criterion target '{$criterion->target}'."
            );
        }

        $queries = array();
        $childJoinString = $this->getChildJoinString( $isChildQuery );
        foreach ( $fieldTypes[$criterion->target] as $names )
        {
            foreach ( $names as $name )
            {
                $queries[] = $childJoinString . $this->getFRange( $criterion->operator, $start, $end ) . $name;
            }
        }

        return '(' . implode( ' OR ', $queries ) . ')';
    }
}

