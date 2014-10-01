<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Integration\IOBase class
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\IO\Tests;

use eZ\Publish\Core\IO\Handler as IOHandlerInterface;
use eZ\Publish\Core\IO\IOService;
use eZ\Publish\Core\IO\Values\BinaryFile;
use eZ\Publish\Core\IO\Values\BinaryFileCreateStruct;
use eZ\Publish\SPI\IO\BinaryFile as SPIBinaryFile;
use eZ\Publish\SPI\IO\MimeTypeDetector;
use PHPUnit_Framework_TestCase;

/**
 * Test case for IO Service
 */
class IOServiceTest extends PHPUnit_Framework_TestCase
{
    const PREFIX = 'test-prefix';

    /** @var IOService */
    private $IOService;

    /** @var \eZ\Publish\Core\IO\IOMetadataHandler|\PHPUnit_Framework_MockObject_MockObject */
    private $metadataHandlerMock;

    /** @var \eZ\Publish\Core\IO\IOBinarydataHandler|\PHPUnit_Framework_MockObject_MockObject */
    private $binarydataHandlerMock;

    /** @var MimeTypeDetector|\PHPUnit_Framework_MockObject_MockObject */
    private $mimeTypeDetectorMock;

    public function setUp()
    {
        parent::setUp();

        $this->binarydataHandlerMock = $this->getMock( 'eZ\Publish\Core\IO\IOBinarydataHandler' );
        $this->metadataHandlerMock = $this->getMock( 'eZ\Publish\Core\IO\IOMetadataHandler' );
        $this->mimeTypeDetectorMock = $this->getMock( 'eZ\\Publish\\SPI\\IO\\MimeTypeDetector' );

        $this->IOService = new \eZ\Publish\Core\IO\IOService(
            $this->metadataHandlerMock,
            $this->binarydataHandlerMock,
            $this->mimeTypeDetectorMock,
            array( 'prefix' => self::PREFIX )
        );
    }
    /**
     * Test creating new BinaryCreateStruct from uploaded file
     * @covers \eZ\Publish\Core\IO\IOService::newBinaryCreateStructFromUploadedFile
     */
    public function testNewBinaryCreateStructFromUploadedFile()
    {
        self::markTestSkipped( 'Test skipped as it seems to depend on php-cgi' );
        $uploadTest = $this->getFileUploadTest();
        $result = $uploadTest->run();// Fails because of unset cgi param and missing php-cgi exe
        // Params bellow makes the code execute but fails:
        //->run( null, array( 'cgi' => 'php' ) );

        if ( $result->failureCount() > 0 )
        {
            self::fail(
                "Failed file upload test, failureCount() > 0: " .
                $this->expandFailureMessages( $result->failures() )
            );
        }

        if ( $result->errorCount() > 0 )
        {
            self::fail(
                "Failed file upload test, errorCount() > 0: " .
                $this->expandFailureMessages( $result->errors() )
            );
        }

        if ( $result->skippedCount() > 0 )
        {
            self::fail(
                "Failed file upload test, skippedCount() > 0: " .
                $this->expandFailureMessages( $result->skipped() )
            );
        }
    }

    /**
     * @covers \eZ\Publish\Core\IO\IOService::newBinaryCreateStructFromUploadedFile
     */
    public function testNewBinaryCreateStructFromLocalFile()
    {
        $file = __FILE__;

        $this->mimeTypeDetectorMock
            ->expects( $this->once() )
            ->method( 'getFromPath' )
            ->with( $this->equalTo( $file ) )
            ->will( $this->returnValue( 'text/x-php' ) );

        $binaryCreateStruct = $this->getIOService()->newBinaryCreateStructFromLocalFile(
            $file
        );

        self::assertInstanceOf( 'eZ\\Publish\\Core\\IO\\Values\\BinaryFileCreateStruct', $binaryCreateStruct );
        self::assertNull( $binaryCreateStruct->id );
        self::assertTrue( is_resource( $binaryCreateStruct->inputStream ) );
        self::assertEquals( filesize( __FILE__ ), $binaryCreateStruct->size );
        self::assertEquals( 'text/x-php', $binaryCreateStruct->mimeType );

        return $binaryCreateStruct;
    }

