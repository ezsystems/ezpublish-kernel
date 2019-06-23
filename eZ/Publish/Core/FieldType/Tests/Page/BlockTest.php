<?php

/**
 * File containing the BlockTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests\Page;

use eZ\Publish\Core\FieldType\Page\Parts\Block;
use eZ\Publish\Core\FieldType\Page\Parts\Item;
use PHPUnit\Framework\TestCase;

class BlockTest extends TestCase
{
    /**
     * @param array $properties
     *
     * @return \eZ\Publish\Core\FieldType\Page\Parts\Block
     */
    private function getBlock(array $properties = [])
    {
        return new Block($properties);
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Page\Parts\Base::__construct
     * @covers \eZ\Publish\Core\FieldType\Page\Parts\Base::getState
     */
    public function testGetState()
    {
        $item = $this->createMock(Item::class);

        $properties = [
            'id' => '4efd68496edd8184aade729b4d2ee17b',
            'name' => 'Main Story',
            'type' => 'Campaign',
            'view' => 'default',
            'overflowId' => '',
            'customAttributes' => null,
            'action' => null,
            'rotation' => null,
            'zoneId' => '6c7f907b831a819ed8562e3ddce5b264',
            'items' => [$item],
            'attributes' => [
                'foo' => 'bar',
                'some' => 'thing',
            ],
        ];

        $block = $this->getBlock($properties);
        $this->assertSame($properties, $block->getState());
    }
}
