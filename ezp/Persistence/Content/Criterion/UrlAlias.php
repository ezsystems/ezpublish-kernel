<?php
/**
 * File containing the ezp\Persistence\Content\Criterion\UrlAlias class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Content\Criterion;

/**
 * A criterion that matches Content based on Url aliases
 * @package ezp.persistence.content.criteria
 */
class UrlAliasCriterion extends Criterion implements \ezp\Persistence\Content\Interfaces\Criterion
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
        if ( $operator != Operator::IN )
        {
            if ( !is_array( $value ) )
            {
                throw new \InvalidArgumentException( "Operator::IN requires an array of values" );
            }
            foreach( $subtreeId as $id )
            {
                if ( !is_numeric( $id ) )
                {
                    throw new \InvalidArgumentException( "Only numeric ids are accepted" );
                }
            }
        }
        // single value, EQ operator
        elseif ( $operator == Operator::EQ || Operator::LIKE)
        {
            if ( is_array( $value ) )
            {
                throw new \InvalidArgumentException( "Operator::EQ and Operator::LIKE require a single value" );
            }
            if ( !is_numeric( $value ) )
            {
                throw new \InvalidArgumentException( "Only numeric ids are accepted" );
            }
        }
        $this->operator = $operator;
        $this->value = $value;
    }

    /**
     * List of UrlAliases to match against
     * @var array(string)
     */
    public $value;
}
?>
