<?php
/**
 * File containing the DateMetadata class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Content\Criterion;
use ezp\Persistence\Content\Criterion,
    ezp\Persistence\Content\Interfaces\Criterion as CriterionInterface,
    InvalidArgumentException;

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
 *
 */
class DateMetadata extends Criterion implements CriterionInterface
{
    /**
     * Creates a new DateMetadata criterion on $metadata
     *
     * @param string $target One of DateMetadata::CREATED or DateMetadata::MODIFIED
     * @param string $operator One of the Operator constants
     * @param mixed $value The match value, either as an array of as a single value, depending on the operator*
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
            new OperatorSpecifications(
                Operator::EQ, OperatorSpecifications::FORMAT_SINGLE, OperatorSpecifications::TYPE_INTEGER
            ),
            new OperatorSpecifications(
                Operator::GT, OperatorSpecifications::FORMAT_SINGLE, OperatorSpecifications::TYPE_INTEGER
            ),
            new OperatorSpecifications(
                Operator::GTE, OperatorSpecifications::FORMAT_SINGLE, OperatorSpecifications::TYPE_INTEGER
            ),
            new OperatorSpecifications(
                Operator::LT, OperatorSpecifications::FORMAT_SINGLE, OperatorSpecifications::TYPE_INTEGER
            ),
            new OperatorSpecifications(
                Operator::LTE, OperatorSpecifications::FORMAT_SINGLE, OperatorSpecifications::TYPE_INTEGER
            ),
            new OperatorSpecifications(
                Operator::IN, OperatorSpecifications::FORMAT_ARRAY, OperatorSpecifications::TYPE_INTEGER
            ),
            new OperatorSpecifications(
                Operator::BETWEEN, OperatorSpecifications::FORMAT_ARRAY, OperatorSpecifications::TYPE_INTEGER, 2
            ),
        );
    }

    /**
     * DateMetadata target: modification date
     */
    const MODIFIED = 'modified';

    /**
     * DateMetadata target: creation date
     */
    const CREATED = 'created';
}
?>
