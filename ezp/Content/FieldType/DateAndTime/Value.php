<?php
/**
 * File containing the DateAndTime Value class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\DateAndTime;
use ezp\Content\FieldType\ValueInterface,
    ezp\Content\FieldType\Value as BaseValue,
    ezp\Base\Exception\InvalidArgumentValue,
    Exception,
    DateTime;

/**
 * Value for DateAndTime field type
 */
class Value extends BaseValue implements ValueInterface
{
    /**
     * Date content
     *
     * @var \DateTime
     */
    public $value;

    /**
     * Date format to be used by {@link __toString()}
     *
     * @var string
     */
    public $stringFormat = 'U';

    /**
     * Construct a new Value object and initialize with $dateTime
     *
     * @param \DateTime $dateTime
     */
    public function __construct( DateTime $dateTime = null )
    {
        if ( $dateTime !== null )
            $this->value = $dateTime;
    }

    /**
     * @param string $stringValue A valid date/time string.
     *                            Valid formats are explained in {@link http://php.net/manual/en/datetime.formats.php Date and Time Formats}.
     * @throws \ezp\Base\Exception\InvalidArgumentValue If $stringValue does not comply a valid date format
     * @return \ezp\Content\FieldType\DateAndTime\Value
     * @see \ezp\Content\FieldType\Value
     */
    public static function fromString( $stringValue )
    {
        try
        {
            return new static( new DateTime( $stringValue ) );
        }
        catch ( Exception $e )
        {
            throw new InvalidArgumentValue( '$stringValue', $stringValue, __CLASS__, $e );
        }
    }

    /**
     * @see \ezp\Content\FieldType\Value
     */
    public function __toString()
    {
        return $this->value->format( $this->stringFormat );
    }

    /**
     * @see \ezp\Content\FieldType\ValueInterface::getTitle()
     * @todo Return format taken from locale configuration
     */
    public function getTitle()
    {
        return $this->value->format( 'D Y-d-m H:i:s' );
    }
}
