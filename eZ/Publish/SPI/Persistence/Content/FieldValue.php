<?php

/**
 * File containing the (content) FieldValue class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Persistence\Content;

use eZ\Publish\SPI\Persistence\ValueObject;

class FieldValue extends ValueObject
{
    /**
     * Mixed field data.
     *
     * Either a scalar (primitive), null or an array (map) of scalar values.
     *
     * Note: For the legacy storage engine we will need adaptors to map them to
     * the existing database fields, like data_int, data_float, data_text.
     *
     * @var int|float|bool|string|null|array
     */
    public $data;

    /**
     * Mixed external field data.
     *
     * Data which is not stored in the field but at an external place.
     * This data is processed by the field type storage interface method
     * storeFieldData, if used by the FieldType, otherwise null.
     *
     * Either a primitive, an array (map) or an object
     * If object it *must* be serializable, for instance DOMDocument is not valid object.
     *
     * @var mixed
     */
    public $externalData;

    /**
     * A value which can be used for sorting.
     *
     * Note: For the "old" storage engine we will need adaptors to map them to
     * the existing database fields, like sort_key_int, sort_key_string
     *
     * @var int|float|bool|string|null
     */
    public $sortKey;
}
