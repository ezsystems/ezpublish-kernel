<?php

/**
 * File containing the ZoneTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests\Page;

use eZ\Publish\Core\FieldType\Page\Parts\Zone;
use eZ\Publish\Core\FieldType\Page\Parts\Block;
use PHPUnit\Framework\TestCase;

class ZoneTest extends TestCase
{
    /**
     * @covers \eZ\Publish\Core\FieldType\Page\Parts\Zone::__construct
     * @covers \eZ\Publish\Core\FieldType\Page\Parts\Base::__construct
     * @covers \eZ\Publish\Core\FieldType\Page\Parts\Base::getState
     */
    public function testGetState()
    {
        $block1 = new Block(['id' => 'foo']);
        $block2 = new Block(['id' => 'bar']);
        $block3 = new Block(['id' => 'baz']);
        $properties = [
            'id' => 'my_zone_id',
            'identifier' => 'somezone',
            'action' => Zone::ACTION_ADD,
            'blocks' => [$block1, $block2, $block3],
        ];
        $zone = new Zone($properties);
        $this->assertEquals(
            $properties + [
                'blocksById' => [
                    'foo' => $block1,
                    'bar' => $block2,
                    'baz' => $block3,
                ],
                'attributes' => [],
            ],
            $zone->getState()
        );
    }
}
