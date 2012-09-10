<?php
/**
 * File containing the Page Value class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Page;
use eZ\Publish\Core\FieldType\Value as BaseValue;

class Value extends BaseValue
{
    /**
     * Page XML definition
     *
     * @var string
     */
    public $xml;

    /**
     * Construct a new Value object and initialize it $xml
     *
     * @param string $xml
     */
    public function __construct( $xml = '' )
    {
        $this->xml = $xml;
    }

    /**
     * Returns a string representation of the field value.
     * This string representation must be compatible with format accepted via
     * {@link \eZ\Publish\SPI\FieldType\FieldType::buildValue}
     *
     * @return string
     */
    public function __toString()
    {
        return (string)$this->value;
    }
}
