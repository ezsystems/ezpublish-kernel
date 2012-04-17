<?php
/**
 * File containing the IOServiceTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

/**
 * Test case for operations in the IOService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\IOService
 */
class IOServiceTest extends BaseTest
{
    /**
     * Test for the newBinaryCreateStructFromUploadedFile() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\IOService::newBinaryCreateStructFromUploadedFile()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testNewBinaryCreateStructFromUploadedFileThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $ioService = $repository->getIOService();

        // This call will fail with an "InvalidArgumentException", because the
        // given file was not uploaded.
        $ioService->newBinaryCreateStructFromUploadedFile(
            array(
                'tmp_name'  =>  __FILE__,
                'name'      =>  basename( __FILE__ ),
                'type'      =>  mime_content_type( __FILE__ ),
                'size'      =>  filesize( __FILE__ ),
                'error'     =>  0
            )
        );
        /* END: Use Case */
    }

    /**
     * Test for the newBinaryCreateStructFromLocalFile() method.
     *
     * @return \eZ\Publish\API\Repository\Values\IO\BinaryFileCreateStruct
     * @see \eZ\Publish\API\Repository\IOService::newBinaryCreateStructFromLocalFile()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetIOService
     */
    public function testNewBinaryCreateStructFromLocalFile()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $ioService = $repository->getIOService();

        // Get a file create struct
        $binaryCreate = $ioService->newBinaryCreateStructFromLocalFile( __FILE__ );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\IO\BinaryFileCreateStruct',
            $binaryCreate
        );

        return $binaryCreate;
    }

    /**
     * Test for the newBinaryCreateStructFromLocalFile() method.
     *
     * @param \eZ\Publish\API\Repository\Values\IO\BinaryFileCreateStruct $binaryCreate
     *
     * @return void
     * @see \eZ\Publish\API\Repository\IOService::newBinaryCreateStructFromLocalFile()
     * @depends eZ\Publish\API\Repository\Tests\IOServiceTest::testNewBinaryCreateStructFromLocalFile
     */
    public function testNewBinaryCreateStructFromLocalFileSetsExpectedProperties( $binaryCreate )
    {
        $this->assertInternalType( 'resource', $binaryCreate->inputStream );

        $this->assertPropertiesCorrect(
            array(
                'originalFileName'  =>  basename( __FILE__ ),
                'uri'               =>  'file://' . __FILE__,
                'mimeType'          =>  mime_content_type( __FILE__ ),
                'size'              =>  filesize( __FILE__ ),
            ),
            $binaryCreate
        );
    }

    /**
     * Test for the newBinaryCreateStructFromLocalFile() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\IOService::newBinaryCreateStructFromLocalFile()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\IOServiceTest::testNewBinaryCreateStructFromLocalFile
     */
    public function testNewBinaryCreateStructFromLocalFileThrowsInvalidArgumentExceptionIfFileNotExists()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $ioService = $repository->getIOService();

        // This call will fail with an "InvalidArgumentException", because no
        // such file exists.
        $ioService->newBinaryCreateStructFromLocalFile( __FILE__ . '.not.exists' );
        /* END: Use Case */
    }

    /**
     * Test for the createBinaryFile() method.
     *
     * @return \eZ\Publish\API\Repository\Values\IO\BinaryFile
     * @see \eZ\Publish\API\Repository\IOService::createBinaryFile()
     * @depends eZ\Publish\API\Repository\Tests\IOServiceTest::testNewBinaryCreateStructFromLocalFile
     */
    public function testCreateBinaryFile()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $ioService = $repository->getIOService();

        // Get a file create struct
        $binaryCreate = $ioService->newBinaryCreateStructFromLocalFile( __FILE__ );

        // Create a new file instance
        $binary = $ioService->createBinaryFile( $binaryCreate );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\IO\BinaryFile',
            $binary
        );

        return $binary;
    }

    /**
     * Test for the createBinaryFile() method.
     *
     * @param \eZ\Publish\API\Repository\Values\IO\BinaryFile $binary
     *
     * @return void
     * @see \eZ\Publish\API\Repository\IOService::createBinaryFile()
     * @depends eZ\Publish\API\Repository\Tests\IOServiceTest::testCreateBinaryFile
     */
    public function testCreateBinaryFileSetsExpectedProperties( $binary )
    {
        $this->assertNotNull( $binary->id );

        // 60 seconds should avoid every latency issues.
        $this->assertGreaterThan( time() - 60, $binary->mtime );
        $this->assertGreaterThan( time() - 60, $binary->ctime );

        $this->assertPropertiesCorrect(
            array(
                'originalFile'  =>  basename( __FILE__ ),
                'size'          =>  filesize( __FILE__ ),
                'mimeType'   =>  mime_content_type( __FILE__ )
                // TODO What is the uri?
                //'uri'  =>  'file://' . __FILE__,
            ),
            $binary
        );
    }


    /**
     * Test for the loadBinaryFile() method.
     *
     * @return \eZ\Publish\API\Repository\Values\IO\BinaryFile
     * @see \eZ\Publish\API\Repository\IOService::loadBinaryFile()
     * @depends eZ\Publish\API\Repository\Tests\IOServiceTest::testCreateBinaryFile
     */
    public function testLoadBinaryFile()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $ioService = $repository->getIOService();

        // Get a file create struct
        $binaryCreate = $ioService->newBinaryCreateStructFromLocalFile( __FILE__ );

        // Create a new file instance
        $binaryId = $ioService->createBinaryFile( $binaryCreate )->id;

        // Load the binary by it's id
        $binary = $ioService->loadBinaryFile( $binaryId );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\IO\BinaryFile',
            $binary
        );

        return $binary;
    }

    /**
     * Test for the loadBinaryFile() method.
     *
     * @param \eZ\Publish\API\Repository\Values\IO\BinaryFile $binary
     *
     * @return void
     * @see \eZ\Publish\API\Repository\IOService::loadBinaryFile()
     * @depends eZ\Publish\API\Repository\Tests\IOServiceTest::testLoadBinaryFile
     */
    public function testLoadBinaryFileSetsExpectedProperties( $binary )
    {
        $this->assertPropertiesCorrect(
            array(
                'originalFile'  =>  basename( __FILE__ ),
                'size'          =>  filesize( __FILE__ ),
                'mimeType'   =>  mime_content_type( __FILE__ )
                // TODO What is the uri?
                //'uri'  =>  'file://' . __FILE__,
            ),
            $binary
        );
    }

    /**
     * Test for the loadBinaryFile() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\IOService::loadBinaryFile()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\IOServiceTest::testLoadBinaryFile
     */
    public function testLoadBinaryFileThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        // TODO: What URL should be generated for this?
        $binaryId = $this->generateId( 'binary', 2342 );
        /* BEGIN: Use Case */
        $ioService = $repository->getIOService();

        // This call will fail with a "NotFoundException", because no binary file
        // with an ID 2342 should exist in an eZ Publish demo installation
        $ioService->loadBinaryFile( $binaryId );
        /* END: Use Case */
    }

    /**
     * Test for the deleteBinaryFile() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\IOService::deleteBinaryFile()
     * @depends eZ\Publish\API\Repository\Tests\IOServiceTest::testLoadBinaryFileThrowsNotFoundException
     */
    public function testDeleteBinaryFile()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $ioService = $repository->getIOService();

        // Get a file create struct
        $binaryCreate = $ioService->newBinaryCreateStructFromLocalFile( __FILE__ );

        // Create a new file instance
        $binary = $ioService->createBinaryFile( $binaryCreate );

        // Delete the new file again
        $ioService->deleteBinaryFile( $binary );
        /* END: Use Case */

        // We use tested NotFoundException to verify that the file isn't present
        try
        {
            $ioService->loadBinaryFile( $binary->id );
            $this->fail( "Can still load file after delete." );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e ) {}
    }

    /**
     * Test for the getFileInputStream() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\IOService::getFileInputStream()
     * 
     */
    public function testGetFileInputStream()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $ioService = $repository->getIOService();

        // Get a file create struct
        $binaryCreate = $ioService->newBinaryCreateStructFromLocalFile( __FILE__ );

        // Create a new file instance
        $binary = $ioService->createBinaryFile( $binaryCreate );

        // Get stream for the contents of the binary file
        $stream = $ioService->getFileInputStream( $binary );

        // Read content with regular file functions
        $content = '';
        while ( false === feof( $stream ) )
        {
            $content .= fgets( $stream );
        }
        fclose( $stream );
        /* END: Use Case */

        $this->assertEquals(
            file_get_contents( __FILE__ ),
            $content
        );
    }

    /**
     * Test for the getFileContents() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\IOService::getFileContents()
     * @depends eZ\Publish\API\Repository\Tests\IOServiceTest::testCreateBinaryFile
     */
    public function testGetFileContents()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $ioService = $repository->getIOService();

        // Get a file create struct
        $binaryCreate = $ioService->newBinaryCreateStructFromLocalFile( __FILE__ );

        // Create a new file instance
        $binary = $ioService->createBinaryFile( $binaryCreate );

        // Load contents of the binary file
        $content = $ioService->getFileContents( $binary );
        /* END: Use Case */

        $this->assertEquals(
            file_get_contents( __FILE__ ),
            $content
        );
    }

}
