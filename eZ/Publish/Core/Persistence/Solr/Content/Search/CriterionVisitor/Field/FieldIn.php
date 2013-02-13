<?php
/**
 * File containing the Content Search handler class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor\Field;

use eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor;
use eZ\Publish\Core\Persistence\Solr\Content\Search\CriterionVisitor\Field;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;

/**
 * Visits the Field criterion
 */
class FieldIn extends Field
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
            ( ( $criterion->operator ?: Operator::IN ) === Operator::IN ||
              $criterion->operator === Operator::EQ );
    }

    /**
     * Map field value to a proper Solr representation
     *
     * @param Criterion $criterion
     * @param CriterionVisitor $subVisitor
     *
     * @return void
     */
    public function visit( Criterion $criterion, CriterionVisitor $subVisitor = null )
    {
        $fieldTypes = $this->getFieldTypes();
        $criterion->value = (array)$criterion->value;

        if ( !isset( $fieldTypes[$criterion->target] ) )
        {
            throw new \OutOfBoundsException( "Content type field {$criterion->target} not found." );
        }

        $queries = array();
        foreach ( $criterion->value as $value )
        {
            foreach ( $fieldTypes[$criterion->target] as $name )
            {
                $queries[] = $name . ':"' . $value . '"';
            }
        }

        return '(' . implode( ' OR ', $queries ) . ')';
    }
}

