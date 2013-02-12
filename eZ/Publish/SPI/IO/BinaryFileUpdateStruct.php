<?php
/**
 * File containing the \eZ\Publish\SPI\IO\BinaryFileUpdateStruct class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\IO;

/**
 * Update struct for BinaryFile objects
 */
class BinaryFileUpdateStruct extends BinaryFile
{
    /**
     * @var resource
     */
    private $inputStream;

    /**
     * Returns the file's input resource
     *
     * @return resource
     */
    public function getInputStream()
    {
        return $this->inputStream;
    }

    /**
     * Sets the file's input resource
     *
     * @param resource $inputStream
     */
    public function setInputStream( $inputStream )
    {
        $this->inputStream = $inputStream;
    }
}
