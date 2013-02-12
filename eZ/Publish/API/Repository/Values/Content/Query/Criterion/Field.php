<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion\Field class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications;
use eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface;

/**
 * The Field Criterion class.
 *
 * Provides content filtering based on Fields contents & values.
 */
class Field extends Criterion implements CriterionInterface
{
    /**
     * Creates a new Field Criterion.
     *
     * Matches $fieldIdentifier against $value using $operator.
     * $fieldIdentifier is same value as eZ\Publish\API\Repository\Values\Content\Field->fieldDefIdentifier
     *
     * @param string $fieldIdentifier The target field identifier
     * @param string $operator The match operator
     * @param mixed $value The value to match against
     */
    public function __construct( $fieldIdentifier, $operator, $value )
    {
        parent::__construct( $fieldIdentifier, $operator, $value );
    }

    public function getSpecifications()
    {
        return array(
            new Specifications( Operator::IN, Specifications::FORMAT_ARRAY ),
            new Specifications( Operator::EQ, Specifications::FORMAT_SINGLE ),
            new Specifications( Operator::GT, Specifications::FORMAT_SINGLE ),
            new Specifications( Operator::GTE, Specifications::FORMAT_SINGLE ),
            new Specifications( Operator::LT, Specifications::FORMAT_SINGLE ),
            new Specifications( Operator::LTE, Specifications::FORMAT_SINGLE ),
            new Specifications( Operator::LIKE, Specifications::FORMAT_SINGLE ),
            new Specifications( Operator::BETWEEN, Specifications::FORMAT_ARRAY, null, 2 ),
        );
    }
}
