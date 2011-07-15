<?php
/**
 * File containing the ezp\Persistence\Content\Criterion\Field class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Content\Criterion;
use ezp\Persistence\Content\Criterion;

/**
 */
class Field extends Criterion implements \ezp\Persistence\Content\Interfaces\Criterion
{
    /**
     * Creates a new Field Criterion.
     *
     * Matches $fieldIdentifier against $value using $operator
     *
     * @param string $target The target field
     * @param string $operato The match operator
     * @param mixed $matchValue The value to match against
     */
    public function __construct( $field, $operator, $value )
    {
        switch ( $operator )
        {
            case Operator::IN:
                if ( !is_array( $value ) )
                {
                    throw new \InvalidArgumentException( "Operator::IN requires an array of values" );
                }
                break;

            case Operator::EQ:
            case Operator::GT:
            case Operator::GTE:
            case Operator::LT:
            case Operator::LTE:
            case Operator::LIKE :
                if ( is_array( $value ) )
                {
                    throw new \InvalidArgumentException( "Operator::EQ requires a single value" );
                }
                break;

            case Operator::BETWEEN:
                if ( !is_array( $value ) || count( $value ) != 2 )
                {
                    throw new \InvalidArgumentException( "Operator::EQ requires an array of two values" );
                }
                break;

            default:
                throw new \InvalidArgumentException( "Unknown operator $operator" );
        }

        $this->target = $field;
        $this->operator = $operator;
        $this->value = $value;
    }

    /**
     * The ContentField identifier
     * @var string
     */
    public $target;

    /**
     * The value $fieldIdentifier should be matched against
     * @var mixed
     */
    public $value;
}
?>
