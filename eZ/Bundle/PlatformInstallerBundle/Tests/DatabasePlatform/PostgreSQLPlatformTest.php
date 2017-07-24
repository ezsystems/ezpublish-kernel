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
use Doctrine\DBAL\Schema\Sequence;
use EzSystems\PlatformInstallerBundle\Database\Platform\PostgreSQLPlatform;
use PHPUnit\Framework\TestCase;

/**
 * Test of custom PostgreSQL database platform implementation.
 */
class PostgreSQLPlatformTest extends TestCase
{
    public function setUp()
    {
        $this->platform = new PostgreSQLPlatform();
    }

    /**
     * @return array
     */
    public function providerForTestFunctionBasedIndex()
    {
        return [
            [
                ['wrap_in' => ['value' => 'func']],
                'CREATE UNIQUE INDEX my_table_value ON my_table (func(value))',
            ],
            // test column based index still works
            [
                [],
                'CREATE UNIQUE INDEX my_table_value ON my_table (value)',
            ],
        ];
    }

    /**
     * Test support of function (expression) based index.
     *
     * @dataProvider providerForTestFunctionBasedIndex
     *
     * @param array $options
     * @param $expectedSQL
     */
    public function testFunctionBasedIndex(array $options, $expectedSQL)
    {
        $schema = new Schema();
        $table = $schema->createTable('my_table');
        $table->addColumn('value', 'string', ['length' => 150]);
        $table->addUniqueIndex(['value'], 'my_table_value', $options);
        $createFlags = AbstractPlatform::CREATE_INDEXES;
        self::assertContains(
            $expectedSQL,
            $this->platform->getCreateTableSQL($table, $createFlags)
        );
    }

    /**
     * Test support of restarting sequence with choosen value.
     */
    public function testAlterSequence()
    {
        $sequence = new Sequence('my_table_id_seq', 1, 2, 3);

        self::assertEquals(
            'ALTER SEQUENCE my_table_id_seq INCREMENT BY 1 CACHE 3 RESTART WITH 2',
            $this->platform->getAlterSequenceSQL($sequence)
        );
    }

    /**
     * @var \Doctrine\DBAL\Platforms\AbstractPlatform
     */
    private $platform;
}
