<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\StorageHandlerTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\FieldValue;

/**
 * Test case for Content Handler
 */
class StorageHandlerTest extends TestCase
{
    /**
     * StorageRegistry mock
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\StorageRegistry
     */
    protected $storageRegistryMock;

    /**
     * StorageHandler to test
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler
     */
    protected $storageHandler;

    /**
     * Mock for external storage
     *
     * @var \eZ\Publish\SPI\FieldType\FieldStorage
     */
    protected $storageMock;

    /**
     * Mock for versionInfo
     *
     * @var \eZ\Publish\Core\Repository\Values\Content\VersionInfo
     */
    protected $versionInfoMock;

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler::storeFieldData
     *
     * @return void
     */
    public function testStoreFieldData()
    {
        $storageMock = $this->getStorageMock();
        $storageRegistryMock = $this->getStorageRegistryMock();

        $storageMock->expects( $this->once() )
            ->method( 'storeFieldData' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\VersionInfo' ),
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field' ),
                $this->equalTo( $this->getContextMock() )
            );

        $storageRegistryMock->expects( $this->once() )
            ->method( 'getStorage' )
            ->with( $this->equalTo( 'foobar' ) )
            ->will( $this->returnValue( $storageMock ) );

        $field = new Field();
        $field->type = 'foobar';
        $field->value = new FieldValue();

        $handler = $this->getStorageHandler();
        $handler->storeFieldData( $this->getVersionInfoMock(), $field );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler::getFieldData
     *
     * @return void
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
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\VersionInfo' ),
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\Field' ),
                $this->equalTo( $this->getContextMock() )
            );

        $storageRegistryMock->expects( $this->once() )
            ->method( 'getStorage' )
            ->with( $this->equalTo( 'foobar' ) )
            ->will( $this->returnValue( $storageMock ) );

        $field = new Field();
        $field->type = 'foobar';
        $field->value = new FieldValue();

        $handler = $this->getStorageHandler();
        $handler->getFieldData( $this->getVersionInfoMock(), $field );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler::getFieldData
     *
     * @return void
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

        $handler = $this->getStorageHandler();
        $handler->getFieldData( $this->getVersionInfoMock(), $field );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler::deleteFieldData
     *
     * @return void
     */
    public function testDeleteFieldData()
    {
        $storageMock = $this->getStorageMock();
        $storageRegistryMock = $this->getStorageRegistryMock();

        $storageMock->expects( $this->once() )
            ->method( 'deleteFieldData' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\VersionInfo' ),
                $this->equalTo( array( 1, 2, 3 ) ),
                $this->equalTo( $this->getContextMock() )
            );

        $storageRegistryMock->expects( $this->once() )
            ->method( 'getStorage' )
            ->with( $this->equalTo( 'foobar' ) )
            ->will( $this->returnValue( $storageMock ) );

        $handler = $this->getStorageHandler();
        $handler->deleteFieldData( 'foobar', new VersionInfo(), array( 1, 2, 3 ) );
    }

    /**
     * Returns the StorageHandler to test
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\StorageHandler
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
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\StorageRegistry
     */
    protected function getStorageRegistryMock()
    {
        if ( !isset( $this->storageRegistryMock ) )
        {
            $this->storageRegistryMock = $this->getMock(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\StorageRegistry',
                array(),
                array( array() )
            );
        }
        return $this->storageRegistryMock;
    }

    /**
     * Returns a Storage mock
     *
     * @return \eZ\Publish\SPI\FieldType\FieldStorage
     */
    protected function getStorageMock()
    {
        if ( !isset( $this->storageMock ) )
        {
            $this->storageMock = $this->getMock(
                'eZ\\Publish\\SPI\\FieldType\\FieldStorage'
            );
        }
        return $this->storageMock;
    }

    protected function getVersionInfoMock()
    {
        if ( !isset( $this->versionInfoMock ) )
        {
            $this->versionInfoMock = $this->getMock(
                'eZ\\Publish\\SPI\\Persistence\\Content\\VersionInfo'
            );
        }
        return $this->versionInfoMock;
    }
}
