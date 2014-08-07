<?php
/**
 * File containing the FieldIn Field criterion visitor class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitor\Field;

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
     * Map field value to a proper Elasticsearch representation
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException If no searchable fields are found for the given criterion target.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     * @param \eZ\Publish\Core\Persistence\Elasticsearch\Content\Search\CriterionVisitor $subVisitor
     *
     * @return string
     */
    public function visit( Criterion $criterion, CriterionVisitor $subVisitor = null )
    {
        /** @var \eZ\Publish\API\Repository\Values\Content\Query\Criterion\Field $criterion */
        $fieldTypes = $this->getFieldTypes( $criterion );

        $criterion->value = (array)$criterion->value;

        if ( !isset( $fieldTypes[$criterion->target] ) )
        {
            throw new InvalidArgumentException(
                "\$criterion->target",
                "No searchable fields found for the given criterion target '{$criterion->target}'."
            );
        }

        $terms = array();
        foreach ( $fieldTypes[$criterion->target] as $names )
        {
            foreach ( $names as $name )
            {
                if ( count( $criterion->value ) > 1 )
                {
                    $term = array(
                        "terms" => array(
                            "fields_doc.". $name => $criterion->value,
                            //"execution" => "bool",
                        ),
                    );
                }
                else
                {
                    $term = array(
                        "term" => array(
                            "fields_doc.". $name => reset( $criterion->value ),
                        ),
                    );
                }

                $terms[] = $term;
            }
        }

        return array(
            "nested" => array(
                "path" => "fields_doc",
                "filter" => array(
                    "or" => $terms,
                ),
            ),
        );
    }
}
