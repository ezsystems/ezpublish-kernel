<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\eZ\Bundle\PlatformInstallerBundle\Tests\DatabasePlatform;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Schema;
use EzSystems\PlatformInstallerBundle\Database\Platform\MySQLPlatform;
use PHPUnit\Framework\TestCase;

/**
 * Test of custom MySQL database platform implementation.
 */
class MySQLPlatformTest extends TestCase
{
    public function setUp()
    {
        $this->platform = new MySQLPlatform();
    }

    /**
     * @return array
     */
    public function providerForTestColumnWithLengthIndex()
    {
        return [
            [
                ['length' => ['value' => 50]],
                'UNIQUE INDEX my_table_value (value(50))',
            ],
            // test pure column based index still works
            [
                [],
                'UNIQUE INDEX my_table_value (value)',
            ],
        ];
    }

    /**
     * Test support of indexes with columns constrained by lengths.
     *
     * @dataProvider providerForTestColumnWithLengthIndex
     *
     * @param array $options
     * @param $expectedSQL
     */
    public function testColumnWithLengthIndex(array $options, $expectedSQL)
    {
        $schema = new Schema();
        $table = $schema->createTable('my_table');
        $table->addColumn('value', 'string', ['length' => 150]);
        $table->addUniqueIndex(['value'], 'my_table_value', $options);
        $createFlags = AbstractPlatform::CREATE_INDEXES;
        $sql = $this->platform->getCreateTableSQL($table, $createFlags);
        self::assertCount(1, $sql);
        self::assertContains(
            $expectedSQL,
            $sql[0]
        );
    }

    /**
     * @var \Doctrine\DBAL\Platforms\AbstractPlatform
     */
    private $platform;
}