    /**
     * @covers \eZ\Publish\Core\IO\IOService::createBinaryFile
     * @covers \eZ\Publish\Core\IO\IOService::buildSPIBinaryFileCreateStructObject
     * @covers \eZ\Publish\Core\IO\IOService::buildDomainBinaryFileObject
     * @depends testNewBinaryCreateStructFromLocalFile
     */
    public function testCreateBinaryFile( BinaryFileCreateStruct $createStruct )
    {
        $createStruct->id = "my/path.php";
        $id = $this->getPrefixedUri( $createStruct->id );

        $spiBinaryFile = new SPIBinaryFile;
        $spiBinaryFile->id = $id;
        $spiBinaryFile->uri = $id;
        $spiBinaryFile->size = filesize( __FILE__ );
        $spiBinaryFile->mimeType = 'text/x-php';

        $this->binarydataHandlerMock
            ->expects( $this->once() )
            ->method( 'create' )
            ->with(
                $this->callback(
                    function ( $subject ) use ( $id )
                    {
                        if ( !$subject instanceof \eZ\Publish\SPI\IO\BinaryFileCreateStruct )
                            return  false;
                        return $subject->id == $id;
                    }
                )
            );

        $this->metadataHandlerMock
            ->expects( $this->once() )
            ->method( 'create' )
            ->with( $this->callback( $this->getSPIBinaryFileCreateStructCallback( $id ) ) )
            ->will( $this->returnValue( $spiBinaryFile ) );

        $binaryFile = $this->IOService->createBinaryFile( $createStruct );
        self::assertInstanceOf( 'eZ\Publish\Core\IO\Values\BinaryFile', $binaryFile );
        self::assertEquals( $createStruct->id, $binaryFile->id );
        self::assertEquals( $createStruct->mimeType, $binaryFile->mimeType );
        self::assertEquals( $createStruct->size, $binaryFile->size );

        return $binaryFile;
    }

    /**
     * @covers \eZ\Publish\Core\IO\IOService::loadBinaryFile
     */
    public function testLoadBinaryFile()
    {
        $id = "my/path.png";
        $spiId = $this->getPrefixedUri( $id );
        $spiBinaryFile = new SPIBinaryFile;
        $spiBinaryFile->id = $spiId;
        $spiBinaryFile->size = 12345;
        $spiBinaryFile->mimeType = 'application/any';
        $spiBinaryFile->uri = $spiId;

        $this->metadataHandlerMock
            ->expects( $this->once() )
            ->method( 'load' )
            ->with( $spiId )
            ->will( $this->returnValue( $spiBinaryFile ) );

        $binaryFile = $this->getIOService()->loadBinaryFile( $id );
        self::assertEquals( $id, $binaryFile->id );

        return $binaryFile;
    }

    /**
     * @covers \eZ\Publish\Core\IO\IOService::loadBinaryFile
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testLoadBinaryFileNotFound()
    {
        $id = 'id.ext';
        $prefixedUri = $this->getPrefixedUri( $id );
        $this->metadataHandlerMock
            ->expects( $this->once() )
            ->method( 'load' )
            ->with( $prefixedUri )
            ->will(
                $this->throwException(
                    new \eZ\Publish\Core\Base\Exceptions\NotFoundException(
                        'BinaryFile', $prefixedUri
                    )
                )
            );

        $this->getIOService()->loadBinaryFile( $id );
    }

    /**
     * @covers \eZ\Publish\Core\IO\IOService::getFileInputStream
     * @depends testCreateBinaryFile
     */
    public function testGetFileInputStream( BinaryFile $binaryFile )
    {
        self::markTestSkipped( "Not implemented" );
    }

