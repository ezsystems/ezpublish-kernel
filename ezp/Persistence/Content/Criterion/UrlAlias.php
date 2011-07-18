<?php
/**
 * File containing the ezp\Persistence\Content\Criterion\UrlAlias class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Content\Criterion;
use ezp\Persistence\Content\Criterion,
    ezp\Persistence\Content\Interfaces\Criterion as CriterionInterface;

/**
 * A criterion that matches Content based on Url aliases.
 *
 *
 */
class UrlAlias extends Criterion implements CriterionInterface
{
    /**
     * Creates a new UrlAlias Criterion
     *
     * @param string $target Not used
     * @param string $operator
     *        Possible values:
     *        - Operator::IN, requires an array of subtree id as the $value
     *        - Operator::EQ, requires a single subtree id as the $value
     * @param array(integer) $subtreeId an array of subtree ids
     *
     * @throws InvalidArgumentException if a non numeric id is given
     * @throw InvalidArgumentException if the value type doesn't match the operator
     */
    public function __construct( $target, $operator, $value )
    {
        parent::__construct( $target, $operator, $value );
    }

    protected function getSpecifications()
    {
        return array(
            array(
                Operator::IN,
                OperatorSpecifications::FORMAT_ARRAY,
                array( self::INPUT_VALUE_STRING )
            ),
            array(
                Operator::EQ,
                OperatorSpecifications::FORMAT_SINGLE,
                array( self::INPUT_VALUE_STRING ),
            ),
            array(
                Operator::LIKE,
                OperatorSpecifications::FORMAT_SINGLE,
                array( self::INPUT_VALUE_STRING ),
            ),
    );
    }

}
?>
