<?php
/**
 * File containing the Time Value class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Time;

use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
use Exception;
use DateTime;
use DateTimeZone;

/**
 * Value for Time field type
 */
class Value extends BaseValue
{
    /**
     * Time of day as number of seconds.
     *
     * @var int|null
     */
    public $time;

    /**
     * Time format to be used by {@link __toString()}.
     *
     * @var string
     */
    public $stringFormat = "H:i:s";

    /**
     * Construct a new Value object and initialize it with $seconds as number of seconds from beginning of day.
     *
     * @param mixed $seconds
     */
    public function __construct( $seconds = null )
    {
        $this->time = $seconds;
    }

    /**
     * Creates a Value from the given $dateTime.
     *
     * @param DateTime $dateTime
     *
     * @return \eZ\Publish\Core\FieldType\Time\Value
     */
    public static function fromDateTime( DateTime $dateTime )
    {
        $dateTime = clone $dateTime;
        return new static( $dateTime->getTimestamp() - $dateTime->setTime( 0, 0, 0 )->getTimestamp() );
    }

    /**
     * Creates a Value from the given $timeString.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param string $timeString
     *
     * @return \eZ\Publish\Core\FieldType\Time\Value
     */
    public static function fromString( $timeString )
    {
        try
        {
            return static::fromDateTime( new DateTime( $timeString ) );
        }
        catch ( Exception $e )
        {
            throw new InvalidArgumentValue( '$timeString', $timeString, __CLASS__, $e );
        }
    }

    /**
     * Creates a Value from the given $timestamp.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param int $timestamp
     *
     * @return static
     */
    public static function fromTimestamp( $timestamp )
    {
        try
        {
            $dateTime = new DateTime();
            $dateTime->setTimestamp( $timestamp );
            return static::fromDateTime( $dateTime );
        }
        catch ( Exception $e )
        {
            throw new InvalidArgumentValue( '$timestamp', $timestamp, __CLASS__, $e );
        }
    }

    /**
     * @see \eZ\Publish\Core\FieldType\Value
     */
    public function __toString()
    {
        if ( $this->time === null )
        {
            return "";
        }

        $dateTime = new DateTime( "@{$this->time}" );
        return $dateTime->format( $this->stringFormat );
    }
}
