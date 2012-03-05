<?php
/**
 * File containing the DateAndTime Value class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\FieldType\DateAndTime;
use eZ\Publish\Core\Repository\FieldType\ValueInterface,
    eZ\Publish\Core\Repository\FieldType\Value as BaseValue,
    ezp\Base\Exception\InvalidArgumentValue,
    ezp\Base\Exception\InvalidArgumentType,
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
     * @param \DateTime|string $dateTime Date/Time as a DateTime object or a string understood by the DateTime class
     * @throws \ezp\Base\Exception\InvalidArgumentType If $dateTime does not comply to a valid dateTime or string
     * @throws \ezp\Base\Exception\InvalidArgumentValue If $dateTime does not comply to a valid date format string
     */
    public function __construct( $dateTime = "now" )
    {
        if ( $dateTime !== null )
        {
            if ( is_string( $dateTime ) )
            {
                try
                {
                    $dateTime = new DateTime( $dateTime );
                }
                catch ( Exception $e )
                {
                    throw new InvalidArgumentValue( '$dateTime', $dateTime, __CLASS__, $e );
                }
            }

            if ( ! $dateTime instanceof DateTime )
                throw new InvalidArgumentType( "dateTime", "DateTime", $dateTime );

            $this->value = $dateTime;
        }
    }

    /**
     * @param string $stringValue A valid date/time string.
     *                            Valid formats are explained in {@link http://php.net/manual/en/datetime.formats.php Date and Time Formats}.
     * @throws \ezp\Base\Exception\InvalidArgumentValue If $stringValue does not comply a valid date format
     * @return \eZ\Publish\Core\Repository\FieldType\DateAndTime\Value
     * @see \eZ\Publish\Core\Repository\FieldType\Value
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
     * @see \eZ\Publish\Core\Repository\FieldType\Value
     */
    public function __toString()
    {
        if ( !$this->value instanceof DateTime )
            return "";

        return $this->value->format( $this->stringFormat );
    }

    /**
     * @see \eZ\Publish\Core\Repository\FieldType\ValueInterface::getTitle()
     * @todo Return format taken from locale configuration
     */
    public function getTitle()
    {
        return $this->value->format( 'D Y-d-m H:i:s' );
    }
}
