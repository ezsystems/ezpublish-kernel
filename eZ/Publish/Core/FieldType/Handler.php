<?php

/**
 * File containing the Handler interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType;

/**
 * Field type handler interface.
 *
 * Some field types provides handlers which help manipulate the field type value.
 * These objects implement this interface.
 */
interface Handler
{
    /**
     * Populates the field type handler with data from a field type.
     *
     * @param mixed $value
     */
    public function initWithFieldTypeValue($value);

    /**
     * Returns a compatible value to store in a field type after manipulation
     * in the handler.
     *
     * @return mixed
     */
    public function getFieldTypeValue();
}
