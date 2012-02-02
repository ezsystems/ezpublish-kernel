<?php
/**
 * File containing a Io Handler test
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Io\Tests\Storage;
use eZ\Publish\Core\Io\InMemory\Handler as InMemory,
    eZ\Publish\SPI\Io\BinaryFile,
    eZ\Publish\SPI\Io\BinaryFileCreateStruct,
    eZ\Publish\SPI\Io\BinaryFileUpdateStruct,
    eZ\Publish\Core\Io\Tests\Base as BaseHandlerTest;

/**
 * Handler test
 */
class InMemoryTest extends BaseHandlerTest
{
    /**
     * @return \eZ\Publish\SPI\Io\Handler
     */
    protected function getIoHandler()
    {
        return new InMemory();
    }
}
