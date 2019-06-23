<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\Tests\SignalDispatcher;

use eZ\Publish\Core\SignalSlot;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use PHPUnit\Framework\TestCase;

/**
 * @group signalSlot
 * @covers \eZ\Publish\Core\SignalSlot\SlotFactory\GeneralSlotFactory
 */
class GeneralSlotFactoryTest extends TestCase
{
    public function providerForFactoryTests()
    {
        return [
            [['slot1' => true, 'slot2' => true]],
            [
                [
                    'slot1' => $this->createMock(SignalSlot\Slot::class),
                    'slot2' => $this->createMock(SignalSlot\Slot::class),
                ],
            ],
        ];
    }

    /**
     * @dataProvider providerForFactoryTests
     */
    public function testValidSlot($slots)
    {
        $factory = $this->setUpFactory($slots);
        foreach ($slots as $slotIdentifier => $slotValue) {
            $this->assertEquals($slotValue, $factory->getSlot($slotIdentifier));
        }
    }

    /**
     * @dataProvider providerForFactoryTests
     */
    public function testInValidSlot($slots)
    {
        $factory = $this->setUpFactory($slots);
        foreach (array_keys($slots) as $slotIdentifier) {
            try {
                $factory->getSlot($slotIdentifier . '42');
                $this->fail('expected NotFoundException ');
            } catch (NotFoundException $e) {
            }
        }
    }

    private function setUpFactory($slots)
    {
        return new SignalSlot\SlotFactory\GeneralSlotFactory($slots);
    }
}
