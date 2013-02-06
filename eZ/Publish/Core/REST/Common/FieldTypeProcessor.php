<?php
/**
 * File containing the FieldTypeProcessor interface.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common;

/**
 * FieldTypeProcessor
 */
interface FieldTypeProcessor
{
    /**
     * Perform manipulations on an a generated $outgoingValueHash
     *
     * This method is called by the REST server to allow a field type to post
     * process the given $outgoingValueHash, which was previously generated
     * using {@link eZ\Publish\SPI\FieldType\FieldType::toHash()}, before it is
     * sent to the client. The return value of this method replaces
     * $outgoingValueHash and must obey to the same rules as the original
     * $outgoingValueHash.
     *
     * @param mixed $outgoingValueHash
     *
     * @return mixed Post processed hash
     */
    public function postProcessHash( $outgoingValueHash );

    /**
     * Perform manipulations on a received $incomingValueHash
     *
     * This method is called by the REST server to allow a field type to pre
     * process the given $incomingValueHash before it is handled by {@link
     * eZ\Publish\SPI\FieldType\FieldType::fromHash()}. The $incomingValueHash
     * can be expected to conform to the rules that need to apply to hashes
     * accepted by fromHash(). The return value of this method replaces the
     * $incomingValueHash.
     *
     * @param mixed $incomingValueHash
     *
     * @return mixed Pre processed hash
     */
    public function preProcessHash( $incomingValueHash );
}
