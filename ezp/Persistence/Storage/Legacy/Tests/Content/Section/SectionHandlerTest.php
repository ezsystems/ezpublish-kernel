<?php
/**
 * File contains: ezp\Persistence\Storage\Legacy\Tests\Content\Section\SectionHandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Tests\Content\Section;
use ezp\Persistence\Storage\Legacy\Tests\TestCase,
    ezp\Persistence\Content\Section,
    ezp\Persistence\Storage\Legacy\Content\Section\Handler,
    ezp\Persistence\Storage\Legacy\Content\Section\Gateway,
    ezp\Persistence\Storage\Legacy\Content\Section\Mapper;

/**
 * Test case for Section Handler
 */
class SectionHandlerTest extends TestCase
{
    /**
     * Section handler
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Section\Handler
     */
    protected $sectionHandler;

    /**
     * Section gateway mock
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Section\Gateway
     */
    protected $gatewayMock;

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Section\Handler::create
     */
    public function testCreate()
    {
        $this->markTestIncomplete();

        $handler     = $this->getSectionHandler();

        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expect( $this->once() )
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
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Section\Handler::update
     */
    public function testUpdate()
    {
        $this->markTestIncomplete();

        $handler     = $this->getSectionHandler();

        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expect( $this->once() )
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
     * @covers ezp\Persistence\Storage\Legacy\Content\Section\Handler::load
     */
    public function testLoad()
    {
        $this->markTestIncomplete();

        $handler     = $this->getSectionHandler();

        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expect( $this->once() )
            ->method( 'loadSectionData' )
            ->with(
                $this->equalTo( 23 )
            )->will(
                $this->returnValue(
                    array(
                        'id'         => '23',
                        'identifier' => 'new_section',
                        'name'       => 'New Section',
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
     * @covers ezp\Persistence\Storage\Legacy\Content\Section\Handler::delete
     */
    public function testDelete()
    {
        $this->markTestIncomplete();

        $handler     = $this->getSectionHandler();

        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expect( $this->once() )
            ->method( 'countContentObjectsInSection' )
            ->with( $this->equalTo( 23 ) )
            ->will( $this->returnValue( 0 ) );

        $gatewayMock->expect( $this->once() )
            ->method( 'deleteSection' )
            ->with(
                $this->equalTo( 23 )
            );

        $result = $handler->load( 23 );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Section\Handler::delete
     * @expectedException RuntimeException
     */
    public function testDeleteFailure()
    {
        $this->markTestIncomplete();

        $handler     = $this->getSectionHandler();

        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expect( $this->once() )
            ->method( 'countContentObjectsInSection' )
            ->with( $this->equalTo( 23 ) )
            ->will( $this->returnValue( 2 ) );

        $gatewayMock->expect( $this->never() )
            ->method( 'deleteSection' );

        $result = $handler->load( 23 );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Section\Handler::assign
     */
    public function testAssign()
    {
        $this->markTestIncomplete();

        $handler     = $this->getSectionHandler();

        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expect( $this->once() )
            ->method( 'assignSectionToContent' )
            ->with(
                $this->equalTo( 23 ),
                $this->equalTo( 42 )
            );

        $result = $handler->assign( 23 );
    }

    /**
     * Returns the section handler to test
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\Section\Handler
     */
    protected function getSectionHandler()
    {
        if ( !isset( $this->sectionHandler ) )
        {
            $this->sectionHandler = new Handler(
                // $this->getGatewayMock()
            );
        }
        return $this->sectionHandler;
    }

    /**
     * Returns a mock for the section gateway
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\Section\Gateway
     */
    protected function getGatewayMock()
    {
        if ( !isset( $this->gatewayMock ) )
        {
            $this->gatewayMock = $this->getMockForAbstractClass(
                'ezp\Persistence\Storage\Legacy\Content\Section\Gateway'
            );
        }
        return $this->gatewayMock;
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
