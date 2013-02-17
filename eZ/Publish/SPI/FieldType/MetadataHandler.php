<?php
/**
 * File containing the MetadataHandler class.
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\SPI\FieldType;

interface MetadataHandler
{
    /**
     * Extracts metadata for the file identified by $storageIdentifier
     * @param string $storageIdentifier
     *
     * @return array Metadata hash
     */
    public function extract( $storageIdentifier );
}
