<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\IO\Tests;

use eZ\Publish\Core\IO\TolerantIOService;
use eZ\Publish\Core\IO\Values\MissingBinaryFile;

/**
 * Test case for the TolerantIOService
 */
class TolerantIOServiceTest extends IOServiceTest
{
    public function setUp()
    {
        parent::setUp();

        $this->IOService = new TolerantIOService(
            $this->metadataHandlerMock,
            $this->binarydataHandlerMock,
            $this->mimeTypeDetectorMock,
            array( 'prefix' => self::PREFIX )
        );
    }

    /**
     * @covers \eZ\Publish\Core\IO\IOService::loadBinaryFile
     */
    public function testLoadBinaryFileNotFound()
    {
        $binaryFile = parent::testLoadBinaryFileNotFound();
        self::assertEquals(
            new MissingBinaryFile( array( 'id' => 'id.ext' ) ),
            $binaryFile
        );
    }

    /**
     * Overridden to change the expected exception (none)
     * @covers \eZ\Publish\Core\IO\IOService::deleteBinaryFile
     */
    public function testDeleteBinaryFileNotFound()
    {
        parent::testDeleteBinaryFileNotFound();
    }

    public function testLoadBinaryFileByUriNotFound()
    {
        self::assertEquals(
            new MissingBinaryFile( array( 'id' => "my/path.png" ) ),
            parent::testLoadBinaryFileByUriNotFound()
        );
    }
}
