<?php
/**
 * File containing the ezp\Persistence\Content\Criterion\RemoteId class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Content\Criterion;
use ezp\Persistence\Content\Criterion,
    ezp\Persistence\Content\Criterion\Operator\Specifications,
    ezp\Persistence\Content\CriterionInterface;

/**
 * A criterion that matches content based on its RemoteId
 *
 * Supported operators:
 * - IN: will match from a list of RemoteId
 * - EQ: will match against one RemoteId
 */
class RemoteId extends Criterion implements CriterionInterface
{
    /**
     * Creates a new remoteId criterion
     *
     * @param null $target Not used
     * @param string $operator
     *        Possible values:
     *        - Operator::IN: match against a list of remoteId. $value must be an array of remoteId
     *        - Operator::EQ: match against a single remoteId. $value must be a single remoteId
     * @param integer|array(integer) One or more remoteId that must be matched
     *
     * @throw InvalidArgumentException if a non numeric id is given
     * @throw InvalidArgumentException if the value type doesn't match the operator
     */
    public function __construct(  $target, $operator, $value  )
    {
        parent::__construct( $target, $operator, $value );
    }

    public function getSpecifications()
    {
        return array(
            new Specifications(
                Operator::IN,
                Specifications::FORMAT_ARRAY,
                Specifications::TYPE_INTEGER | Specifications::TYPE_STRING
            ),
            new Specifications(
                Operator::EQ,
                Specifications::FORMAT_SINGLE,
                Specifications::TYPE_INTEGER | Specifications::TYPE_STRING
            ),
        );
    }
}
?>
