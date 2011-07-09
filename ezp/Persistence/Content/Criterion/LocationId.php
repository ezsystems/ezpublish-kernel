<?php
/**
 * File containing the ezp\Persistence\Content\Criterion\Location
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 *
 */

namespace ezp\Persistence\Content\Criterion;

/**
 * @package ezp.persistence.content.criterion
 */
class LocationId extends Criterion implements \ezp\Persistence\Content\Interfaces\Criterion
{
    /**
     * Creates a new LocationId criterion
     *
     * @param null $target Not used
     * @param string $operator
     *        Possible values:
     *        - Operator::IN: match against a list of locationId. $value must be an array of locationId
     *        - Operator::EQ: match against a single locationId. $value must be a single locationId
     * @param integer|array(integer) One or more locationId that must be matched
     *
     * @throw InvalidArgumentException if a non numeric id is given
     * @throw InvalidArgumentException if the value type doesn't match the operator
     */
    public function __construct( $target = null, $operator, $value )
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
        elseif ( $operator == Operator::EQ )
        {
            if ( is_array( $value ) )
            {
                throw new \InvalidArgumentException( "Operator::EQ requires a single value" );
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
     */
    public $value;
}
?>
