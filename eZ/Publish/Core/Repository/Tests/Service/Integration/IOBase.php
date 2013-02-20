<?php
/**
 * File contains: eZ\Publish\Core\Repository\Tests\Service\Integration\IOBase class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service\Integration;

use eZ\Publish\Core\Repository\Tests\Service\Integration\Base as BaseServiceTest;
use eZ\Publish\API\Repository\Values\IO\BinaryFile;
use eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException as PropertyNotFound;
use eZ\Publish\API\Repository\Exceptions\PropertyReadOnlyException;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;

/**
 * Test case for IO Service
 */
abstract class IOBase extends BaseServiceTest
{
    /**
     * @return \PHPUnit_Extensions_PhptTestCase
     */
    abstract protected function getFileUploadTest();

    /**
     * Test a new class and default values on properties
     * @covers \eZ\Publish\API\Repository\Values\IO\BinaryFile::__construct
     */
    public function testNewClass()
    {
        $binaryFile = new BinaryFile();

        $this->assertPropertiesCorrect(
            array(
                'id' => null,
                'size' => null,
                'mtime' => null,
                'ctime' => null,
                'mimeType' => null,
                'uri' => null,
                'originalFile' => null
            ),
            $binaryFile
        );
    }

    /**
     * Test retrieving missing property
     * @covers \eZ\Publish\API\Repository\Values\IO\BinaryFile::__get
     */
    public function testMissingProperty()
    {
        try
        {
            $binaryFile = new BinaryFile();
            $value = $binaryFile->notDefined;
            self::fail( "Succeeded getting non existing property" );
        }
        catch ( PropertyNotFound $e )
        {
        }
    }

    /**
     * Test setting read only property
     * @covers \eZ\Publish\API\Repository\Values\IO\BinaryFile::__set
     */
    public function testReadOnlyProperty()
    {
        try
        {
            $binaryFile = new BinaryFile();
            $binaryFile->id = 42;
            self::fail( "Succeeded setting read only property" );
        }
        catch ( PropertyReadOnlyException $e )
        {
        }
    }

    /**
     * Test if property exists
     * @covers \eZ\Publish\API\Repository\Values\IO\BinaryFile::__isset
     */
    public function testIsPropertySet()
    {
        $binaryFile = new BinaryFile();
        $value = isset( $binaryFile->notDefined );
        self::assertEquals( false, $value );

        $value = isset( $binaryFile->id );
        self::assertEquals( true, $value );
    }

    /**
     * Test unsetting a property
     * @covers \eZ\Publish\API\Repository\Values\IO\BinaryFile::__unset
     */
    public function testUnsetProperty()
    {
        $binaryFile = new BinaryFile( array( "id" => "file-id" ) );
        try
        {
            unset( $binaryFile->id );
            self::fail( 'Unsetting read-only property succeeded' );
        }
        catch ( PropertyReadOnlyException $e )
        {
        }
    }

    /**
     * Test creating new BinaryCreateStruct from uploaded file
     * @covers \eZ\Publish\API\Repository\IOService::newBinaryCreateStructFromUploadedFile
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
     * @param array $failures
     * @param string $delimiter
     *
     * @return string
     */
    private function expandFailureMessages( array $failures, $delimiter = ', ' )
    {
        $messages = array();
        /**
         * @var \PHPUnit_Framework_TestFailure $failure
         */
        foreach ( $failures as $failure )
        {
            $e = $failure->thrownException();
            $text = "\n\nException " . get_class( $e ) . ' in file ' . $e->getFile() . ':' . $e->getLine() . "\n";
            $text .= $e->toString();
            $text .= "\n" . $e->getTraceAsString();
            $messages[] = $text;
        }
        return implode( $delimiter, $messages );

    }

    /**
     * Test creating new BinaryCreateStruct from uploaded file throwing InvalidArgumentException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @covers \eZ\Publish\API\Repository\IOService::newBinaryCreateStructFromUploadedFile
     */
    public function testNewBinaryCreateStructFromUploadedFileThrowsInvalidArgumentException()
    {
        $ioService = $this->repository->getIOService();

        $postArray = array(
            'name' => 'ezplogo.png',
            'type' => 'image/png',
            'tmp_name' => __DIR__ . '/ezplogo.png',
            'size' => 7329,
            'error' => 0
        );

        $ioService->newBinaryCreateStructFromUploadedFile( $postArray );
    }

    /**
     * Test creating new BinaryCreateStruct from local file
     * @covers \eZ\Publish\API\Repository\IOService::newBinaryCreateStructFromLocalFile
     */
    public function testNewBinaryCreateStructFromLocalFile()
    {
        $ioService = $this->repository->getIOService();

        $filePath = __DIR__ . '/ezplogo.png';

        $binaryCreateStruct = $ioService->newBinaryCreateStructFromLocalFile( $filePath );
        self::assertInstanceOf( '\\eZ\\Publish\\API\\Repository\\Values\\IO\\BinaryFileCreateStruct', $binaryCreateStruct );

        $fileHandle = fopen( $filePath, 'rb' );

        $this->assertPropertiesCorrect(
            array(
                'mimeType' => 'image/png',
                'uri' => $filePath,
                'originalFileName' => 'ezplogo.png',
                'size' => 7329
            ),
            $binaryCreateStruct
        );

        $expectedStreamMetaData = stream_get_meta_data( $fileHandle );
        $actualStreamMetaData = stream_get_meta_data( $binaryCreateStruct->inputStream );

        self::assertEquals( $expectedStreamMetaData, $actualStreamMetaData );

        fclose( $fileHandle );
        fclose( $binaryCreateStruct->inputStream );
    }

