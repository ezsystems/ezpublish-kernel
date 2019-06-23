<?php

/**
 * File containing the PageTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\FieldType\Tests\Page;

use eZ\Publish\Core\FieldType\Page\Parts\Page;
use eZ\Publish\Core\FieldType\Page\Parts\Zone;
use PHPUnit\Framework\TestCase;

class PageTest extends TestCase
{
    /**
     * @covers \eZ\Publish\Core\FieldType\Page\Parts\Page::__construct
     * @covers \eZ\Publish\Core\FieldType\Page\Parts\Base::__construct
     * @covers \eZ\Publish\Core\FieldType\Page\Parts\Base::getState
     */
    public function testGetState()
    {
        $zone1 = new Zone(['id' => 'foo']);
        $zone2 = new Zone(['id' => 'bar']);
        $zone3 = new Zone(['id' => 'baz']);
        $properties = [
            'layout' => 'my_layout',
            'zones' => [$zone1, $zone2, $zone3],
        ];
        $page = new Page($properties);
        $this->assertEquals(
            $properties + [
                'zonesById' => [
                    'foo' => $zone1,
                    'bar' => $zone2,
                    'baz' => $zone3,
                ],
                'attributes' => [],
            ],
            $page->getState()
        );
    }
}
