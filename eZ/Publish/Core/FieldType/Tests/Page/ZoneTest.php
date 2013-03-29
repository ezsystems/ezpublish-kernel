<?php
/**
 * File containing the ZoneTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\FieldType\Tests\Page;

use eZ\Publish\Core\FieldType\Page\Parts\Zone;
use eZ\Publish\Core\FieldType\Page\Parts\Block;

class ZoneTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers eZ\Publish\Core\FieldType\Page\Parts\Zone::__construct
     * @covers eZ\Publish\Core\FieldType\Page\Parts\Base::__construct
     * @covers eZ\Publish\Core\FieldType\Page\Parts\Base::getState
     */
    public function testGetState()
    {
        $block1 = new Block( array( 'id' => 'foo' ) );
        $block2 = new Block( array( 'id' => 'bar' ) );
        $block3 = new Block( array( 'id' => 'baz' ) );
        $properties = array(
            'id'            => 'my_zone_id',
            'identifier'    => 'somezone',
            'action'        => Zone::ACTION_ADD,
            'blocks'        => array( $block1, $block2, $block3 )
        );
        $zone = new Zone( $properties );
        $this->assertEquals(
            $properties + array(
                'blocksById'     => array(
                    'foo'   => $block1,
                    'bar'   => $block2,
                    'baz'   => $block3
                ),
                'attributes'    => array()
            ),
            $zone->getState()
        );
    }
}
