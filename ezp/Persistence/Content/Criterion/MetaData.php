<?php
/**
 * File containing the MetaData
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Content\Criterion;
use ezp\Persistence\Content\Criterion,
    ezp\Persistence\Content\Interfaces\Criterion as CriterionInterface,
    InvalidArgumentException;

/**
 */
class MetaData extends Criterion implements CriterionInterface
{
    /**
     * Creates a new metadata criterion on $metadata
     *
     * @param string $target One of MetaData::STATE, MetaData::OWNER, MetaData::MODIFIED, MetaData::CREATED
     * @param string $operator One of the Operator constants
     * @param mixed $value The match value, either as an array of as a single value, depending on the operator*
     *
     * @throw InvalidArgumentException if a non numeric id is given
     * @throw InvalidArgumentException if the value type doesn't match the operator
     */
    public function __construct( $target, $operator, $value )
    {
        if ( !in_array( $target, array( self::STATE, self::OWNER, self::MODIFIED, self::CREATED ) ) )
        {
            throw new InvalidArgumentException( "target must be one of MetaData::STATE, MetaData::OWNER, MetaData::MODIFIED or MetaData::CREATED" );
        }

        switch ( $operator )
        {
            case Operator::IN:
                if ( !is_array( $value ) )
                {
                    throw new InvalidArgumentException( "Operator::IN requires an array of values" );
                }
                break;

            case Operator::EQ:
            case Operator::GT:
            case Operator::GTE:
            case Operator::LT:
            case Operator::LTE:
                if ( is_array( $value ) )
                {
                    throw new InvalidArgumentException( "Operator::EQ requires a single value" );
                }
                break;

            case Operator::BETWEEN:
                if ( !is_array( $value ) || count( $value ) != 2 )
                {
                    throw new InvalidArgumentException( "Operator::EQ requires an array of two values" );
                }
                break;

            default:
                throw new InvalidArgumentException( "Unknown operator $operator" );
        }
        $this->operator = $operator;
        $this->value = $value;
        $this->target = $target;
    }

    public function getSpecifications()
    {

    }

    /**
     * The criterion operator
     * @var string
     */
    public $operator;

    /**
     * The criterion's target MetaData
     * @var string
     */public $target;

    /**
     * The criterion match value
     * @var mixed
     */
    public $value;

    /**
     * MetaData target: Content state
     */
    const STATE = 0;

    /**
     * MetaData target: owner
     */
    const OWNER = 1;

    /**
     * MetaData target: modification date
     */
    const MODIFIED = 2;

    /**
     * MetaData targeet: creation date
     */
    const CREATED = 3;
}
?>
