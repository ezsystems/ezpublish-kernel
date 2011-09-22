<?php
/**
 * File contains: ezp\Base\Tests\ServiceTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base\Tests;
use ezp\Base\Service,
    ReflectionClass;

/**
 * Test case for Collection\ReadOnly class
 *
 */
class ServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \ezp\Base\Service
     */
    private $service;

    /**
     * ReflectionClass for service
     * @var \ReflectionClass
     */
    private $serviceRC;

    public function setUp()
    {
        parent::setUp();

        $repositoryStub = $this->getMockBuilder( 'ezp\\Base\\Repository' )
            ->setConstructorArgs( array(
                $this->getMock( 'ezp\\Persistence\\Repository\\Handler' ),
                $this->getMock( 'ezp\\User' )
            ) )
            ->getMock();

        $this->service = $this->getMockBuilder( 'ezp\\Base\\Service' )
             ->setConstructorArgs( array(
                $repositoryStub,
                $this->getMock( 'ezp\\Persistence\\Repository\\Handler' )
             ) )
             ->getMockForAbstractClass();

        $this->serviceRC = new ReflectionClass( 'ezp\\Base\\Service' );
    }

    /**
     * Basic test for the Observer::attach() method
     */
    public function testAttach()
    {
        $observer = $this->getMock( 'ezp\\Base\\Observer' );
        $return = $this->service->attach( $observer );
        self::assertSame( $this->service, $return );

        $observersProperty = $this->getObserversPropertyValue();

        self::assertArrayHasKey( 'update', $observersProperty );
        self::assertInstanceOf( 'SplObjectStorage', $observersProperty['update'] );
        self::assertEquals( 1, count( $observersProperty['update'] ) );
        self::assertTrue( $observersProperty['update']->contains( $observer ) );
    }

    /**
     * Tests the behaviour when the same observer is attached twice
     */
    public function testAttachSameObserver()
    {
        $observer = $this->getMock( 'ezp\\Base\\Observer' );
        $return = $this->service->attach( $observer );
        $return = $this->service->attach( $observer );

        $observersProperty = $this->getObserversPropertyValue();

        self::assertArrayHasKey( 'update', $observersProperty );
        self::assertInstanceOf( 'SplObjectStorage', $observersProperty['update'] );
        self::assertEquals( 1, count( $observersProperty['update'] ) );
        self::assertTrue( $observersProperty['update']->contains( $observer ) );
    }

    public function testAttachCustomEvent()
    {
        $observer = $this->getMock( 'ezp\\Base\\Observer' );
        $return = $this->service->attach( $observer, 'myUpdate' );

        $observersProperty = $this->getObserversPropertyValue();

        self::assertArrayHasKey( 'myUpdate', $observersProperty );
        self::assertArrayNotHasKey( 'update', $observersProperty );
        self::assertInstanceOf( 'SplObjectStorage', $observersProperty['myUpdate'] );
        self::assertEquals( 1, count( $observersProperty['myUpdate'] ) );
        self::assertTrue( $observersProperty['myUpdate']->contains( $observer ) );
    }

    public function testDetach()
    {
        $observer = $this->getMock( 'ezp\\Base\\Observer' );
        $this->service->attach( $observer, 'update' );

        $observersProperty = $this->getObserversPropertyValue();

        self::assertArrayHasKey( 'update', $observersProperty );
        self::assertInstanceOf( 'SplObjectStorage', $observersProperty['update'] );
        self::assertEquals( 1, count( $observersProperty['update'] ) );
        self::assertTrue( $observersProperty['update']->contains( $observer ) );

        $this->service->detach( $observer );

        $observersProperty = $this->getObserversPropertyValue();

        self::assertArrayNotHasKey( 'update', $observersProperty );
    }

    public function testNotify()
    {
        $observerOne = $this->getMockBuilder( 'ezp\\Base\\Observer' )
            ->setMethods( array( 'update' ) )
            ->getMock();

        $observerOne->expects( $this->once() )
            ->method( 'update' )
            ->with( $this->service, 'update', null )
            ->will( $this->returnValue( $observerOne ) );

        $this->service->attach( $observerOne, 'update' );

        $observerTwo = $this->getMockBuilder( 'ezp\\Base\\Observer' )
            ->setMethods( array( 'update' ) )
            ->getMock();

        $observerTwo->expects( $this->never() )
            ->method( 'update' );

        $this->service->attach( $observerTwo, 'delete' );

        $this->service->notify( 'update' );
    }

    /**
     * Returns the value of ezp\Base\Service::$observers
     */
    private function getObserversPropertyValue()
    {
        $observersRM = $this->serviceRC->getProperty( 'observers' );
        $observersRM->setAccessible( true );
        return $observersRM->getValue( $this->service );
    }
}
