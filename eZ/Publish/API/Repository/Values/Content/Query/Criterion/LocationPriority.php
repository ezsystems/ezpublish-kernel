<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion\LocationPriority class.
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
 * A criterion that matches content based on its own location priority
 *
 * Supported operators:
 * - BETWEEN: matches content whose location priority is between (included) the TWO given priorities
 * - GT, GTE: matches content whose location priority is greater than/greater than or equals the given priority
 * - LT, LTE: matches content whose location priority is lower than/lower than or equals the given priority
 */
class LocationPriority extends Criterion implements CriterionInterface
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
