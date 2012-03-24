<?php
/**
 * File containing the DateAndTime Value class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\FieldType\DateAndTime;
use eZ\Publish\Core\Repository\FieldType\Value as BaseValue,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentType,
    Exception,
    DateTime;

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
     * @param \DateTime|string $dateTime Date/Time as a DateTime object or a string understood by the DateTime class
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If $dateTime does not comply to a valid dateTime or string
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If $dateTime does not comply to a valid date format string
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
                throw new InvalidArgumentType( '$dateTime', "DateTime", $dateTime );

            $this->value = $dateTime;
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
     * @see \eZ\Publish\Core\Repository\FieldType\Value::getTitle()
     * @todo Return format taken from locale settings (via ctor injection)
     */
    public function getTitle()
    {
        return $this->value->format( 'D Y-d-m H:i:s' );
    }
}
