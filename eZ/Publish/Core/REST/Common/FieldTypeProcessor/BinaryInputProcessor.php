<?php
/**
 * File containing the BinaryInputProcessor class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\FieldTypeProcessor;

use eZ\Publish\Core\REST\Common\FieldTypeProcessor;

abstract class BinaryInputProcessor extends FieldTypeProcessor
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
     * {@inheritDoc}
     */
    public function preProcessValueHash( $incomingValueHash )
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
