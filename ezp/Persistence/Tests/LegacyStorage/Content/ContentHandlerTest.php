<?php
/**
 * File contains: ezp\Persistence\Tests\LegacyStorage\Content\ContentHandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests\LegacyStorage\Content;
use ezp\Persistence\Tests\LegacyStorage\TestCase,
    ezp\Persistence\Content,
    ezp\Persistence\Content\Field,
    ezp\Persistence\Content\FieldValue,
    ezp\Persistence\Content\Version,
    ezp\Persistence\Content\ContentCreateStruct,
    ezp\Persistence\LegacyStorage\Content\StorageFieldValue,
    ezp\Persistence\LegacyStorage\Content\ContentHandler;

/**
 * Test case for ContentHandler
 */
class ContentHandlerTest extends TestCase
{
    /**
     * @return void
     * @covers ezp\Persistence\LegacyStorage\ContentHandler::__construct
     */
    public function testCtor()
    {
        $gatewayMock = $this->getGatewayMock();
        $mapperMock = $this->getMapperMock();
        $storageRegistryMock = $this->getStorageRegistryMock();

        $handler = new ContentHandler(
            $gatewayMock,
            $mapperMock,
            $storageRegistryMock
        );

        $this->assertAttributeSame(
            $gatewayMock,
            'contentGateway',
            $handler
        );
        $this->assertAttributeSame(
            $mapperMock,
            'mapper',
            $handler
        );
        $this->assertAttributeSame(
            $storageRegistryMock,
            'storageRegistry',
            $handler
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\LegacyStorage\ContentHandler::create
     * @todo Current method way to complex to test, refactor!
     */
    public function testCreate()
    {
        $mapperMock     = $this->getMapperMock();
        $gatewayMock    = $this->getGatewayMock();
        $storageRegMock = $this->getStorageRegistryMock();
        $storageMock    = $this->getMock(
            'ezp\\Persistence\\Fields\\StorageInterface'
        );

        $handler = new ContentHandler(
            $gatewayMock,
            $mapperMock,
            $storageRegMock
        );

        $mapperMock->expects( $this->once() )
            ->method( 'createContentFromCreateStruct' )
            ->with(
                $this->isInstanceOf(
                    'ezp\\Persistence\\Content\\ContentCreateStruct'
                )
            )->will(
                $this->returnValue( new Content() )
            );
        $mapperMock->expects( $this->once() )
            ->method( 'createVersionForContent' )
            ->with(
                $this->isInstanceOf(
                    'ezp\\Persistence\\Content'
                )
            )->will(
                $this->returnValue( new Version() )
            );
        $mapperMock->expects( $this->exactly( 2 ) )
            ->method( 'convertToStorageValue' )
            ->with(
                $this->isInstanceOf(
                    'ezp\\Persistence\\Content\\Field'
                )
            )->will(
                $this->returnValue( new StorageFieldValue() )
            );

        $gatewayMock->expects( $this->once() )
            ->method( 'insertContentObject' )
            ->with(
                $this->isInstanceOf( 'ezp\\Persistence\\Content' )
            )->will( $this->returnValue( 23 ) );

        $gatewayMock->expects( $this->once() )
            ->method( 'insertVersion' )
            ->with(
                $this->isInstanceOf( 'ezp\\Persistence\\Content\\Version' )
            )->will( $this->returnValue( 1 ) );

        $gatewayMock->expects( $this->exactly( 2 ) )
            ->method( 'insertNewField' )
            ->with(
                $this->isInstanceOf( 'ezp\\Persistence\\Content' ),
                $this->isInstanceOf( 'ezp\\Persistence\\Content\\Field' ),
                $this->isInstanceOf( 'ezp\\Persistence\\LegacyStorage\\Content\\StorageFieldValue' )
            )->will( $this->returnValue( 42 ) );

        $storageRegMock->expects( $this->exactly( 2 ) )
            ->method( 'getStorage' )
            ->with( $this->equalTo( 'some-type' ) )
            ->will(
                $this->returnValue( $storageMock )
            );

        $storageMock->expects( $this->exactly( 2 ) )
            ->method( 'storeFieldData' )
            ->with(
                $this->equalTo( 42 ),
                $this->isInstanceOf(
                    'ezp\\Persistence\\Content\\FieldValue'
                )
            );

        $res = $handler->create( $this->getContentCreateStructFixture() );

        $this->assertInstanceOf(
            'ezp\\Persistence\\Content',
            $res,
            'Content not created'
        );
        $this->assertEquals(
            23,
            $res->id,
            'Content ID not set correctly'
        );
        $this->assertInternalType(
            'array',
            $res->versionInfos,
            'Version infos not created'
        );
        $this->assertEquals(
            1,
            $res->versionInfos[0]->id,
            'Version ID not set correctly'
        );
        $this->assertEquals(
            2,
            count( $res->versionInfos[0]->fields ),
            'Fields not set correctly in version'
        );
        foreach ( $res->versionInfos[0]->fields as $field )
        {
            $this->assertEquals(
                42,
                $field->id,
                'Field ID not set correctly'
            );
        }
    }

    /**
     * Returns a ContentCreateStruct fixture.
     *
     * @return ContentCreateStruct
     */
    public function getContentCreateStructFixture()
    {
        $struct = new ContentCreateStruct();

        $firstField        = new Field();
        $firstField->type  = 'some-type';
        $firstField->value = new FieldValue();

        $secondField = clone $firstField;

        $struct->fields = array(
            $firstField, $secondField
        );
        return $struct;
    }

    /**
     * Returns a StorageRegistry mock.
     *
     * @return StorageRegistry
     */
    protected function getStorageRegistryMock()
    {
        return $this->getMock(
            'ezp\\Persistence\\LegacyStorage\\Content\\StorageRegistry',
            array( 'getStorage' )
        );
    }

    /**
     * Returns a Mapper mock.
     *
     * @return Mapper
     */
    protected function getMapperMock()
    {
        return $this->getMock(
            'ezp\\Persistence\\LegacyStorage\\Content\\Mapper'
        );
    }

    /**
     * Returns a mock object for the ContentGateway.
     *
     * @return ContentGateway
     */
    protected function getGatewayMock()
    {
        return $this->getMockForAbstractClass(
            'ezp\\Persistence\\LegacyStorage\\Content\\ContentGateway'
        );
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
