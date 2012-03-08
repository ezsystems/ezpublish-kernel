<?php
/**
 * File containing the Handler class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\FieldType\BinaryFile;
use eZ\Publish\API\Repository\IOService;

/**
 * Binary file handler
 * @todo Handle creation from HTTP
 */
class Handler
{
    /**
     * @var \eZ\Publish\API\Repository\IOService
     */
    protected $IOService;

    /**
     * @param \eZ\Publish\API\Repository\IOService $IOService
     */
    public function __construct( IOService $IOService )
    {
        $this->IOService = $IOService;
    }

    /**
     * Creates a {@link \eZ\Publish\API\Repository\Values\IO\BinaryFile} object from $localPath.
     *
     * @param string $localPath Path to the local file, somewhere accessible in the system
     * @return \eZ\Publish\API\Repository\Values\IO\BinaryFile
     */
    public function createFromLocalPath( $localPath )
    {
        $struct = $this->IOService->newBinaryCreateStructFromLocalFile( $localPath );
        return $this->IOService->createBinaryFile( $struct );
    }
}
