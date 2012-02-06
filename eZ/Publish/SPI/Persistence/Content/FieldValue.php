<?php
/**
 * File containing the (content) FieldValue class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\ValueObject,
    eZ\Publish\Core\Repository\FieldType\Value;

/**
 */
class FieldValue extends ValueObject
{
    /**
     * FieldType Value object
     *
     * @note: For the "old" storage engine we will need adaptors to map them to
     * the existing database fields, like data_int, data_float, data_text.
     *
     * @var eZ\Publish\Core\Repository\FieldType\Value
     */
    public $data;

    /**
     * Collection of custom properties which are specific to the field type.
     * Typically these properties are used to configure behaviour of field types
     * and normally set in the FieldDefinition on ContentTypes.
     *
     * Example: List of base choices in ezselection field type
     *
     * Settings are indexed by field setting name.
     *
     * @var \eZ\Publish\Core\Repository\FieldType\FieldSettings
     */
    public $fieldSettings;

    /**
     * Mixed sort key
     *
     * @note: For the "old" storage engine we will need adaptors to map them to
     * the existing database fields, like sort_key_int, sort_key_string
     *
     * @var mixed
     */
    public $sortKey;

    public function __clone()
    {
        // Force object cloning to avoid them to point to the same object (same reference)
        if ( isset( $this->data ) )
            $this->data = clone $this->data;
        if ( isset( $this->fieldSettings ) )
            $this->fieldSettings = clone $this->fieldSettings;
    }
}
