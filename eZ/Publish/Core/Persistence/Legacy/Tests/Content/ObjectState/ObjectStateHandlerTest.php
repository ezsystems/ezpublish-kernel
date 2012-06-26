<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\Language\ObjectStateHandlerTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\ObjectState;
use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase,
    eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Handler,
    eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway,
    eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Mapper,
    eZ\Publish\SPI\Persistence\Content\ObjectState,
    eZ\Publish\SPI\Persistence\Content\ObjectState\Group,
    eZ\Publish\SPI\Persistence\Content\ObjectState\InputStruct,
    eZ\Publish\SPI\Persistence\Content\Language;

/**
 * Test case for Object state Handler
 */
class ObjectStateHandlerTest extends TestCase
{
    /**
     * Object state handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Handler
     */
    protected $objectStateHandler;

    /**
     * Object state gateway mock
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway
     */
    protected $gatewayMock;

    /**
     * Object state mapper mock
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Mapper
     */
    protected $mapperMock;

    /**
     * Language handler mock
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler
     */
    protected $languageHandler;


    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Handler::createGroup
     */
    public function testCreateGroup()
    {
        $handler = $this->getObjectStateHandler();
        $mapperMock = $this->getMapperMock();
        $gatewayMock = $this->getGatewayMock();

        $mapperMock->expects( $this->once() )
            ->method( 'createObjectStateGroupFromInputStruct' )
            ->with( $this->equalTo( $this->getInputStructFixture() ) )
            ->will( $this->returnValue( $this->getObjectStateGroupFixture() ) );

        $gatewayMock->expects( $this->once() )
            ->method( 'insertObjectStateGroup' )
            ->with( $this->equalTo( $this->getObjectStateGroupFixture() ) )
            ->will( $this->returnValue( $this->getObjectStateGroupFixture() ) );

        $result = $handler->createGroup( $this->getInputStructFixture() );

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState\\Group',
            $result
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Handler::loadGroup
     */
    public function testLoadGroup()
    {
        $handler = $this->getObjectStateHandler();
        $mapperMock = $this->getMapperMock();
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects( $this->once() )
            ->method( 'loadObjectStateGroupData' )
            ->with( $this->equalTo( 2 ) )
            ->will( $this->returnValue( array( array() ) ) );

        $mapperMock->expects( $this->once() )
            ->method( 'createObjectStateGroupFromData' )
            ->with( $this->equalTo( array( array() ) ) )
            ->will( $this->returnValue( $this->getObjectStateGroupFixture() ) );

        $result = $handler->loadGroup( 2 );

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState\\Group',
            $result
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Handler::loadGroup
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadGroupThrowsNotFoundException()
    {
        $handler = $this->getObjectStateHandler();
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects( $this->once() )
            ->method( 'loadObjectStateGroupData' )
            ->with( $this->equalTo( PHP_INT_MAX ) )
            ->will( $this->returnValue( array() ) );

        $handler->loadGroup( PHP_INT_MAX );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Handler::loadAllGroups
     */
    public function testLoadAllGroups()
    {
        $handler = $this->getObjectStateHandler();
        $mapperMock = $this->getMapperMock();
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects( $this->once() )
            ->method( 'loadObjectStateGroupListData' )
            ->with( $this->equalTo( 0 ), $this->equalTo( -1 ) )
            ->will( $this->returnValue( array( array() ) ) );

        $mapperMock->expects( $this->once() )
            ->method( 'createObjectStateGroupListFromData' )
            ->with( $this->equalTo( array( array() ) ) )
            ->will( $this->returnValue( array( $this->getObjectStateGroupFixture() ) ) );

        $result = $handler->loadAllGroups();

        foreach ( $result as $resultItem )
        {
            $this->assertInstanceOf(
                'eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState\\Group',
                $resultItem
            );
        }
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Handler::loadObjectStates
     */
    public function testLoadObjectStates()
    {
        $handler = $this->getObjectStateHandler();
        $mapperMock = $this->getMapperMock();
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects( $this->once() )
            ->method( 'loadObjectStateListData' )
            ->with( $this->equalTo( 2 ) )
            ->will( $this->returnValue( array( array() ) ) );

        $mapperMock->expects( $this->once() )
            ->method( 'createObjectStateListFromData' )
            ->with( $this->equalTo( array( array() ) ) )
            ->will( $this->returnValue( array( $this->getObjectStateFixture(), $this->getObjectStateFixture() ) ) );

        $result = $handler->loadObjectStates( 2 );

        foreach ( $result as $resultItem )
        {
            $this->assertInstanceOf(
                'eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState',
                $resultItem
            );
        }
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Handler::updateGroup
     */
    public function testUpdateGroup()
    {
        $handler = $this->getObjectStateHandler();
        $mapperMock = $this->getMapperMock();
        $gatewayMock = $this->getGatewayMock();

        $mapperMock->expects( $this->once() )
            ->method( 'createObjectStateGroupFromInputStruct' )
            ->with( $this->equalTo( $this->getInputStructFixture() ) )
            ->will( $this->returnValue( $this->getObjectStateGroupFixture() ) );

        $gatewayMock->expects( $this->once() )
            ->method( 'updateObjectStateGroup' )
            ->with( $this->equalTo( new Group( array( 'id' => 2 ) ) ) );

        $gatewayMock->expects( $this->once() )
            ->method( 'loadObjectStateGroupData' )
            ->with( $this->equalTo( 2 ) )
            ->will( $this->returnValue( array( array() ) ) );

        $mapperMock->expects( $this->once() )
            ->method( 'createObjectStateGroupFromData' )
            ->with( $this->equalTo( array( array() ) ) )
            ->will( $this->returnValue( $this->getObjectStateGroupFixture() ) );

        $result = $handler->updateGroup( 2, $this->getInputStructFixture() );

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState\\Group',
            $result
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Handler::deleteGroup
     */
    public function testDeleteGroup()
    {
        $handler = $this->getObjectStateHandler();
        $mapperMock = $this->getMapperMock();
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects( $this->once() )
            ->method( 'loadObjectStateListData' )
            ->with( $this->equalTo( 2 ) )
            ->will( $this->returnValue( array( array() ) ) );

        $mapperMock->expects( $this->once() )
            ->method( 'createObjectStateListFromData' )
            ->with( $this->equalTo( array( array() ) ) )
            ->will( $this->returnValue( array(
                    new ObjectState( array( 'id' => 1 ) ),
                    new ObjectState( array( 'id' => 2 ) )
            ) ) );

        $gatewayMock->expects( $this->exactly( 2 ) )
            ->method( 'deleteObjectStateLinks' );

        $gatewayMock->expects( $this->exactly( 2 ) )
            ->method( 'deleteObjectState' );

        $gatewayMock->expects( $this->at( 1 ) )
            ->method( 'deleteObjectStateLinks' )
            ->with( $this->equalTo( 1 ) );

        $gatewayMock->expects( $this->at( 2 ) )
            ->method( 'deleteObjectState' )
            ->with( $this->equalTo( 1 ) );

        $gatewayMock->expects( $this->at( 3 ) )
            ->method( 'deleteObjectStateLinks' )
            ->with( $this->equalTo( 2 ) );

        $gatewayMock->expects( $this->at( 4 ) )
            ->method( 'deleteObjectState' )
            ->with( $this->equalTo( 2 ) );

        $gatewayMock->expects( $this->once() )
            ->method( 'deleteObjectStateGroup' )
            ->with( $this->equalTo( 2 ) );

        $handler->deleteGroup( 2 );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Handler::create
     */
    public function testCreate()
    {
        $handler = $this->getObjectStateHandler();
        $mapperMock = $this->getMapperMock();
        $gatewayMock = $this->getGatewayMock();

        $mapperMock->expects( $this->once() )
            ->method( 'createObjectStateFromInputStruct' )
            ->with( $this->equalTo( $this->getInputStructFixture() ) )
            ->will( $this->returnValue( $this->getObjectStateFixture() ) );

        $gatewayMock->expects( $this->once() )
            ->method( 'insertObjectState' )
            ->with( $this->equalTo( $this->getObjectStateFixture() ), $this->equalTo( 2 ) )
            ->will( $this->returnValue( $this->getObjectStateFixture() ) );

        $result = $handler->create( 2, $this->getInputStructFixture() );

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState',
            $result
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Handler::load
     */
    public function testLoad()
    {
        $handler = $this->getObjectStateHandler();
        $mapperMock = $this->getMapperMock();
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects( $this->once() )
            ->method( 'loadObjectStateData' )
            ->with( $this->equalTo( 1 ) )
            ->will( $this->returnValue( array( array() ) ) );

        $mapperMock->expects( $this->once() )
            ->method( 'createObjectStateFromData' )
            ->with( $this->equalTo( array( array() ) ) )
            ->will( $this->returnValue( $this->getObjectStateFixture() ) );

        $result = $handler->load( 1 );

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState',
            $result
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Handler::load
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadThrowsNotFoundException()
    {
        $handler = $this->getObjectStateHandler();
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects( $this->once() )
            ->method( 'loadObjectStateData' )
            ->with( $this->equalTo( PHP_INT_MAX ) )
            ->will( $this->returnValue( array() ) );

        $handler->load( PHP_INT_MAX );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Handler::update
     */
    public function testUpdate()
    {
        $handler = $this->getObjectStateHandler();
        $mapperMock = $this->getMapperMock();
        $gatewayMock = $this->getGatewayMock();

        $mapperMock->expects( $this->once() )
            ->method( 'createObjectStateFromInputStruct' )
            ->with( $this->equalTo( $this->getInputStructFixture() ) )
            ->will( $this->returnValue( $this->getObjectStateFixture() ) );

        $gatewayMock->expects( $this->once() )
            ->method( 'updateObjectState' )
            ->with( $this->equalTo( new ObjectState( array( 'id' => 1 ) ) ) );

        $gatewayMock->expects( $this->once() )
            ->method( 'loadObjectStateData' )
            ->with( $this->equalTo( 1 ) )
            ->will( $this->returnValue( array( array() ) ) );

        $mapperMock->expects( $this->once() )
            ->method( 'createObjectStateFromData' )
            ->with( $this->equalTo( array( array() ) ) )
            ->will( $this->returnValue( $this->getObjectStateFixture() ) );

        $result = $handler->update( 1, $this->getInputStructFixture() );

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState',
            $result
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Handler::setPriority
     */
    public function testSetPriority()
    {
        $handler = $this->getObjectStateHandler();
        $mapperMock = $this->getMapperMock();
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects( $this->once() )
            ->method( 'loadObjectStateData' )
            ->with( $this->equalTo( 2 ) )
            ->will( $this->returnValue( array( array() ) ) );

        $mapperMock->expects( $this->once() )
            ->method( 'createObjectStateFromData' )
            ->with( $this->equalTo( array( array() ) ) )
            ->will( $this->returnValue( new ObjectState( array( 'id' => 2, 'groupId' => 2 ) ) ) );

        $gatewayMock->expects( $this->once() )
            ->method( 'loadCurrentPriorityList' )
            ->with( $this->equalTo( 2 ) )
            ->will( $this->returnValue( array( 1 => 0, 2 => 1 ) ) );

        $gatewayMock->expects( $this->once() )
            ->method( 'reorderPriorities' )
            ->with( $this->equalTo( array( 1 => 0, 2 => 1 ) ), $this->equalTo( array( 2 => 0, 1 => 0 ) ) );

        $handler->setPriority( 2, 0 );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Handler::delete
     */
    public function testDelete()
    {
        $handler = $this->getObjectStateHandler();
        $mapperMock = $this->getMapperMock();
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects( $this->once() )
            ->method( 'loadObjectStateData' )
            ->with( $this->equalTo( 1 ) )
            ->will( $this->returnValue( array( array() ) ) );

        $mapperMock->expects( $this->once() )
            ->method( 'createObjectStateFromData' )
            ->with( $this->equalTo( array( array() ) ) )
            ->will( $this->returnValue( new ObjectState( array( 'groupId' => 2 ) ) ) );

        $gatewayMock->expects( $this->once() )
            ->method( 'deleteObjectState' )
            ->with( $this->equalTo( 1 ) );

        $gatewayMock->expects( $this->once() )
            ->method( 'loadCurrentPriorityList' )
            ->with( $this->equalTo( 2 ) )
            ->will( $this->returnValue( array( 2 => 1 ) ) );

        $gatewayMock->expects( $this->once() )
            ->method( 'reorderPriorities' )
            ->with( $this->equalTo( array( 2 => 1 ) ), $this->equalTo( array( 2 => 1 ) ) );

        $gatewayMock->expects( $this->once() )
            ->method( 'updateObjectStateLinks' )
            ->with( $this->equalTo( 1 ), $this->equalTo( 2 ) );

        $handler->delete( 1 );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Handler::delete
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testDeleteThrowsNotFoundException()
    {
        $handler = $this->getObjectStateHandler();
        $mapperMock = $this->getMapperMock();
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects( $this->once() )
            ->method( 'loadObjectStateData' )
            ->with( $this->equalTo( PHP_INT_MAX ) )
            ->will( $this->returnValue( array() ) );

        $handler->delete( PHP_INT_MAX );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Handler::setObjectState
     */
    public function testSetObjectState()
    {
        $handler = $this->getObjectStateHandler();
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects( $this->once() )
            ->method( 'setObjectState' )
            ->with( $this->equalTo( 42 ), $this->equalTo( 2 ), $this->equalTo( 2 ) );

        $result = $handler->setObjectState( 42, 2, 2 );

        $this->assertEquals( true, $result );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Handler::getObjectState
     */
    public function testGetObjectState()
    {
        $handler = $this->getObjectStateHandler();
        $mapperMock = $this->getMapperMock();
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects( $this->once() )
            ->method( 'loadObjectStateDataForContent' )
            ->with( $this->equalTo( 42 ), $this->equalTo( 2 ) )
            ->will( $this->returnValue( array( array() ) ) );

        $mapperMock->expects( $this->once() )
            ->method( 'createObjectStateFromData' )
            ->with( $this->equalTo( array( array() ) ) )
            ->will( $this->returnValue( $this->getObjectStateFixture() ) );

        $result = $handler->getObjectState( 42, 2 );

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\Content\\ObjectState',
            $result
        );
    }

    /**
     * @return void
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Handler::getContentCount
     */
    public function testGetContentCount()
    {
        $handler = $this->getObjectStateHandler();
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects( $this->once() )
            ->method( 'getContentCount' )
            ->with( $this->equalTo( 1 ) )
            ->will( $this->returnValue( 185 ) );

        $result = $handler->getContentCount( 1 );

        $this->assertEquals( 185, $result );
    }

    /**
     * Returns an object state
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState
     */
    protected function getObjectStateFixture()
    {
        return new ObjectState();
    }

    /**
     * Returns an object state group
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState\Group
     */
    protected function getObjectStateGroupFixture()
    {
        return new Group();
    }

    /**
     * Returns the InputStruct
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState\InputStruct
     */
    protected function getInputStructFixture()
    {
        return new InputStruct();
    }

    /**
     * Returns the object state handler to test
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Handler
     */
    protected function getObjectStateHandler()
    {
        if ( !isset( $this->objectStateHandler ) )
        {
            $this->objectStateHandler = new Handler(
                $this->getGatewayMock(),
                $this->getMapperMock()
            );
        }
        return $this->objectStateHandler;
    }

    /**
     * Returns an object state mapper mock
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Mapper
     */
    protected function getMapperMock()
    {
        if ( !isset( $this->mapperMock ) )
        {
            $this->mapperMock = $this->getMock(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\ObjectState\\Mapper',
                array(),
                array( $this->getLanguageHandlerMock() )
            );
        }
        return $this->mapperMock;
    }

    /**
     * Returns a mock for the object state gateway
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway
     */
    protected function getGatewayMock()
    {
        if ( !isset( $this->gatewayMock ) )
        {
            $this->gatewayMock = $this->getMockForAbstractClass(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\ObjectState\\Gateway'
            );
        }
        return $this->gatewayMock;
    }

    /**
     * Returns a language handler mock
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler
     */
    protected function getLanguageHandlerMock()
    {
        if ( !isset( $this->languageHandler ) )
        {
            $innerLanguageHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Language\\Handler' );
            $innerLanguageHandler->expects( $this->any() )
                ->method( 'loadAll' )
                ->will(
                    $this->returnValue(
                        array(
                            new Language( array(
                                'id' => 2,
                                'languageCode' => 'eng-GB',
                                'name' => 'British english'
                            ) ),
                            new Language( array(
                                'id' => 4,
                                'languageCode' => 'eng-US',
                                'name' => 'US english'
                            ) ),
                            new Language( array(
                                'id' => 8,
                                'languageCode' => 'fre-FR',
                                'name' => 'Français franchouillard'
                            ) )
                        )
                    )
                );
            $this->languageHandler = $this->getMock(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Language\\CachingHandler',
                array( 'getByLocale', 'getById', 'loadByLanguageCode' ),
                array(
                    $innerLanguageHandler,
                    $this->getMock( 'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Language\\Cache' )
                )
            );
        }
        return $this->languageHandler;
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
