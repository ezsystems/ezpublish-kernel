<?php
/**
 * File containing the BlockTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests\Page;

use eZ\Publish\Core\FieldType\Page\Parts\Block;

class BlockTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $pageServiceMock;

    /**
     * @param array $properties
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Block
     */
    private function getBlock( array $properties = array() )
    {
        if ( !isset( $this->pageServiceMock ) )
        {
            $this->pageServiceMock = $this
                ->getMockBuilder( 'eZ\\Publish\\Core\\FieldType\\Page\\PageService' )
                ->disableOriginalConstructor()
                ->getMock();
        }

        return new Block( $this->pageServiceMock, $properties );
    }

    /**
     * @covers eZ\Publish\Core\FieldType\Page\Parts\Base::__construct
     * @covers eZ\Publish\Core\FieldType\Page\Parts\Block::getValidItems
     */
    public function testGetValidItems()
    {
        $block = $this->getBlock();
        $this->pageServiceMock
            ->expects( $this->once() )
            ->method( 'getValidBlockItems' )
            ->will(
                $this->returnValue( array() )
            );

        // Calling twice. First time the page service should be called while it shouldn't the 2nd time.
        $block->getValidItems();
        $block->getValidItems();
    }

    /**
     * @covers eZ\Publish\Core\FieldType\Page\Parts\Base::__construct
     * @covers eZ\Publish\Core\FieldType\Page\Parts\Block::getWaitingItems
     */
    public function testGetWaitingItems()
    {
        $block = $this->getBlock();
        $this->pageServiceMock
            ->expects( $this->once() )
            ->method( 'getWaitingBlockItems' )
            ->will(
                $this->returnValue( array() )
            );

        // Calling twice. First time the page service should be called while it shouldn't the 2nd time.
        $block->getWaitingItems();
        $block->getWaitingItems();
    }

    /**
     * @covers eZ\Publish\Core\FieldType\Page\Parts\Base::__construct
     * @covers eZ\Publish\Core\FieldType\Page\Parts\Block::getArchivedItems
     */
    public function testGetArchivedItems()
    {
        $block = $this->getBlock();
        $this->pageServiceMock
            ->expects( $this->once() )
            ->method( 'getArchivedBlockItems' )
            ->will(
                $this->returnValue( array() )
            );

        // Calling twice. First time the page service should be called while it shouldn't the 2nd time.
        $block->getArchivedItems();
        $block->getArchivedItems();
    }

    /**
     * @covers eZ\Publish\Core\FieldType\Page\Parts\Base::__construct
     * @covers eZ\Publish\Core\FieldType\Page\Parts\Base::getState
     */
    public function testGetState()
    {
        $item = $this
            ->getMockBuilder( 'eZ\\Publish\\Core\\FieldType\\Page\\Parts\\Item' )
            ->disableOriginalConstructor()
            ->getMock();

        $properties = array(
            'id'                => '4efd68496edd8184aade729b4d2ee17b',
            'name'              => 'Main Story',
            'type'              => 'Campaign',
            'view'              => 'default',
            'overflowId'        => '',
            'customAttributes'  => array(),
            'action'            => null,
            'items'             => array( $item ),
            'attributes'        => array(
                'foo'   => 'bar',
                'some'  => 'thing'
            )
        );

        $block = $this->getBlock( $properties );
        $this->assertSame( $properties, $block->getState() );
    }
}
