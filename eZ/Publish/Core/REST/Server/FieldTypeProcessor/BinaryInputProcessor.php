<?php
/**
 * File containing the BinaryInputProcessor class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\FieldTypeProcessor;

use eZ\Publish\Core\REST\Common\FieldTypeProcessor;

abstract class BinaryInputProcessor implements FieldTypeProcessor
{
    /**
     * @var string
     */
    protected $temporaryDirectory;

    /**
     * @param string $temporaryDirectory
     */
    public function __construct( $temporaryDirectory )
    {
        $this->temporaryDirectory = $temporaryDirectory;
    }

    /**
     * Processes uploaded binary file data in $incomingValueHash
     *
     * This method checks the 'data' key in $incomingValueHash, which must
     * contain base64 encoded binary data to be stored as a binary file. It
     * stores the decoded data in a temporary file in {@link
     * $temporaryDirectory} and sets the 'path' key in the returned hash
     * accordingly.
     *
     * @param array $incomingValueHash
     *
     * @return array
     */
    public function preProcessHash( $incomingValueHash )
    {
        if ( isset( $incomingValueHash['data'] ) )
        {
            $tempFile = tempnam( $this->temporaryDirectory, 'eZ_REST_BinaryFile' );

            file_put_contents(
                $tempFile,
                $binaryContent = base64_decode( $incomingValueHash['data'] )
            );

            unset( $incomingValueHash['data'] );
            $incomingValueHash['path'] = $tempFile;
        }

        return $incomingValueHash;
    }
}
