<?php
/**
 * File containing the Date Value class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Date;

use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
use Exception;
use DateTime;
use DateTimeZone;

/**
 * Value for Date field type
 */
class Value extends BaseValue
{
    /**
     * Date content
     *
     * @var \DateTime
     */
    public $date;

    /**
     * Date format to be used by {@link __toString()}
     *
     * @var string
     */
    public $stringFormat = 'l d F Y';

    /**
     * Construct a new Value object and initialize with $dateTime
     *
     * @param \DateTime|null $dateTime Date as a DateTime object
     */
    public function __construct( DateTime $dateTime = null )
    {
        if ( $dateTime !== null )
        {
            $dateTime = clone $dateTime;
            $dateTime->setTime( 0, 0, 0 );
        }
        $this->date = $dateTime;
    }

    /**
     * Creates a Value from the given $dateString
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param string $dateString
     *
     * @return \eZ\Publish\Core\FieldType\Date\Value
     */
    public static function fromString( $dateString )
    {
        try
        {
            return new static( new DateTime( $dateString ) );
        }
        catch ( Exception $e )
        {
            throw new InvalidArgumentValue( '$dateString', $dateString, __CLASS__, $e );
        }
    }

    /**
     * Creates a Value from the given $timestamp
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param int $timestamp
     *
     * @return \eZ\Publish\Core\FieldType\Date\Value
     */
    public static function fromTimestamp( $timestamp )
    {
        try
        {
            $dateTime = new DateTime();
            $dateTime->setTimestamp( $timestamp );
            return new static( $dateTime );
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
        if ( !$this->date instanceof DateTime )
        {
            return "";
        }

        return $this->date->format( $this->stringFormat );
    }
}
