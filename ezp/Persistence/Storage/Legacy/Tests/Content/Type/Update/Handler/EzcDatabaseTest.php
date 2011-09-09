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
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\Update\Handler::performUpdate
     */
    public function testPerformUpdate()
    {
        $handler = $this->getUpdateHandler();

        $gatewayMock = $this->getGatewayMock();
        $updaterMock = $this->getContentUpdaterMock();

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

        $gatewayMock->expects( $this->once() )
            ->method( 'publishTypeAndFields' )
            ->with( $this->equalTo( 23 ), $this->equalTo( 1 ) );

        $fromType = new Type();
        $fromType->id     = 23;
        $fromType->status = Type::STATUS_DEFINED;

        $toType = new Type();
        $toType->id     = 23;
        $toType->status = Type::STATUS_DRAFT;

        $handler->performUpdate( $fromType, $toType );
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
