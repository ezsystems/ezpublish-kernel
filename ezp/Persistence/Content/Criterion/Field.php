<?php
/**
 * File containing the ezp\Persistence\Content\Criterion\Field class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Content\Criterion;
use ezp\Persistence\Content\Criterion,
    ezp\Persistence\Content\Interfaces\Criterion as CriterionInterface;

/**
 */
class Field extends Criterion implements CriterionInterface
{
    /**
     * Creates a new Field Criterion.
     *
     * Matches $fieldIdentifier against $value using $operator
     *
     * @param string $target The target field
     * @param string $operato The match operator
     * @param mixed $matchValue The value to match against
     */
    public function __construct( $field, $operator, $value )
    {
        parent::__construct( $field, $operator, $value );
    }

    public function getSpecifications()
    {
        return array(
            new OperatorSpecifications( Operator::IN, OperatorSpecifications::FORMAT_ARRAY ),
            new OperatorSpecifications( Operator::EQ, OperatorSpecifications::FORMAT_SINGLE ),
            new OperatorSpecifications( Operator::GT, OperatorSpecifications::FORMAT_SINGLE ),
            new OperatorSpecifications( Operator::GTE, OperatorSpecifications::FORMAT_SINGLE ),
            new OperatorSpecifications( Operator::LT, OperatorSpecifications::FORMAT_SINGLE ),
            new OperatorSpecifications( Operator::LTE, OperatorSpecifications::FORMAT_SINGLE ),
            new OperatorSpecifications( Operator::LIKE, OperatorSpecifications::FORMAT_SINGLE ),
            new OperatorSpecifications( Operator::BETWEEN, OperatorSpecifications::FORMAT_ARRAY, null, 2 ),
        );
    }
}
?>
