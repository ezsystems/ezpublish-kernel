<?php
/**
 * File containing the ezp\Persistence\Content\Criterion\Section
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
class Section extends Criterion implements CriterionInterface
{
    /**
     * Creates a new Section criterion
     *
     * Matches the content against one or more sectionId
     *
     * @param null $target Not used
     * @param string $operator
     *        Possible values:
     *        - Operator::IN: match against a list of sectionId. $value must be an array of sectionId
     *        - Operator::EQ: match against a single sectionId. $value must be a single sectionId
     * @param integer|array(integer) One or more sectionId that must be matched
     *
     * @throw InvalidArgumentException if a non numeric id is given
     * @throw InvalidArgumentException if the value type doesn't match the operator
     */
    public function __construct( $target = null, $operator, $value  )
    {
        parent::__construct( $target, $operator, $value );
    }

    private function getSpecifications()
    {
        return array(
            new OperatorSpecifications(
                Operator::IN,
                OperatorSpecifications::FORMAT_ARRAY,
                array( OperatorSpecifications::TYPE_INTEGER, OperatorSpecifications::TYPE_STRING )
            ),
            new OperatorSpecifications(
                Operator::EQ,
                OperatorSpecifications::FORMAT_SINGLE,
                array( OperatorSpecifications::TYPE_INTEGER, OperatorSpecifications::TYPE_STRING )
            ),
        );
    }
}
?>