    /**
     * Test creating new BinaryCreateStruct from local file throwing InvalidArgumentException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @covers \eZ\Publish\API\Repository\IOService::newBinaryCreateStructFromLocalFile
     */
    public function testNewBinaryCreateStructFromLocalFileThrowsInvalidArgumentException()
    {
        $ioService = $this->repository->getIOService();

        $filePath = __DIR__ . '/ezplogo-invalid.png';

        $ioService->newBinaryCreateStructFromLocalFile( $filePath );
    }

    /**
     * Test creating new BinaryFile in the repository
     * @covers \eZ\Publish\API\Repository\IOService::createBinaryFile
     */
    public function testCreateBinaryFile()
    {
        $ioService = $this->repository->getIOService();

        $filePath = __DIR__ . '/ezplogo.png';

        $binaryCreateStruct = $ioService->newBinaryCreateStructFromLocalFile( $filePath );
        $binaryCreateStruct->uri = 'var/test/ezplogo.png';

        $binaryFile = $ioService->createBinaryFile( $binaryCreateStruct );

        $this->assertPropertiesCorrect(
            array(
                //@todo: is binary file ID equal to path?
                'id' => $binaryCreateStruct->uri,
                'size' => $binaryCreateStruct->size,
                'mimeType' => $binaryCreateStruct->mimeType,
                'uri' => $binaryCreateStruct->uri,
                'originalFile' => $binaryCreateStruct->originalFileName
            ),
            $binaryFile,
            array(
                'mtime',
                'ctime'
            )
        );
    }

    /**
     * Test deleting BinaryFile from the repository
     * @covers \eZ\Publish\API\Repository\IOService::deleteBinaryFile
     */
    public function testDeleteBinaryFileThrowsNotFoundException()
    {
        $ioService = $this->repository->getIOService();

        $filePath = __DIR__ . '/ezplogo.png';

        $binaryCreateStruct = $ioService->newBinaryCreateStructFromLocalFile( $filePath );
        $binaryCreateStruct->uri = 'var/test/ezplogo.png';

        $binaryFile = $ioService->createBinaryFile( $binaryCreateStruct );

        $loadedBinaryFile = $ioService->loadBinaryFile( $binaryFile->id );

        $ioService->deleteBinaryFile( $loadedBinaryFile );

        try
        {
            $ioService->loadBinaryFile( $loadedBinaryFile->id );
            self::fail( "succeeded loading deleted file" );
        }
        catch ( NotFoundException $e )
        {
            // Do nothing
        }
    }

    /**
     * Test loading BinaryFile from the repository
     * @covers \eZ\Publish\API\Repository\IOService::loadBinaryFile
     */
    public function testLoadBinaryFile()
    {
        $ioService = $this->repository->getIOService();

        $filePath = __DIR__ . '/ezplogo.png';

        $binaryCreateStruct = $ioService->newBinaryCreateStructFromLocalFile( $filePath );
        $binaryCreateStruct->uri = 'var/test/ezplogo.png';

        $binaryFile = $ioService->createBinaryFile( $binaryCreateStruct );

        $loadedBinaryFile = $ioService->loadBinaryFile( $binaryFile->id );

        $this->assertSameClassPropertiesCorrect(
            array(
                'id',
                'size',
                'mtime',
                'ctime',
                'mimeType',
                'uri',
                'originalFile'
            ),
            $binaryFile,
            $loadedBinaryFile
        );
    }

    /**
     * Test loading BinaryFile from the repository throwing NotFoundException
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @covers \eZ\Publish\API\Repository\IOService::loadBinaryFile
     */
    public function testLoadBinaryFileThrowsNotFoundException()
    {
        $ioService = $this->repository->getIOService();

        $filePath = __DIR__ . '/ezplogo-invalid.png';
        $ioService->loadBinaryFile( $filePath );
    }

    /**
     * Test getting file input stream
     * @covers \eZ\Publish\API\Repository\IOService::getFileInputStream
     */
    public function testGetFileInputStream()
    {
        $ioService = $this->repository->getIOService();

        $filePath = __DIR__ . '/ezplogo.png';

        $binaryCreateStruct = $ioService->newBinaryCreateStructFromLocalFile( $filePath );
        $binaryCreateStruct->uri = 'var/test/ezplogo.png';

        $binaryFile = $ioService->createBinaryFile( $binaryCreateStruct );

        $loadedInputStream = $ioService->getFileInputStream( $binaryFile );

        self::assertEquals( true, is_resource( $loadedInputStream ) );
    }

    /**
     * Test getting file contents
     * @covers \eZ\Publish\API\Repository\IOService::getFileContents
     */
    public function testGetFileContents()
    {
        $ioService = $this->repository->getIOService();

        $filePath = __DIR__ . '/ezplogo.png';

        $binaryCreateStruct = $ioService->newBinaryCreateStructFromLocalFile( $filePath );
        $binaryCreateStruct->uri = 'var/test/ezplogo.png';

        $binaryFile = $ioService->createBinaryFile( $binaryCreateStruct );

        $expectedFileContents = file_get_contents( $filePath );
        $loadedFileContents = $ioService->getFileContents( $binaryFile );

        self::assertEquals( base64_encode( $expectedFileContents ), base64_encode( $loadedFileContents ) );
    }
}
