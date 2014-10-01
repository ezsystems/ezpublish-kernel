<?php
/**
 * File containing the BinaryDataHandler interface
 *
 * @copyright Copyright (C) 2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\Core\IO;

use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\Core\IO\MetadataHandler as IOMetadataHandler;
use eZ\Publish\SPI\IO\BinaryFileCreateStruct;

/**
 * Provides reading & writing of files binary data.
 */
interface IOBinarydataHandler
{
    /**
     * Creates a new file with data from $binaryFileCreateStruct
     *
     * @param BinaryFileCreateStruct $binaryFileCreateStruct
     *
     * @return void
     */
    public function create( BinaryFileCreateStruct $binaryFileCreateStruct );

    /**
     * Deletes the file $path
     *
     * @param string $path
     *
     * @throws NotFoundException If $path isn't found
     */
    public function delete( $path );

    /**
     * Returns the binary content from $path
     *
     * @param $path
     *
     * @throws NotFoundException If $path is not found
     *
     * @return string
     */
    public function getContents( $path );

    /**
     * Returns a read-only, binary file resource to $path
     *
     * @param string $path
     *
     * @return resource A read-only binary resource to $path
     */
    public function getResource( $path );

    /**
     * Returns the public URI for $path
     * @param string $path
     * @return string
     */
    public function getUri( $path );
}
