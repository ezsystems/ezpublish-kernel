<?php
/**
 * File contains: ezp\Persistence\Storage\Legacy\Tests\Content\Type\ContentTypeHandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Tests\Content\Type\UpdateHandler;
use ezp\Persistence\Content\Type,

    ezp\Persistence\Storage\Legacy\Content\Type\Gateway,
    ezp\Persistence\Storage\Legacy\Content\Type\ContentUpdater,
    ezp\Persistence\Storage\Legacy\Content\Type\Update\Handler\EzcDatabase;

/**
 * Test case for Content Type Handler.
 */
class ContentTypeHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gateway mock
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Type\Gateway
     */
    protected $gatewayMock;

    /**
     * Content Updater mock
     *
     * @var ezp\Persistence\Storage\Legacy\Content\Type\ContentUpdater
     */
    protected $contentUpdaterMock;

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Update\Handler\EzcDatabase::updateContentObjects
     */
    public function testUpdateContentObjects()
    {
        $handler = $this->getUpdateHandler();

        $updaterMock = $this->getContentUpdaterMock();

        $updaterMock->expects( $this->once() )
            ->method( 'determineActions' )
            ->with(
                $this->isInstanceOf(
                    'ezp\\Persistence\\Content\\Type'
                ),
                $this->isInstanceOf(
                    'ezp\\Persistence\\Content\\Type'
                )
            )->will( $this->returnValue( array() ) );

        $updaterMock->expects( $this->once() )
            ->method( 'applyUpdates' )
            ->with(
                $this->equalTo( 23 ),
                $this->equalTo( array() )
            );

        $types = $this->getTypeFixtures();

        $handler->updateContentObjects( $types['from'], $types['to'] );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Update\Handler\EzcDatabase::deleteOldType
     */
    public function testDeleteOldType()
    {
        $handler = $this->getUpdateHandler();

        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects( $this->once() )
            ->method( 'deleteType' )
            ->with(
                $this->equalTo( 23 ),
                $this->equalTo( 0 )
            );
        $gatewayMock->expects( $this->once() )
            ->method( 'deleteFieldDefinitionsForType' )
            ->with(
                $this->equalTo( 23 ),
                $this->equalTo( 0 )
            );

        $types = $this->getTypeFixtures();

        $handler->deleteOldType( $types['from'], $types['to'] );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Update\Handler\EzcDatabase::publishNewType
     */
    public function testPublishNewType()
    {
        $handler = $this->getUpdateHandler();

        $gatewayMock = $this->getGatewayMock();
        $updaterMock = $this->getContentUpdaterMock();

        $gatewayMock->expects( $this->once() )
            ->method( 'publishTypeAndFields' )
            ->with( $this->equalTo( 23 ), $this->equalTo( 1 ), $this->equalTo( 0 ) );

        $types = $this->getTypeFixtures();

        $handler->publishNewType( $types['to'], 0 );
    }

    /**
     * Returns an arra with 'from' and 'to' types
     *
     * @return \ezp\Persistence\Content\Type[]
     */
    protected function getTypeFixtures()
    {
        $types = array();

        $types['from'] = new Type();
        $types['from']->id = 23;
        $types['from']->status = Type::STATUS_DEFINED;

        $types['to'] = new Type();
        $types['to']->id = 23;
        $types['to']->status = Type::STATUS_DRAFT;

        return $types;
    }

    /**
     * Returns the Update Handler to test
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\Type\Update\Handler\EzcDatabase
     */
    protected function getUpdateHandler()
    {
        return new EzcDatabase(
            $this->getGatewayMock(),
            $this->getContentUpdaterMock()
        );
    }

    /**
     * Returns a gateway mock
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\Type\Gateway
     */
    protected function getGatewayMock()
    {
        if ( !isset( $this->gatewayMock ) )
        {
            $this->gatewayMock = $this->getMockForAbstractClass(
                'ezp\\Persistence\\Storage\\Legacy\\Content\\Type\\Gateway'
            );
        }
        return $this->gatewayMock;
    }

    /**
     * Returns a Content Updater mock
     *
     * @return ezp\Persistence\Storage\Legacy\Content\Type\ContentUpdater
     */
    protected function getContentUpdaterMock()
    {
        if ( !isset( $this->contentUpdaterMock ) )
        {
            $this->contentUpdaterMock = $this->getMock(
                'ezp\\Persistence\\Storage\\Legacy\\Content\\Type\\ContentUpdater',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->contentUpdaterMock;
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
