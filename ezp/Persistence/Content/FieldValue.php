<?php
/**
 * File containing the (content) FieldValue class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Content;
use ezp\Persistence\ValueObject;

/**
 */
class FieldValue extends ValueObject
{
    /**
     * Mixed data, which is contained in the field data
     *
     * @note: For the "old" storage engine we will need adaptors to map them to
     * the existing database fields, like data_int, data_float, data_text.
     *
     * @var mixed
     */
    public $data;

    /**
     * Arbitrary external data.
     *
     * This field is used to handle data of a field type, which will be stored
     * in its own database tables.
     *
     * @var mixed|null
     */
    public $externalData;

    /**
     * Mixed sort key
     *
     * @note: For the "old" storage engine we will need adaptors to map them to
     * the existing database fields, like sort_key_int, sort_key_string
     *
     * @var mixed
     */
    public $sortKey;
}
