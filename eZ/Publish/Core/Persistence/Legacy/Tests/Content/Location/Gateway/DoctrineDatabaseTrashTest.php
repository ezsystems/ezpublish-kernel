<?php

/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\Location\Gateway\DoctrineDatabaseTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\Location\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Tests\Content\LanguageAwareTestCase;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;
use eZ\Publish\API\Repository\Values\Content\Query;

/**
 * Test case for eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase.
 */
class DoctrineDatabaseTrashTest extends LanguageAwareTestCase
{
    protected function getLocationGateway()
    {
        $dbHandler = $this->getDatabaseHandler();

        return new DoctrineDatabase(
            $dbHandler,
            $this->getLanguageMaskGenerator()
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase::trashLocation
     *
     * @todo test updated content status
     */
    public function testTrashLocation()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();
        $handler->trashLocation(71);

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            [
                [1, 0],
                [2, 0],
                [69, 0],
                [70, 0],
            ],
            $query
                ->select('node_id', 'priority')
                ->from('ezcontentobject_tree')
                ->where($query->expr->in('node_id', [1, 2, 69, 70, 71]))
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase::trashLocation
     */
    public function testTrashLocationUpdateTrashTable()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();
        $handler->trashLocation(71);

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            [
                [71, '/1/2/69/70/71/'],
            ],
            $query
                ->select('node_id', 'path_string')
                ->from('ezcontentobject_trash')
        );
    }

    public static function getUntrashedLocationValues()
    {
        return [
            ['contentobject_is_published', 1],
            ['contentobject_version', 1],
            ['depth', 4],
            ['is_hidden', 0],
            ['is_invisible', 0],
            ['main_node_id', 228],
            ['node_id', 228],
            ['parent_node_id', 70],
            ['path_identification_string', ''],
            ['path_string', '/1/2/69/70/228/'],
            ['priority', 0],
            ['remote_id', '087adb763245e0cdcac593fb4a5996cf'],
            ['sort_field', 1],
            ['sort_order', 1],
        ];
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase::untrashLocation
     * @dataProvider getUntrashedLocationValues
     */
    public function testUntrashLocationDefault($property, $value)
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();
        $handler->trashLocation(71);

        $handler->untrashLocation(71);

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            [[$value]],
            $query
                ->select($property)
                ->from('ezcontentobject_tree')
                ->where($query->expr->in('contentobject_id', [69]))
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase::untrashLocation
     */
    public function testUntrashLocationNewParent()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();
        $handler->trashLocation(71);

        $handler->untrashLocation(71, 1);

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            [['228', '1', '/1/228/']],
            $query
                ->select('node_id', 'parent_node_id', 'path_string')
                ->from('ezcontentobject_tree')
                ->where($query->expr->in('contentobject_id', [69]))
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase::untrashLocation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testUntrashInvalidLocation()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();

        $handler->untrashLocation(23);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase::untrashLocation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testUntrashLocationInvalidParent()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();
        $handler->trashLocation(71);

        $handler->untrashLocation(71, 1337);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase::untrashLocation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testUntrashLocationInvalidOldParent()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();
        $handler->trashLocation(71);
        $handler->trashLocation(70);

        $handler->untrashLocation(70);
        $handler->untrashLocation(71);
    }

