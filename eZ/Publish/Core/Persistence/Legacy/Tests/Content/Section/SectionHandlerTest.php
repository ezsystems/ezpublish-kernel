<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\Section\SectionHandlerTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\Section;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\SPI\Persistence\Content\Section;
use eZ\Publish\Core\Persistence\Legacy\Content\Section\Handler;

/**
 * Test case for Section Handler
 */
class SectionHandlerTest extends TestCase
{
    /**
     * Section handler
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Section\Handler
     */
    protected $sectionHandler;

    /**
     * Section gateway mock
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway
     */
    protected $gatewayMock;

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Section\Handler::create
     *
     * @return void
     */
    public function testCreate()
    {
        $handler = $this->getSectionHandler();

        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects( $this->once() )
            ->method( 'insertSection' )
            ->with(
                $this->equalTo( 'New Section' ),
                $this->equalTo( 'new_section' )
            )->will( $this->returnValue( 23 ) );

        $sectionRef = new Section();
        $sectionRef->id = 23;
        $sectionRef->name = 'New Section';
        $sectionRef->identifier = 'new_section';

        $result = $handler->create( 'New Section', 'new_section' );

        $this->assertEquals(
            $sectionRef,
            $result
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Section\Handler::update
     *
     * @return void
     */
    public function testUpdate()
    {
        $handler = $this->getSectionHandler();

        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects( $this->once() )
            ->method( 'updateSection' )
            ->with(
                $this->equalTo( 23 ),
                $this->equalTo( 'New Section' ),
                $this->equalTo( 'new_section' )
            );

        $sectionRef = new Section();
        $sectionRef->id = 23;
        $sectionRef->name = 'New Section';
        $sectionRef->identifier = 'new_section';

        $result = $handler->update( 23, 'New Section', 'new_section' );

        $this->assertEquals(
            $sectionRef,
            $result
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Section\Handler::load
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Section\Handler::createSectionFromArray
     */
    public function testLoad()
    {
        $handler = $this->getSectionHandler();

        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects( $this->once() )
            ->method( 'loadSectionData' )
            ->with(
                $this->equalTo( 23 )
            )->will(
                $this->returnValue(
                    array(
                        array(
                            'id' => '23',
                            'identifier' => 'new_section',
                            'name' => 'New Section',
                        ),
                    )
                )
            );

        $sectionRef = new Section();
        $sectionRef->id = 23;
        $sectionRef->name = 'New Section';
        $sectionRef->identifier = 'new_section';

        $result = $handler->load( 23 );

        $this->assertEquals(
            $sectionRef,
            $result
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Section\Handler::loadAll
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Section\Handler::createSectionFromArray
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Section\Handler::createSectionsFromArray
     */
    public function testLoadAll()
    {
        $handler = $this->getSectionHandler();

        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects( $this->once() )
        ->method( 'loadAllSectionData' )
        ->will(
            $this->returnValue(
                array(
                    array(
                        'id' => '23',
                        'identifier' => 'new_section',
                        'name' => 'New Section',
                    ),
                    array(
                        'id' => '46',
                        'identifier' => 'new_section2',
                        'name' => 'New Section2',
                    ),
                )
            )
        );

        $sectionRef = new Section();
        $sectionRef->id = 23;
        $sectionRef->name = 'New Section';
        $sectionRef->identifier = 'new_section';

        $sectionRef2 = new Section();
        $sectionRef2->id = 46;
        $sectionRef2->name = 'New Section2';
        $sectionRef2->identifier = 'new_section2';

        $result = $handler->loadAll();

        $this->assertEquals(
            array( $sectionRef, $sectionRef2 ),
            $result
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Section\Handler::loadByIdentifier
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Section\Handler::createSectionFromArray
     */
    public function testLoadByIdentifier()
    {
        $handler = $this->getSectionHandler();

        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects( $this->once() )
            ->method( 'loadSectionDataByIdentifier' )
            ->with(
                $this->equalTo( 'new_section' )
            )->will(
                $this->returnValue(
                    array(
                        array(
                            'id' => '23',
                            'identifier' => 'new_section',
                            'name' => 'New Section',
                        ),
                    )
                )
            );

        $sectionRef = new Section();
        $sectionRef->id = 23;
        $sectionRef->name = 'New Section';
        $sectionRef->identifier = 'new_section';

        $result = $handler->loadByIdentifier( 'new_section' );

        $this->assertEquals(
            $sectionRef,
            $result
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Section\Handler::delete
     *
     * @return void
     */
    public function testDelete()
    {
        $handler = $this->getSectionHandler();

        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects( $this->once() )
            ->method( 'countContentObjectsInSection' )
            ->with( $this->equalTo( 23 ) )
            ->will( $this->returnValue( 0 ) );

        $gatewayMock->expects( $this->once() )
            ->method( 'deleteSection' )
            ->with(
                $this->equalTo( 23 )
            );

        $result = $handler->delete( 23 );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Section\Handler::delete
     * @expectedException RuntimeException
     */
    public function testDeleteFailure()
    {
        $handler = $this->getSectionHandler();

        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects( $this->once() )
            ->method( 'countContentObjectsInSection' )
            ->with( $this->equalTo( 23 ) )
            ->will( $this->returnValue( 2 ) );

        $gatewayMock->expects( $this->never() )
            ->method( 'deleteSection' );

        $result = $handler->delete( 23 );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Section\Handler::assign
     *
     * @return void
     */
    public function testAssign()
    {
        $handler = $this->getSectionHandler();

        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects( $this->once() )
            ->method( 'assignSectionToContent' )
            ->with(
                $this->equalTo( 23 ),
                $this->equalTo( 42 )
            );

        $result = $handler->assign( 23, 42 );
    }

    /**
     * Returns the section handler to test
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Section\Handler
     */
    protected function getSectionHandler()
    {
        if ( !isset( $this->sectionHandler ) )
        {
            $this->sectionHandler = new Handler(
                $this->getGatewayMock()
            );
        }
        return $this->sectionHandler;
    }

    /**
     * Returns a mock for the section gateway
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway
     */
    protected function getGatewayMock()
    {
        if ( !isset( $this->gatewayMock ) )
        {
            $this->gatewayMock = $this->getMockForAbstractClass(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Section\\Gateway'
            );
        }
        return $this->gatewayMock;
    }
}
