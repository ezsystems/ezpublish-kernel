<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Legacy\Tests\UserPreference;

use eZ\Publish\Core\Persistence\Legacy\UserPreference\Mapper;
use eZ\Publish\SPI\Persistence\UserPreference\UserPreference;
use PHPUnit\Framework\TestCase;

class MapperTest extends TestCase
{
    /** @var \eZ\Publish\Core\Persistence\Legacy\UserPreference\Mapper */
    private $mapper;

    protected function setUp()
    {
        $this->mapper = new Mapper();
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\UserPreference\Mapper::extractUserPreferencesFromRows
     */
    public function testExtractUserPreferencesFromRows()
    {
        $rows = [
            [
                'id' => 1,
                'user_id' => 5,
                'name' => 'setting_1',
                'value' => 'value_1',
            ],
            [
                'id' => 1,
                'user_id' => 5,
                'name' => 'setting_2',
                'value' => 'value_2',
            ],
        ];

        $objects = [
            new UserPreference([
                'id' => 1,
                'userId' => 5,
                'name' => 'setting_1',
                'value' => 'value_1',
            ]),
            new UserPreference([
                'id' => 1,
                'userId' => 5,
                'name' => 'setting_2',
                'value' => 'value_2',
            ]),
        ];

        $this->assertEquals($objects, $this->mapper->extractUserPreferencesFromRows($rows));
    }
}
