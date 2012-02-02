<?php
/**
 * File containing a Io Handler test
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Io\Tests\Storage;
use eZ\Publish\Core\Io\Dispatcher\Handler as Dispatcher,
    eZ\Publish\Core\Io\InMemory\Handler as InMemory,
    eZ\Publish\SPI\Io\BinaryFile,
    eZ\Publish\SPI\Io\BinaryFileCreateStruct,
    eZ\Publish\SPI\Io\BinaryFileUpdateStruct,
    eZ\Publish\Core\Io\Tests\Base as BaseHandlerTest;

/**
 * Handler test
 */
class DispatcherTest extends BaseHandlerTest
{
    /**
     * @var \eZ\Publish\SPI\Io\Handler
     */
    protected $defaultBackend;

    /**
     * @var \eZ\Publish\SPI\Io\Handler
     */
    protected $alternativeBackend;

    /**
     * @return \eZ\Publish\SPI\Io\Handler
     */
    protected function getIoHandler()
    {
        $this->defaultBackend = new InMemory();
        $this->alternativeBackend = new InMemory();
        return new Dispatcher(
            array(
                'default' => $this->defaultBackend,
                'handlers' => array(
                    array(
                        'handler' => $this->alternativeBackend,
                        // match conditions:
                        'prefix' => 'var/test/',
                        'suffix' => '.gif,.jpg',
                        'contains' => 'image-versioned'
                    )
                )
            )
        );
    }

    /**
     * Test that file is created in default handler
     */
    public function testDispatcherDefaultBackendCreate()
    {
        $repositoryPath = 'var/test/storage/images/ezplogo.gif';
        $binaryFile = $this->binaryService->createFromLocalFile( $this->imageInputPath, $repositoryPath );
        $binaryFile2 = $this->defaultBackend->load( $repositoryPath );

        self::assertEquals( $binaryFile, $binaryFile2 );
    }

    /**
     * Test that file is created in default handler
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testDispatcherDefaultBackendCreateNotFound()
    {
        $repositoryPath = 'var/test/storage/images/ezplogo.gif';
        $this->binaryService->createFromLocalFile( $this->imageInputPath, $repositoryPath );
        $this->alternativeBackend->load( $repositoryPath );
    }

    /**
     * Test that file is created in alternative handler
     */
    public function testDispatcherAlternativeBackendCreate()
    {
        $repositoryPath = 'var/test/storage/image-versioned/ezplogo.gif';
        $binaryFile = $this->binaryService->createFromLocalFile( $this->imageInputPath, $repositoryPath );
        $binaryFile2 = $this->alternativeBackend->load( $repositoryPath );

        self::assertEquals( $binaryFile, $binaryFile2 );
    }

    /**
     * Test that file is created in alternative handler
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testDispatcherAlternativeBackendCreateNotFound()
    {
        $repositoryPath = 'var/test/storage/image-versioned/ezplogo.gif';
        $this->binaryService->createFromLocalFile( $this->imageInputPath, $repositoryPath );
        $this->defaultBackend->load( $repositoryPath );
    }
}