    public static function getLoadTrashValues()
    {
        return [
            ['node_id', 71],
            ['priority', 0],
            ['is_hidden', 0],
            ['is_invisible', 0],
            ['remote_id', '087adb763245e0cdcac593fb4a5996cf'],
            ['contentobject_id', 69],
            ['parent_node_id', 70],
            ['path_identification_string', 'products/software/os_type_i'],
            ['path_string', '/1/2/69/70/71/'],
            ['modified_subnode', 1311065013],
            ['main_node_id', 71],
            ['depth', 4],
            ['sort_field', 1],
            ['sort_order', 1],
        ];
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase::loadTrashByLocation
     * @dataProvider getLoadTrashValues
     */
    public function testLoadTrashByLocationId($field, $value)
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();
        $handler->trashLocation(71);

        $data = $handler->loadTrashByLocation(71);

        $this->assertEquals(
            $value,
            $data[$field],
            "Value in property $field not as expected."
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase::listTrashed
     */
    public function testListEmptyTrash()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();

        $this->assertEquals(
            [],
            $handler->listTrashed(0, null, [])
        );
    }

    protected function trashSubtree()
    {
        $handler = $this->getLocationGateway();
        $handler->trashLocation(69);
        $handler->trashLocation(70);
        $handler->trashLocation(71);
        $handler->trashLocation(72);
        $handler->trashLocation(73);
        $handler->trashLocation(74);
        $handler->trashLocation(75);
        $handler->trashLocation(76);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase::listTrashed
     */
    public function testListFullTrash()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();
        $this->trashSubtree();

        $this->assertEquals(
            8,
            count($handler->listTrashed(0, null, []))
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase::listTrashed
     */
    public function testListTrashLimited()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();
        $this->trashSubtree();

        $this->assertEquals(
            5,
            count($handler->listTrashed(0, 5, []))
        );
    }

    public static function getTrashValues()
    {
        return [
            ['contentobject_id', 67],
            ['contentobject_version', 1],
            ['depth', 2],
            ['is_hidden', 0],
            ['is_invisible', 0],
            ['main_node_id', 69],
            ['modified_subnode', 1311065014],
            ['node_id', 69],
            ['parent_node_id', 2],
            ['path_identification_string', 'products'],
            ['path_string', '/1/2/69/'],
            ['priority', 0],
            ['remote_id', '9cec85d730eec7578190ee95ce5a36f5'],
            ['sort_field', 2],
            ['sort_order', 1],
        ];
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase::listTrashed
     * @dataProvider getTrashValues
     */
    public function testListTrashItem($key, $value)
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();
        $this->trashSubtree();

        $trashList = $handler->listTrashed(0, 1, []);
        $this->assertEquals($value, $trashList[0][$key]);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase::listTrashed
     */
    public function testListTrashSortedPathStringDesc()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();
        $this->trashSubtree();

        $this->assertEquals(
            [
                '/1/2/69/76/',
                '/1/2/69/72/75/',
                '/1/2/69/72/74/',
                '/1/2/69/72/73/',
                '/1/2/69/72/',
                '/1/2/69/70/71/',
                '/1/2/69/70/',
                '/1/2/69/',
            ],
            array_map(
                function ($trashItem) {
                    return $trashItem['path_string'];
                },
                $trashList = $handler->listTrashed(
                    0,
                    null,
                    [
                        new SortClause\Location\Path(Query::SORT_DESC),
                    ]
                )
            )
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase::listTrashed
     */
    public function testListTrashSortedDepth()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();
        $this->trashSubtree();

        $this->assertEquals(
            [
                '/1/2/69/',
                '/1/2/69/76/',
                '/1/2/69/72/',
                '/1/2/69/70/',
                '/1/2/69/72/75/',
                '/1/2/69/72/74/',
                '/1/2/69/72/73/',
                '/1/2/69/70/71/',
            ],
            array_map(
                function ($trashItem) {
                    return $trashItem['path_string'];
                },
                $trashList = $handler->listTrashed(
                    0,
                    null,
                    [
                        new SortClause\Location\Depth(),
                        new SortClause\Location\Path(Query::SORT_DESC),
                    ]
                )
            )
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase::cleanupTrash
     */
    public function testCleanupTrash()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();
        $this->trashSubtree();
        $handler->cleanupTrash();

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            [],
            $query
                ->select('*')
                ->from('ezcontentobject_trash')
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase::removeElementFromTrash
     */
    public function testRemoveElementFromTrash()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();
        $this->trashSubtree();
        $handler->removeElementFromTrash(71);

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            [],
            $query
                ->select('*')
                ->from('ezcontentobject_trash')
                ->where($query->expr->eq('node_id', 71))
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase::countLocationsByContentId
     */
    public function testCountLocationsByContentId()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();

        self::assertSame(0, $handler->countLocationsByContentId(123456789));
        self::assertSame(1, $handler->countLocationsByContentId(67));

        // Insert a new node and count again
        $query = $this->handler->createInsertQuery();
        $query
            ->insertInto('ezcontentobject_tree')
            ->set('contentobject_id', $query->bindValue(67, null, \PDO::PARAM_INT))
            ->set('contentobject_version', $query->bindValue(1, null, \PDO::PARAM_INT))
            ->set('path_string', $query->bindValue('/1/2/96'))
            ->set('parent_node_id', $query->bindValue(96, null, \PDO::PARAM_INT))
            ->set('remote_id', $query->bindValue('some_remote_id'));
        $query->prepare()->execute();
        self::assertSame(2, $handler->countLocationsByContentId(67));
    }
}
