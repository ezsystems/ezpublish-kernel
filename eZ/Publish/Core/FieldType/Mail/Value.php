<?php
/**
 * File containing the Mail Value class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Mail;
use eZ\Publish\Core\FieldType\Value as BaseValue;

/**
 * Value for Mail field type
 */
class Value extends BaseValue
{
    /**
     * email addreas
     *
     * @var string
     */
    public $email;

    /**
     * Construct a new Value object and initialize its $email
     *
     * @param string $text
     */
    public function __construct( $email = '' )
    {
        $this->email = $email;
    }

    /**
     * @see \eZ\Publish\Core\FieldType\Value
     */
    public function __toString()
    {
        return (string)$this->email;
    }
}
