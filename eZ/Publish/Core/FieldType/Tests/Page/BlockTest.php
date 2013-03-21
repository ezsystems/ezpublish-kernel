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
     * @param array $properties
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Block
     */
    private function getBlock( array $properties = array() )
    {
        return new Block( $properties );
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
            'customAttributes'  => null,
            'action'            => null,
            'rotation'          => null,
            'zoneId'            => '6c7f907b831a819ed8562e3ddce5b264',
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
