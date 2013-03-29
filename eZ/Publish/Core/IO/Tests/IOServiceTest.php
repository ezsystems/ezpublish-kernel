<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Integration\IOBase class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\IO\Tests;

use eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException as PropertyNotFound;
use eZ\Publish\API\Repository\Exceptions\PropertyReadOnlyException;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\Core\IO\Handler as IOHandlerInterface;
use eZ\Publish\Core\IO\Values\BinaryFile;
use eZ\Publish\Core\IO\Values\BinaryFileCreateStruct;
use eZ\Publish\SPI\IO\BinaryFileCreateStruct as SPIBinaryFileCreateStruct;
use eZ\Publish\SPI\IO\BinaryFile as SPIBinaryFile;
use eZ\Publish\SPI\IO\MimeTypeDetector;

/**
 * Test case for IO Service
 */
class IOServiceTest extends \PHPUnit_Framework_TestCase
{
    const PREFIX = 'test-prefix';

    /**
     * @var IOService
     */
    private $IOService;

    /**
     * @var IOHandlerInterface
     */
    private $IOHandlerMock;

    /** @var MimeTypeDetector */
    private $mimeTypeDetectorMock;

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

        $this->getMimeTypeDetectorMock()
            ->expects( $this->once() )
            ->method( 'getFromPath' )
            ->with( $this->equalTo( $file ) )
            ->will( $this->returnValue( 'text/x-php' ) );

        $binaryCreateStruct = $this->getIOService()->newBinaryCreateStructFromLocalFile(
            $file
        );

        self::assertInstanceOf( 'eZ\\Publish\\Core\\IO\\Values\\BinaryFileCreateStruct', $binaryCreateStruct );
        self::assertNull( $binaryCreateStruct->uri );
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
        $createStruct->uri = "my/path.php";
        $uri = $this->getPrefixedUri( $createStruct->uri );

        $spiBinaryFile = new SPIBinaryFile;
        $spiBinaryFile->uri = $uri;
        $spiBinaryFile->size = filesize( __FILE__ );
        $spiBinaryFile->mimeType = 'text/x-php';

        $this->getIOHandlerMock()->expects( $this->once() )
            ->method( 'create' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\IO\\BinaryFileCreateStruct' ) )
            ->will( $this->returnValue( $spiBinaryFile ) );

        $binaryFile = $this->getIOService()->createBinaryFile( $createStruct );
        self::assertInstanceOf( 'eZ\\Publish\\Core\\IO\Values\\BinaryFile', $binaryFile );
        self::assertEquals( $createStruct->uri, $binaryFile->uri );
        self::assertEquals( $createStruct->mimeType, $binaryFile->mimeType );
        self::assertEquals( $createStruct->size, $binaryFile->size );

        return $binaryFile;
    }

    /**
     * @covers \eZ\Publish\Core\IO\IOService::loadBinaryFile
     */
    public function testLoadBinaryFile()
    {
        $uri = "my/path.png";
        $spiUri = $this->getPrefixedUri( $uri );
        $spiBinaryFile = new SPIBinaryFile;
        $spiBinaryFile->uri = $spiUri;
        $spiBinaryFile->size = 12345;
        $spiBinaryFile->mimeType = 'application/any';

        $this->getIOHandlerMock()
            ->expects( $this->once() )
            ->method( 'load' )
            ->with( $spiUri )
            ->will( $this->returnValue( $spiBinaryFile ) );

        $binaryFile = $this->getIOService()->loadBinaryFile( $uri );
        self::assertEquals( $uri, $binaryFile->uri );

        return $binaryFile;
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

        $this->getIOHandlerMock()
            ->expects( $this->once() )
            ->method( 'getFileContents' )
            ->with( $this->equalTo( $this->getPrefixedUri( $binaryFile->uri ) ) )
            ->will( $this->returnValue( $expectedContents ) );

        self::assertEquals(
            $this->getIOService()->getFileContents( $binaryFile ),
            $expectedContents
        );
    }

    /**
     * @covers \eZ\Publish\Core\IO\IOService::deleteBinaryFile
     * @depends testCreateBinaryFile
     */
    public function testDeleteBinaryFile( BinaryFile $binaryFile )
    {
        $this->getIOHandlerMock()
            ->expects( $this->once() )
            ->method( 'delete' )
            ->with( $this->equalTo( $this->getPrefixedUri( $binaryFile->uri ) ) );

        $this->getIOService()->deleteBinaryFile( $binaryFile );
    }

    public function getPrefixedUri( $uri )
    {
        return self::PREFIX . '/' . $uri;
    }

    public function testGetMetadata()
    {
        $binaryFile = new BinaryFile(
            array(
                'uri' => 'some/uri.png',
            )
        );

        $expectedMetadata = array(
            'meta' => 1,
            'data' => 2
        );

        $metadataHandlerMock = $this->getMock( 'eZ\\Publish\\Core\\IO\\MetadataHandler' );

        $this->getIOHandlerMock()
            ->expects( $this->once() )
            ->method( 'getMetadata' )
            ->with(
                $metadataHandlerMock,
                'test-prefix/some/uri.png'
            )
            ->will( $this->returnValue( $expectedMetadata ) );

        $metadata = $this->getIOService()->getMetadata( $metadataHandlerMock, $binaryFile );
        self::assertEquals( $metadata, $expectedMetadata );
    }

    /**
     * @return \eZ\Publish\Core\IO\IOService
     */
    private function getIOService()
    {
        if ( !$this->IOService )
        {
            $this->IOService = new \eZ\Publish\Core\IO\IOService(
                $this->getIOHandlerMock(),
                $this->getMimeTypeDetectorMock(),
                array( 'prefix' => self::PREFIX )
            );
        }
        return $this->IOService;
    }

    /**
     * @return IOHandlerInterface
     */
    private function getIOHandlerMock()
    {
        if ( !$this->IOHandlerMock )
        {
            $this->IOHandlerMock = $this->getMock( 'eZ\\Publish\\Core\\IO\\Handler' );
        }
        return $this->IOHandlerMock;
    }

    /**
     * @return MimeTypeDetector
     */
    private function getMimeTypeDetectorMock()
    {
        if ( !$this->mimeTypeDetectorMock )
        {
            $this->mimeTypeDetectorMock = $this->getMock( 'eZ\\Publish\\SPI\\IO\\MimeTypeDetector' );
        }
        return $this->mimeTypeDetectorMock;
    }
}
