<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Notification;

use eZ\Publish\Core\Persistence\Legacy\Notification\Mapper;
use eZ\Publish\SPI\Persistence\Notification\Notification;
use eZ\Publish\SPI\Persistence\Notification\UpdateStruct;
use PHPUnit\Framework\TestCase;

class MapperTest extends TestCase
{
    /** @var \eZ\Publish\Core\Persistence\Legacy\Notification\Mapper */
    private $mapper;

    protected function setUp()
    {
        $this->mapper = new Mapper();
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Notification\Mapper::extractNotificationsFromRows
     */
    public function testExtractNotificationsFromRows()
    {
        $rows = [
            [
                'id' => 1,
                'owner_id' => 5,
                'type' => 'FOO',
                'created' => 1529913161,
                'is_pending' => 0,
                'data' => null,
            ],
            [
                'id' => 1,
                'owner_id' => 5,
                'type' => 'BAR',
                'created' => 1529910161,
                'is_pending' => 1,
                'data' => json_encode([
                    'foo' => 'Foo',
                    'bar' => 'Bar',
                    'baz' => ['B', 'A', 'Z'],
                ]),
            ],
        ];

        $objects = [
            new Notification([
                'id' => 1,
                'ownerId' => 5,
                'type' => 'FOO',
                'created' => 1529913161,
                'isPending' => false,
                'data' => [],
            ]),
            new Notification([
                'id' => 1,
                'ownerId' => 5,
                'type' => 'BAR',
                'created' => 1529910161,
                'isPending' => true,
                'data' => [
                    'foo' => 'Foo',
                    'bar' => 'Bar',
                    'baz' => ['B', 'A', 'Z'],
                ],
            ]),
        ];

        $this->assertEquals($objects, $this->mapper->extractNotificationsFromRows($rows));
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Notification\Mapper::extractNotificationsFromRows
     * @expectedException \RuntimeException
     */
    public function testExtractNotificationsFromRowsThrowsRuntimeException()
    {
        $rows = [
            [
                'id' => 1,
                'owner_id' => 5,
                'type' => 'FOO',
                'created' => 1529913161,
                'is_pending' => false,
                'data' => '{ InvalidJSON }',
            ],
        ];

        $this->mapper->extractNotificationsFromRows($rows);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Notification\Mapper::createNotificationFromUpdateStruct
     */
    public function testCreateNotificationFromUpdateStruct()
    {
        $updateStruct = new UpdateStruct([
            'isPending' => false,
        ]);

        $this->assertEquals(new Notification([
            'isPending' => false,
        ]), $this->mapper->createNotificationFromUpdateStruct($updateStruct));
    }
}