    /**
     * @depends testLoadBinaryFile
     * @covers \eZ\Publish\Core\IO\IOService::getFileContents
     */
    public function testGetFileContents( BinaryFile $binaryFile )
    {
        $expectedContents = file_get_contents( __FILE__ );

        $this->binarydataHandlerMock
            ->expects( $this->once() )
            ->method( 'getContents' )
            ->with( $this->equalTo( $this->getPrefixedUri( $binaryFile->id ) ) )
            ->will( $this->returnValue( $expectedContents ) );

        self::assertEquals(
            $this->getIOService()->getFileContents( $binaryFile ),
            $expectedContents
        );
    }

    /**
     * @depends testCreateBinaryFile
     * @covers IOService \eZ\Publish\Core\IO\IOService::exists()
     */
    public function testExists( BinaryFile $binaryFile)
    {
        $this->metadataHandlerMock
            ->expects( $this->once() )
            ->method( 'exists' )
            ->with( $this->equalTo( $this->getPrefixedUri( $binaryFile->id ) ) )
            ->will( $this->returnValue( true ) );

        self::assertTrue(
            $this->getIOService()->exists(
                $binaryFile->id
            )
        );
    }

    /**
     * @covers IOService \eZ\Publish\Core\IO\IOService::exists()
     */
    public function testExistsNot()
    {
        $this->metadataHandlerMock
            ->expects( $this->once() )
            ->method( 'exists' )
            ->with( $this->equalTo( $this->getPrefixedUri( __METHOD__ ) ) )
            ->will( $this->returnValue( false ) );

        self::assertFalse(
            $this->getIOService()->exists(
                __METHOD__
            )
        );
    }

    /**
     * @covers \eZ\Publish\Core\IO\IOService::deleteBinaryFile
     * @depends testCreateBinaryFile
     */
    public function testDeleteBinaryFile( BinaryFile $binaryFile )
    {
        $this->metadataHandlerMock
            ->expects( $this->once() )
            ->method( 'delete' )
            ->with( $this->equalTo( $this->getPrefixedUri( $binaryFile->id ) ) );

        $this->binarydataHandlerMock
            ->expects( $this->once() )
            ->method( 'delete' )
            ->with( $this->equalTo( $this->getPrefixedUri( $binaryFile->id ) ) );

        $this->getIOService()->deleteBinaryFile( $binaryFile );
    }

    /**
     * @covers \eZ\Publish\Core\IO\IOService::deleteBinaryFile
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testDeleteBinaryFileNotFound()
    {
        $binaryFile = new BinaryFile(
            array( 'id' => __METHOD__ )
        );

        $prefixedId = $this->getPrefixedUri( $binaryFile->id );
        $this->metadataHandlerMock
            ->expects( $this->once() )
            ->method( 'delete' )
            ->with( $this->equalTo( $prefixedId ) )
            ->will(
                $this->throwException(
                    new \eZ\Publish\Core\Base\Exceptions\NotFoundException( 'BinaryFile', $prefixedId )
                )
            );

        $this->getIOService()->deleteBinaryFile( $binaryFile );
    }

    public function getPrefixedUri( $uri )
    {
        return self::PREFIX . '/' . $uri;
    }

    /**
     * @return \eZ\Publish\Core\IO\IOService
     */
    private function getIOService()
    {
        return $this->IOService;
    }

    /**
     * Asserts that the given $ioCreateStruct is of the right type and that id matches the expected value
     * @param $ioCreateStruct
     *
     * @return bool
     */
    private function getSPIBinaryFileCreateStructCallback( $spiId )
    {
        return function( $subject ) use ( $spiId ) {
            if ( !$subject instanceof \eZ\Publish\SPI\IO\BinaryFileCreateStruct )
            {
                return false;
            }

            return $subject->id == $spiId;
        };
    }
}
