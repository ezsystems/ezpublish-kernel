<?php
/**
 * File containing the ValidationError class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType;

use eZ\Publish\SPI\FieldType\ValidationError as ValidationErrorInterface;
use eZ\Publish\API\Repository\Values\Translation\Message;
use eZ\Publish\API\Repository\Values\Translation\Plural;

/**
 * Class for validation errors.
 */
class ValidationError implements ValidationErrorInterface
{
    /**
     * @var string
     */
    protected $singular;

    /**
     * @var string
     */
    protected $plural;

    /**
     * @var string
     */
    protected $values;

    /**
     * @param string $singular
     * @param string $plural
     * @param array $values
     */
    public function __construct( $singular, $plural = null, array $values = array() )
    {
        $this->singular = $singular;
        $this->plural = $plural;
        $this->values = $values;
    }

    /**
     * Returns a translatable Message
     *
     * @return \eZ\Publish\API\Repository\Values\Translation
     */
    public function getTranslatableMessage()
    {
        if ( isset( $this->plural ) )
        {
            return new Plural(
                $this->singular,
                $this->plural,
                $this->values
            );
        }
        else
        {
            return new Message(
                $this->singular,
                $this->values
            );
        }
    }
}
