<?php
/**
 * File containing the DateAndTime Value class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\DateAndTime;

use eZ\Publish\Core\FieldType\Value as BaseValue;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use Exception;
use DateTime;

/**
 * Value for DateAndTime field type
 */
class Value extends BaseValue
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
     * @param \DateTime|null $dateTime Date/Time as a DateTime object
     */
    public function __construct( DateTime $dateTime = null )
    {
        $this->value = $dateTime;
    }

    /**
     * Creates a Value from the given $dateString
     *
     * @param string $dateString
     *
     * @return \eZ\Publish\Core\FieldType\DateAndTime\Value
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
     * @param int $timestamp
     *
     * @return \eZ\Publish\Core\FieldType\DateAndTime\Value
     */
    public static function fromTimestamp( $timestamp )
    {
        try
        {
            return new static( new DateTime( "@{$timestamp}" ) );
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
        if ( !$this->value instanceof DateTime )
            return "";

        return $this->value->format( $this->stringFormat );
    }
}
