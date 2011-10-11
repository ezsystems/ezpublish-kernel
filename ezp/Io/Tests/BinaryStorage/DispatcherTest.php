<?php
/**
 * File containing the ezp\Io\Tests\BinaryStorage\BinaryRepositoryLegacyTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Io\Tests\BinaryStorage;
use ezp\Base\ServiceContainer,
    ezp\Io\Storage\Dispatcher,
    ezp\Io\Storage\InMemory,
    ezp\Io\BinaryFile,
    ezp\Io\BinaryFileCreateStruct,
    ezp\Io\BinaryFileUpdateStruct,
    ezp\Io\Tests\BinaryRepositoryTest;

/**
 * @fixme This class should be named LegacyTest according to the file name or
 *        the file name must be adapted.
 */
class DispatcherTest extends BinaryRepositoryTest
{
    /**
     * @var \ezp\Io\Storage\InMemory
     */
    protected $defaultBackend;

    /**
     * @var \ezp\Io\Storage\InMemory
     */
    protected $alternativeBackend;

    /**
     * Setup dispatcher backend for testing
     */
    public function setUp()
    {
        $this->defaultBackend = new InMemory();
        $this->alternativeBackend = new InMemory();
        $sc = new ServiceContainer(
            array(
                '@io_handler' => new Dispatcher(
                    array(
                        'default' => $this->defaultBackend,
                        'backends' => array(
                            array(
                                'handler' => $this->alternativeBackend,
                                'match' => array(
                                    'prefix' => 'var/test/',
                                    'suffix' => '.gif,.jpg',
                                    'contains' => 'image-versioned'
                                )
                            )
                        )
                    )
                )
            )
        );
        $this->binaryService = $sc->getRepository()->getIoService();
        $this->imageInputPath = realpath( __DIR__ . DIRECTORY_SEPARATOR . '..' ) . DIRECTORY_SEPARATOR . 'ezplogo.gif';
    }

    /**
     * Test that file is created in default backend
     */
    public function testDispatcherDefaultBackendCreate()
    {
        $repositoryPath = 'var/test/storage/images/ezplogo.gif';
        $binaryFile = $this->binaryService->createFromLocalFile( $this->imageInputPath, $repositoryPath );
        $binaryFile2 = $this->defaultBackend->load( $repositoryPath );

        self::assertEquals( $binaryFile, $binaryFile2 );
    }

    /**
     * Test that file is created in default backend
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testDispatcherDefaultBackendCreateNotFound()
    {
        $repositoryPath = 'var/test/storage/images/ezplogo.gif';
        $this->binaryService->createFromLocalFile( $this->imageInputPath, $repositoryPath );
        $this->alternativeBackend->load( $repositoryPath );
    }

    /**
     * Test that file is created in alternative backend
     */
    public function testDispatcherAlternativeBackendCreate()
    {
        $repositoryPath = 'var/test/storage/image-versioned/ezplogo.gif';
        $binaryFile = $this->binaryService->createFromLocalFile( $this->imageInputPath, $repositoryPath );
        $binaryFile2 = $this->alternativeBackend->load( $repositoryPath );

        self::assertEquals( $binaryFile, $binaryFile2 );
    }

    /**
     * Test that file is created in alternative backend
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testDispatcherAlternativeBackendCreateNotFound()
    {
        $repositoryPath = 'var/test/storage/image-versioned/ezplogo.gif';
        $this->binaryService->createFromLocalFile( $this->imageInputPath, $repositoryPath );
        $this->defaultBackend->load( $repositoryPath );
    }
}
