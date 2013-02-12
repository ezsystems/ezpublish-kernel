<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Tests\SignalDispatcher;

use eZ\Publish\Core\SignalSlot;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;

/**
 * @group signalSlot
 */
class LegacySlotsTest extends \PHPUnit_Framework_TestCase
{
    const SIGNAL_SLOT_NS = '\\eZ\\Publish\\Core\\SignalSlot';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $ezpKernelHandlerMock;

    public function setUp()
    {
        $this->ezpKernelHandlerMock = $this->getMock( '\\ezpKernelHandler' );
        parent::setUp();
    }

    /**
     * @covers \eZ\Publish\Core\SignalSlot\Slot\AbstractLegacySlot::getLegacyKernel
     */
    public function testAbstractLegacySlot()
    {
        $ezpKernelHandlerMock = $this->ezpKernelHandlerMock;
        $legacySlot = $this->getMock(
            '\\eZ\\Publish\\Core\\SignalSlot\\Slot\\AbstractLegacySlot',
            // methods
            array(),
            // ctor arguments
            array(
                function () use ( $ezpKernelHandlerMock )
                {
                    return $ezpKernelHandlerMock;
                }
            )
        );

        $reflectionLegacySlot = new \ReflectionObject( $legacySlot );
        $reflectionLegacySlotMethod = $reflectionLegacySlot->getMethod( 'getLegacyKernel' );
        $reflectionLegacySlotMethod->setAccessible( true );

        $this->assertSame( $ezpKernelHandlerMock, $reflectionLegacySlotMethod->invoke( $legacySlot ) );
    }

    public function providerForTestLegacySlots()
    {
        return array(
            array( 'LegacyAssignSectionSlot', 'SectionService\\AssignSectionSignal', array() ),
            array( 'LegacyCopyContentSlot', 'ContentService\\CopyContentSignal', array() ),
            array( 'LegacyCreateLocationSlot', 'LocationService\\CreateLocationSignal', array() ),
            array( 'LegacyDeleteContentSlot', 'ContentService\\DeleteContentSignal', array() ),
            array( 'LegacyDeleteLocationSlot', 'LocationService\\DeleteLocationSignal', array() ),
            array( 'LegacyDeleteVersionSlot', 'ContentService\\DeleteVersionSignal', array() ),
            array( 'LegacyHideLocationSlot', 'LocationService\\HideLocationSignal', array() ),
            array( 'LegacyMoveSubtreeSlot', 'LocationService\\MoveSubtreeSignal', array() ),
            array( 'LegacyPublishVersionSlot', 'ContentService\\PublishVersionSignal', array() ),
            array( 'LegacySetContentStateSlot', 'ObjectStateService\\SetContentStateSignal', array() ),
            array( 'LegacySwapLocationSlot', 'LocationService\\SwapLocationSignal', array() ),
            array( 'LegacyUnhideLocationSlot', 'LocationService\\UnhideLocationSignal', array() ),
            array( 'LegacyUpdateLocationSlot', 'LocationService\\UpdateLocationSignal', array() ),
            array( 'LegacyPublishContentTypeDraftSlot', 'ContentTypeService\\PublishContentTypeDraftSignal', array() ),
        );
    }

    /**
     * @dataProvider providerForTestLegacySlots
     */
    public function testLegacySlotsValidSignal( $slotName, $signalName, array $signalProperties = array() )
    {
        $ezpKernelHandlerMock = $this->ezpKernelHandlerMock;
        $signalClassName = self::SIGNAL_SLOT_NS . '\\Signal\\' . $signalName;
        $slotClassName = self::SIGNAL_SLOT_NS . '\\Slot\\' . $slotName;

        /**
         * @var \eZ\Publish\Core\SignalSlot\Slot $slot
         */
        $slot = new $slotClassName(
            function () use ( $ezpKernelHandlerMock )
            {
                return $ezpKernelHandlerMock;
            }
        );

        $ezpKernelHandlerMock
            ->expects( $this->once() )
            ->method( 'runCallback' )
            ->will( $this->returnValue( null ) );

        /**
         * @var \eZ\Publish\Core\SignalSlot\Signal $signal
         */
        $signal = new $signalClassName( $signalProperties );
        $slot->receive( $signal );
    }

    /**
     * @dataProvider providerForTestLegacySlots
     */
    public function testLegacySlotsInValidSignal( $slotName )
    {
        $ezpKernelHandlerMock = $this->ezpKernelHandlerMock;
        $slotClassName = self::SIGNAL_SLOT_NS . '\\Slot\\' . $slotName;

        /**
         * @var \eZ\Publish\Core\SignalSlot\Slot $slot
         */
        $slot = new $slotClassName(
            function () use ( $ezpKernelHandlerMock )
            {
                return $ezpKernelHandlerMock;
            }
        );

        $ezpKernelHandlerMock
            ->expects( $this->never() )
            ->method( 'runCallback' )
            ->will( $this->returnValue( null ) );

        /**
         * @var \eZ\Publish\Core\SignalSlot\Signal $signal
         */
        $signal = $this->getMock( self::SIGNAL_SLOT_NS . '\\Signal' );
        $slot->receive( $signal );
    }
}
