<?php
/**
 * File containing the FieldType interface
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\FieldType;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition,
    eZ\Publish\SPI\Persistence\Content\FieldValue,
    eZ\Publish\SPI\FieldType\Event;

/**
 * The field type interface which all field types have to implement.
 *
 *
 * Hashes:
 *
 * The {@link toHash()} method in this class is meant to generate a simple
 * representation of a value of this field type. Hash does here not refer to
 * MD5 or similar hashing algorithms, but rather to hash-map (associative array)
 * type representation. This representation must be
 * usable, to transfer the value over plain text encoding formats, like e.g.
 * XML. As a result, the returned "hash" must either be a scalar value, a hash
 * array (associative array) a pure numeric array or a nested combination of
 * these. It must by no means contain objects, resources or cyclic references.
 * The corresponding {@link fromHash()} method must convert such a
 * representation back into a value, which is understood by the FieldType.
 */
interface EventListener
{
    /**
     * This method is called on occurring events.
     *
     * This method is called on occurring events in the Core to allow
     * FieldTypes to react to such events.
     *
     * @param \eZ\Publish\SPI\FieldType\Event $event
     */
    public function handleEvent( Event $event );
}

