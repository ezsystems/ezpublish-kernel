<?php
/**
 * File containing the FieldType interface
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence;

/**
 * The field type interface which field types available to storage engines have to implement.
 *
 * @see \eZ\Publish\SPI\FieldType\FieldType
 */
interface FieldType
{
    /**
     * Returns the empty value for the field type that can be processed by the storage engine.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue
     */
    public function getEmptyValue();
}
