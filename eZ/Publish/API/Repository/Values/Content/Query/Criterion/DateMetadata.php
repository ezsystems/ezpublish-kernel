<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\Query\Criterion\DateMetadata class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\Content\Query\Criterion;

use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator\Specifications;
use eZ\Publish\API\Repository\Values\Content\Query\CriterionInterface;
use InvalidArgumentException;

/**
 * A criterion that matches content based on one of the date metadata (created or modified)
 *
 * Supported Operators:
 * EQ, IN: matches content whose date is or belongs to a list of timestamps
 * GT, GTE: matches content whose date is greater than/greater than or equals the given timestamp
 * LT, LTE: matches content whose date is lower than/lower than or equals the given timestamp
 * BETWEEN: matches content whose date is between (included) the TWO given timestamps
 *
 * Example:
 * <code>
 * $createdCriterion = new Criterion\DateMetadata(
 *     Criterion\DateMetadata::CREATED,
 *     Operator::GTE,
 *     strtotime( 'yesterday' )
 * );
 * </code>
 */
class DateMetadata extends Criterion implements CriterionInterface
{
    /**
     * DateMetadata target: modification date
     */
    const MODIFIED = 'modified';

    /**
     * DateMetadata target: creation date
     */
    const CREATED = 'created';

    /**
     * Creates a new DateMetadata criterion on $metadata
     *
     * @throws \InvalidArgumentException If target is unknown
     *
     * @param string $target One of DateMetadata::CREATED or DateMetadata::MODIFIED
     * @param string $operator One of the Operator constants
     * @param mixed $value The match value, either as an array of as a single value, depending on the operator
     */
    public function __construct( $target, $operator, $value )
    {
        if ( $target != self::MODIFIED && $target != self::CREATED )
        {
            throw new InvalidArgumentException( "Unknown DateMetadata $target" );
        }
        parent::__construct( $target, $operator, $value );
    }

    public function getSpecifications()
    {
        return array(
            new Specifications(
                Operator::EQ, Specifications::FORMAT_SINGLE, Specifications::TYPE_INTEGER
            ),
            new Specifications(
                Operator::GT, Specifications::FORMAT_SINGLE, Specifications::TYPE_INTEGER
            ),
            new Specifications(
                Operator::GTE, Specifications::FORMAT_SINGLE, Specifications::TYPE_INTEGER
            ),
            new Specifications(
                Operator::LT, Specifications::FORMAT_SINGLE, Specifications::TYPE_INTEGER
            ),
            new Specifications(
                Operator::LTE, Specifications::FORMAT_SINGLE, Specifications::TYPE_INTEGER
            ),
            new Specifications(
                Operator::IN, Specifications::FORMAT_ARRAY, Specifications::TYPE_INTEGER
            ),
            new Specifications(
                Operator::BETWEEN, Specifications::FORMAT_ARRAY, Specifications::TYPE_INTEGER, 2
            ),
        );
    }
}
