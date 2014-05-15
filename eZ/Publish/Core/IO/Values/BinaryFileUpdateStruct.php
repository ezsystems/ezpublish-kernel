<?php
/**
 * File containing the eZ\Publish\Core\IO\Values\BinaryFileUpdateStruct class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\IO\Values;

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
