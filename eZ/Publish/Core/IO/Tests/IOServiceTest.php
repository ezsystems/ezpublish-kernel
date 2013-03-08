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
        $binaryCreateStruct = $this->getIOService()->newBinaryCreateStructFromLocalFile(
            __FILE__
        );

        self::assertInstanceOf( 'eZ\\Publish\\Core\\IO\\Values\\BinaryFileCreateStruct', $binaryCreateStruct );
        self::assertNull( $binaryCreateStruct->uri );
        self::assertTrue( is_resource( $binaryCreateStruct->inputStream ) );
        self::assertEquals( filesize( __FILE__ ), $binaryCreateStruct->size );

        return $binaryCreateStruct;
    }

    /**
     * @covers \eZ\Publish\Core\IO\IOService::createBinaryFile
     * @depends testNewBinaryCreateStructFromLocalFile
     */
    public function testCreateBinaryFile( BinaryFileCreateStruct $createStruct )
    {
        $createStruct->uri = "my/path.php";
        $uri = $this->getPrefixedUri( $createStruct->uri );

        $spiBinaryFile = new SPIBinaryFile;
        $spiBinaryFile->uri = $uri;
        $spiBinaryFile->size = filesize( __FILE__ );

        $this->getIOHandlerMock()->expects( $this->once() )
            ->method( 'create' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\IO\\BinaryFileCreateStruct' ) )
            ->will( $this->returnValue( $spiBinaryFile ) );

        $binaryFile = $this->getIOService()->createBinaryFile( $createStruct );
        self::assertInstanceOf( 'eZ\\Publish\\Core\\IO\Values\\BinaryFile', $binaryFile );
        self::assertEquals( $createStruct->uri, $binaryFile->uri );

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

        $this->getIOHandlerMock()
            ->expects( $this->once() )
            ->method( 'load' )
            ->with( $spiUri )
            ->will( $this->returnValue( $spiBinaryFile ) );

        $binaryFile = $this->getIOService()->loadBinaryFile( $uri );
        // @todo Do we really expect the SPI URI here ? Shouldn't it be kept INSIDE the IOService only ?
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

    /**
     * @return \eZ\Publish\Core\IO\IOService
     */
    private function getIOService()
    {
        if ( !$this->IOService )
        {
            $this->IOService = new \eZ\Publish\Core\IO\IOService(
                $this->getIOHandlerMock(),
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
}
