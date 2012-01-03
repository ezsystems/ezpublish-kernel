<?php
/**
 * File contains: ezp\Persistence\Storage\Legacy\Tests\Content\StorageHandlerTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Tests\Content;
use ezp\Persistence\Storage\Legacy\Tests\TestCase,
    ezp\Persistence\Storage\Legacy\Content\StorageHandler,
    ezp\Persistence\Content\Field,
    ezp\Persistence\Content\FieldValue,
    ezp\Persistence\Storage\Legacy\Content\StorageRegistry;

/**
 * Test case for Content Handler
 */
class StorageHandlerTest extends TestCase
{
    /**
     * StorageRegistry mock
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\StorageRegistry
     */
    protected $storageRegistryMock;

    /**
     * StorageHandler to test
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\StorageHandler
     */
    protected $storageHandler;

    /**
     * Mock for external storage
     *
     * @var \ezp\Persistence\Fields\Storage
     */
    protected $storageMock;

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\StorageHandler::storeFieldData
     */
    public function testStoreFieldData()
    {
        $storageMock = $this->getStorageMock();
        $storageRegistryMock = $this->getStorageRegistryMock();

        $storageMock->expects( $this->once() )
            ->method( 'storeFieldData' )
            ->with(
                $this->isInstanceOf( 'ezp\\Persistence\\Content\\Field' ),
                $this->equalTo( $this->getContextMock() )
            );

        $storageRegistryMock->expects( $this->once() )
            ->method( 'getStorage' )
            ->with( $this->equalTo( 'foobar' ) )
            ->will( $this->returnValue( $storageMock ) );

        $field = new Field();
        $field->type = 'foobar';
        $field->value = new FieldValue();
        $field->value->data = $this->getMock( 'ezp\\Content\\FieldType\\Value' );

        $handler = $this->getStorageHandler();
        $handler->storeFieldData( $field );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\StorageHandler::getFieldData
     */
    public function testGetFieldDataAvailable()
    {
        $storageMock = $this->getStorageMock();
        $storageRegistryMock = $this->getStorageRegistryMock();

        $storageMock->expects( $this->once() )
            ->method( 'hasFieldData' )
            ->will( $this->returnValue( true ) );
        $storageMock->expects( $this->once() )
            ->method( 'getFieldData' )
            ->with(
                $this->isInstanceOf( 'ezp\\Persistence\\Content\\Field' ),
                $this->equalTo( $this->getContextMock() )
            );

        $storageRegistryMock->expects( $this->once() )
            ->method( 'getStorage' )
            ->with( $this->equalTo( 'foobar' ) )
            ->will( $this->returnValue( $storageMock ) );

        $field = new Field();
        $field->type = 'foobar';
        $field->value = new FieldValue();
        $field->value->data = $this->getMock( 'ezp\\Content\\FieldType\\Value' );

        $handler = $this->getStorageHandler();
        $handler->getFieldData( $field );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\StorageHandler::getFieldData
     */
    public function testGetFieldDataNotAvailable()
    {
        $storageMock = $this->getStorageMock();
        $storageRegistryMock = $this->getStorageRegistryMock();

        $storageMock->expects( $this->once() )
            ->method( 'hasFieldData' )
            ->will( $this->returnValue( false ) );
        $storageMock->expects( $this->never() )
            ->method( 'getFieldData' );

        $storageRegistryMock->expects( $this->once() )
            ->method( 'getStorage' )
            ->with( $this->equalTo( 'foobar' ) )
            ->will( $this->returnValue( $storageMock ) );

        $field = new Field();
        $field->type = 'foobar';
        $field->value = new FieldValue();
        $field->value->data = $this->getMock( 'ezp\\Content\\FieldType\\Value' );

        $handler = $this->getStorageHandler();
        $handler->getFieldData( $field );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\StorageHandler::deleteFieldData
     */
    public function testDeleteFieldData()
    {
        $storageMock = $this->getStorageMock();
        $storageRegistryMock = $this->getStorageRegistryMock();

        $storageMock->expects( $this->once() )
            ->method( 'deleteFieldData' )
            ->with(
                $this->equalTo( array( 1, 2, 3 ) ),
                $this->equalTo( $this->getContextMock() )
            );

        $storageRegistryMock->expects( $this->once() )
            ->method( 'getStorage' )
            ->with( $this->equalTo( 'foobar' ) )
            ->will( $this->returnValue( $storageMock ) );

        $handler = $this->getStorageHandler();
        $handler->deleteFieldData( 'foobar', array( 1, 2, 3 ) );
    }


    /**
     * Returns the StorageHandler to test
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\StorageHandler
     */
    protected function getStorageHandler()
    {
        if ( !isset( $this->storageHandler ) )
        {
            $this->storageHandler = new StorageHandler(
                $this->getStorageRegistryMock(),
                $this->getContextMock()
            );
        }
        return $this->storageHandler;
    }

    /**
     * Returns a context mock
     *
     * @return array
     */
    protected function getContextMock()
    {
        return array( 23, 42 );
    }

    /**
     * Returns a StorageRegistry mock
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\StorageRegistry
     */
    protected function getStorageRegistryMock()
    {
        if ( !isset( $this->storageRegistryMock ) )
        {
            $this->storageRegistryMock = $this->getMock(
                'ezp\\Persistence\\Storage\\Legacy\\Content\\StorageRegistry'
            );
        }
        return $this->storageRegistryMock;
    }

    /**
     * Returns a Storage mock
     *
     * @return \ezp\Persistence\Fields\Storage
     */
    protected function getStorageMock()
    {
        if ( !isset( $this->storageMock ) )
        {
            $this->storageMock = $this->getMock(
                'ezp\\Persistence\\Fields\\Storage'
            );
        }
        return $this->storageMock;
    }

    /**
     * Returns the test suite with all tests declared in this class.
     *
     * @return \PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        return new \PHPUnit_Framework_TestSuite( __CLASS__ );
    }
}
