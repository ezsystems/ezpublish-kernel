<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion\Depth class.
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
 * The Depth Criterion class.
 *
 * Provides content filtering based on Location depth
 */
class Depth extends Criterion implements CriterionInterface
{
    /**
     * Creates a new Depth criterion
     *
     * @throws \InvalidArgumentException if a non numeric id is given
     * @throws \InvalidArgumentException if the value type doesn't match the operator
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
            new Specifications(
                Operator::IN,
                Specifications::FORMAT_ARRAY,
                Specifications::TYPE_INTEGER
            ),
            new Specifications(
                Operator::EQ,
                Specifications::FORMAT_SINGLE,
                Specifications::TYPE_INTEGER
            ),
            new Specifications(
                Operator::GT,
                Specifications::FORMAT_SINGLE,
                Specifications::TYPE_INTEGER
            ),
            new Specifications(
                Operator::GTE,
                Specifications::FORMAT_SINGLE,
                Specifications::TYPE_INTEGER
            ),
            new Specifications(
                Operator::LT,
                Specifications::FORMAT_SINGLE,
                Specifications::TYPE_INTEGER
            ),
            new Specifications(
                Operator::LTE,
                Specifications::FORMAT_SINGLE,
                Specifications::TYPE_INTEGER
            ),
            new Specifications(
                Operator::BETWEEN,
                Specifications::FORMAT_ARRAY,
                Specifications::TYPE_INTEGER,
                2
            ),
        );
    }
}
