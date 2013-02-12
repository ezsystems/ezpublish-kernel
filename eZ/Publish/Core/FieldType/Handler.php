<?php
/**
 * File containing the Handler interface
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
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
     *
     * @return void
     */
    public function initWithFieldTypeValue( $value );

    /**
     * Returns a compatible value to store in a field type after manipulation
     * in the handler.
     *
     * @return mixed
     */
    public function getFieldTypeValue();
}
