<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion\Location\Priority class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion\Location;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Location;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications;
use eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface;

/**
 * A criterion that matches Location based on its priority
 *
 * Supported operators:
 * - BETWEEN: matches location whose priority is between (included) the TWO given priorities
 * - GT, GTE: matches location whose priority is greater than/greater than or equals the given priority
 * - LT, LTE: matches location whose priority is lower than/lower than or equals the given priority
 */
class Priority extends Location implements CriterionInterface
{
    /**
     * Creates a new LocationPriority criterion
     *
     * @param string $operator One of the Operator constants
     * @param mixed $value The match value, either as an array of as a single value, depending on the operator
     */
    public function __construct( $operator, $value )
    {
        parent::__construct( null, $operator, $value );
    }

    public function getSpecifications()
    {
        return array(
            new Specifications( Operator::BETWEEN, Specifications::FORMAT_ARRAY, Specifications::TYPE_INTEGER ),
            new Specifications( Operator::GT, Specifications::FORMAT_SINGLE, Specifications::TYPE_INTEGER ),
            new Specifications( Operator::GTE, Specifications::FORMAT_SINGLE, Specifications::TYPE_INTEGER ),
            new Specifications( Operator::LT, Specifications::FORMAT_SINGLE, Specifications::TYPE_INTEGER ),
            new Specifications( Operator::LTE, Specifications::FORMAT_SINGLE, Specifications::TYPE_INTEGER ),
        );
    }

    public static function createFromQueryBuilder( $target, $operator, $value )
    {
        return new self( $operator, $value );
    }
}
