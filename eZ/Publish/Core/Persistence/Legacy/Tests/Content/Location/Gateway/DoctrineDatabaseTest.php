<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\Location\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Tests\Content\LanguageAwareTestCase;
use eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase as ContentDoctrineDatabase;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Persistence\Content\Location\CreateStruct;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct as SPIMetadataUpdateStruct;

/**
 * Test case for eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase.
 */
class DoctrineDatabaseTest extends LanguageAwareTestCase
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
     * Returns a ready to test DoctrineDatabase gateway.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase
     */
    protected function getContentGateway()
    {
        $databaseGateway = new \eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase(
            ($dbHandler = $this->getDatabaseHandler()),
            $this->getDatabaseConnection(),
            new ContentDoctrineDatabase\QueryBuilder($dbHandler),
            $this->getLanguageHandler(),
            $this->getLanguageMaskGenerator()
        );

        return $databaseGateway;
    }

    private static function getLoadLocationValues(): array
    {
        return [
            'node_id' => 77,
            'priority' => 0,
            'is_hidden' => 0,
            'is_invisible' => 0,
            'remote_id' => 'dbc2f3c8716c12f32c379dbf0b1cb133',
            'contentobject_id' => 75,
            'parent_node_id' => 2,
            'path_identification_string' => 'solutions',
            'path_string' => '/1/2/77/',
            'modified_subnode' => 1311065017,
            'main_node_id' => 77,
            'depth' => 2,
            'sort_field' => 2,
            'sort_order' => 1,
        ];
    }

    private function assertLoadLocationProperties(array $locationData): void
    {
        foreach (self::getLoadLocationValues() as $field => $expectedValue) {
            self::assertEquals(
                $expectedValue,
                $locationData[$field],
                "Value in property $field not as expected."
            );
        }
    }

    public function testLoadLocationByRemoteId()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();
        $data = $handler->getBasicNodeDataByRemoteId('dbc2f3c8716c12f32c379dbf0b1cb133');

        self::assertLoadLocationProperties($data);
    }

    public function testLoadLocation()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();
        $data = $handler->getBasicNodeData(77);

        self::assertLoadLocationProperties($data);
    }

    public function testLoadLocationList()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();
        $locationsData = $handler->getNodeDataList([77]);

        self::assertCount(1, $locationsData);

        $locationRow = reset($locationsData);

        self::assertLoadLocationProperties($locationRow);
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadInvalidLocation()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();
        $handler->getBasicNodeData(1337);
    }

    public function testLoadLocationDataByContent()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');

        $gateway = $this->getLocationGateway();

        $locationsData = $gateway->loadLocationDataByContent(75);

        self::assertCount(1, $locationsData);

        $locationRow = reset($locationsData);

        self::assertLoadLocationProperties($locationRow);
    }

    public function testLoadParentLocationDataForDraftContentAll()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');

        $gateway = $this->getLocationGateway();

        $locationsData = $gateway->loadParentLocationsDataForDraftContent(226);

        $this->assertCount(1, $locationsData);

        $locationRow = reset($locationsData);

        self::assertLoadLocationProperties($locationRow);
    }

    public function testLoadLocationDataByContentLimitSubtree()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');

        $gateway = $this->getLocationGateway();

        $locationsData = $gateway->loadLocationDataByContent(75, 3);

        $this->assertCount(0, $locationsData);
    }

    public function testMoveSubtreePathUpdate()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();
        $handler->moveSubtreeNodes(
            [
                'path_string' => '/1/2/69/',
                'contentobject_id' => 67,
                'path_identification_string' => 'products',
                'is_hidden' => 0,
                'is_invisible' => 0,
            ],
            [
                'path_string' => '/1/2/77/',
                'path_identification_string' => 'solutions',
                'is_hidden' => 0,
                'is_invisible' => 0,
            ]
        );

        /** @var $query \eZ\Publish\Core\Persistence\Database\SelectQuery */
        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            [
                [65, '/1/2/', '', 1, 1, 0, 0],
                [67, '/1/2/77/69/', 'solutions/products', 77, 3, 0, 0],
                [69, '/1/2/77/69/70/71/', 'solutions/products/software/os_type_i', 70, 5, 0, 0],
                [73, '/1/2/77/69/72/75/', 'solutions/products/boxes/cd_dvd_box_iii', 72, 5, 0, 0],
                [75, '/1/2/77/', 'solutions', 2, 2, 0, 0],
            ],
            $query
                ->select('contentobject_id', 'path_string', 'path_identification_string', 'parent_node_id', 'depth', 'is_hidden', 'is_invisible')
                ->from('ezcontentobject_tree')
                ->where($query->expr->in('node_id', [69, 71, 75, 77, 2]))
                ->orderBy('contentobject_id')
        );
    }

    public function testMoveHiddenDestinationUpdate()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();
        $handler->hideSubtree('/1/2/77/');
        $handler->moveSubtreeNodes(
            [
                'path_string' => '/1/2/69/',
                'contentobject_id' => 67,
                'path_identification_string' => 'products',
                'is_hidden' => 0,
                'is_invisible' => 0,
            ],
            [
                'path_string' => '/1/2/77/',
                'path_identification_string' => 'solutions',
                'is_hidden' => 1,
                'is_invisible' => 1,
            ]
        );

        /** @var $query \eZ\Publish\Core\Persistence\Database\SelectQuery */
        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            [
                [65, '/1/2/', '', 1, 1, 0, 0],
                [67, '/1/2/77/69/', 'solutions/products', 77, 3, 0, 1],
                [69, '/1/2/77/69/70/71/', 'solutions/products/software/os_type_i', 70, 5, 0, 1],
                [73, '/1/2/77/69/72/75/', 'solutions/products/boxes/cd_dvd_box_iii', 72, 5, 0, 1],
                [75, '/1/2/77/', 'solutions', 2, 2, 1, 1],
            ],
            $query
                ->select('contentobject_id', 'path_string', 'path_identification_string', 'parent_node_id', 'depth', 'is_hidden', 'is_invisible')
                ->from('ezcontentobject_tree')
                ->where($query->expr->in('node_id', [69, 71, 75, 77, 2]))
                ->orderBy('contentobject_id')
        );
    }

    public function testMoveHiddenSourceUpdate()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();
        $handler->hideSubtree('/1/2/69/');
        $handler->moveSubtreeNodes(
            [
                'path_string' => '/1/2/69/',
                'contentobject_id' => 67,
                'path_identification_string' => 'products',
                'is_hidden' => 1,
                'is_invisible' => 1,
            ],
            [
                'path_string' => '/1/2/77/',
                'path_identification_string' => 'solutions',
                'is_hidden' => 0,
                'is_invisible' => 0,
            ]
        );

        /** @var $query \eZ\Publish\Core\Persistence\Database\SelectQuery */
        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            [
                [65, '/1/2/', '', 1, 1, 0, 0],
                [67, '/1/2/77/69/', 'solutions/products', 77, 3, 1, 1],
                [69, '/1/2/77/69/70/71/', 'solutions/products/software/os_type_i', 70, 5, 0, 1],
                [73, '/1/2/77/69/72/75/', 'solutions/products/boxes/cd_dvd_box_iii', 72, 5, 0, 1],
                [75, '/1/2/77/', 'solutions', 2, 2, 0, 0],
            ],
            $query
                ->select('contentobject_id', 'path_string', 'path_identification_string', 'parent_node_id', 'depth', 'is_hidden', 'is_invisible')
                ->from('ezcontentobject_tree')
                ->where($query->expr->in('node_id', [69, 71, 75, 77, 2]))
                ->orderBy('contentobject_id')
        );
    }

    private function hideContent(int $contentId)
    {
        $contentHandler = $this->getContentGateway();

        $contentHandler->updateContent(
            $contentId,
            new SPIMetadataUpdateStruct([
                'isHidden' => true,
            ])
        );
        $locationHandler = $this->getLocationGateway();
        $childLocations = $locationHandler->loadLocationDataByContent($contentId);
        foreach ($childLocations as $childLocation) {
            $locationHandler->setNodeWithChildrenInvisible($childLocation['path_string']);
        }
    }

    public function testMoveContentHiddenSourceUpdate()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $this->hideContent(67);

        $handler = $this->getLocationGateway();

        $handler->moveSubtreeNodes(
            [
                'path_string' => '/1/2/69/',
                'contentobject_id' => 67,
                'path_identification_string' => 'products',
                'is_hidden' => 0,
                'is_invisible' => 1,
            ],
            [
                'path_string' => '/1/2/77/',
                'path_identification_string' => 'solutions',
                'is_hidden' => 0,
                'is_invisible' => 0,
            ]
        );

        /** @var $query \eZ\Publish\Core\Persistence\Database\SelectQuery */
        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            [
                [65, '/1/2/', '', 1, 1, 0, 0],
                [67, '/1/2/77/69/', 'solutions/products', 77, 3, 0, 1],
                [69, '/1/2/77/69/70/71/', 'solutions/products/software/os_type_i', 70, 5, 0, 1],
                [73, '/1/2/77/69/72/75/', 'solutions/products/boxes/cd_dvd_box_iii', 72, 5, 0, 1],
                [75, '/1/2/77/', 'solutions', 2, 2, 0, 0],
            ],
            $query
                ->select('contentobject_id', 'path_string', 'path_identification_string', 'parent_node_id', 'depth', 'is_hidden', 'is_invisible')
                ->from('ezcontentobject_tree')
                ->where($query->expr->in('node_id', [69, 71, 75, 77, 2]))
                ->orderBy('contentobject_id')
        );

        /** @var $query \eZ\Publish\Core\Persistence\Database\SelectQuery */
        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            [
                [67, 1],
                [68, 0],
                [69, 0],
                [70, 0],
                [75, 0],
                [76, 0],
                [77, 0],
                [78, 0],
                [79, 0],
                [80, 0],
                [81, 0],
            ],
            $query
                ->select('id', 'is_hidden')
                ->from('ezcontentobject')
                ->where($query->expr->in('id', [67, 68, 69, 70, 75, 76, 77, 78, 79, 80, 81])) //,     69, 71, 75, 77, 2]))
                ->orderBy('id')
        );
    }

    public function testMoveHiddenSourceChildUpdate()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();
        $handler->hideSubtree('/1/2/69/70/');

        $handler->moveSubtreeNodes(
            [
                'path_string' => '/1/2/69/',
                'contentobject_id' => 67,
                'path_identification_string' => 'products',
                'is_hidden' => 0,
                'is_invisible' => 0,
            ],
            [
                'path_string' => '/1/2/77/',
                'path_identification_string' => 'solutions',
                'is_hidden' => 0,
                'is_invisible' => 0,
            ]
        );

        /** @var $query \eZ\Publish\Core\Persistence\Database\SelectQuery */
        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            [
                [65, '/1/2/', '', 1, 1, 0, 0],
                [67, '/1/2/77/69/', 'solutions/products', 77, 3, 0, 0],
                [68, '/1/2/77/69/70/', 'solutions/products/software', 69, 4, 1, 1],
                [69, '/1/2/77/69/70/71/', 'solutions/products/software/os_type_i', 70, 5, 0, 1],
                [73, '/1/2/77/69/72/75/', 'solutions/products/boxes/cd_dvd_box_iii', 72, 5, 0, 0],
                [75, '/1/2/77/', 'solutions', 2, 2, 0, 0],
            ],
            $query
                ->select('contentobject_id', 'path_string', 'path_identification_string', 'parent_node_id', 'depth', 'is_hidden', 'is_invisible')
                ->from('ezcontentobject_tree')
                ->where($query->expr->in('node_id', [69, 70, 71, 75, 77, 2]))
                ->orderBy('contentobject_id')
        );
    }

    public function testMoveSubtreeAssignmentUpdate()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();
        $handler->updateNodeAssignment(67, 2, 77, 5);

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            [
                [67, 1, 0, 53, 1, 5, 77, '9cec85d730eec7578190ee95ce5a36f5', 0, 2, 1, 0, 0],
            ],
            $query
                ->select('*')
                ->from('eznode_assignment')
                ->where($query->expr->eq('contentobject_id', 67))
        );
    }

    public function testUpdateSubtreeModificationTime()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();
        $time = time();
        $handler->updateSubtreeModificationTime('/1/2/69/');

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            [
                ['/1/'],
                ['/1/2/'],
                ['/1/2/69/'],
            ],
            $query
                ->select('path_string')
                ->from('ezcontentobject_tree')
                ->where($query->expr->gte('modified_subnode', $time))
                ->orderBy('path_string')
        );
    }

    public function testHideUpdateHidden()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();
        $handler->hideSubtree('/1/2/69/');

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            [
                [1, 0, 0],
                [2, 0, 0],
                [69, 1, 1],
                [75, 0, 1],
            ],
            $query
                ->select('node_id', 'is_hidden', 'is_invisible')
                ->from('ezcontentobject_tree')
                ->where($query->expr->in('node_id', [1, 2, 69, 75]))
                ->orderBy('node_id')
        );
    }

    /**
     * @depends testHideUpdateHidden
     */
    public function testHideUnhideUpdateHidden()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();
        $handler->hideSubtree('/1/2/69/');
        $handler->unhideSubtree('/1/2/69/');

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            [
                [1, 0, 0],
                [2, 0, 0],
                [69, 0, 0],
                [75, 0, 0],
            ],
            $query
                ->select('node_id', 'is_hidden', 'is_invisible')
                ->from('ezcontentobject_tree')
                ->where($query->expr->in('node_id', [1, 2, 69, 75]))
                ->orderBy('node_id')
        );
    }

    /**
     * @depends testHideUpdateHidden
     */
    public function testHideUnhideParentTree()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();
        $handler->hideSubtree('/1/2/69/');
        $handler->hideSubtree('/1/2/69/70/');
        $handler->unhideSubtree('/1/2/69/');

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            [
                [1, 0, 0],
                [2, 0, 0],
                [69, 0, 0],
                [70, 1, 1],
                [71, 0, 1],
                [75, 0, 0],
            ],
            $query
                ->select('node_id', 'is_hidden', 'is_invisible')
                ->from('ezcontentobject_tree')
                ->where($query->expr->in('node_id', [1, 2, 69, 70, 71, 75]))
                ->orderBy('node_id')
        );
    }

    /**
     * @depends testHideUpdateHidden
     */
    public function testHideUnhidePartialSubtree()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();
        $handler->hideSubtree('/1/2/69/');
        $handler->hideSubtree('/1/2/69/70/');
        $handler->unhideSubtree('/1/2/69/70/');

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            [
                [1, 0, 0],
                [2, 0, 0],
                [69, 1, 1],
                [70, 0, 1],
                [71, 0, 1],
                [75, 0, 1],
            ],
            $query
                ->select('node_id', 'is_hidden', 'is_invisible')
                ->from('ezcontentobject_tree')
                ->where($query->expr->in('node_id', [1, 2, 69, 70, 71, 75]))
                ->orderBy('node_id')
        );
    }

    public function testSwapLocations()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();
        $handler->swap(70, 78);

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            [
                [70, 76],
                [78, 68],
            ],
            $query
                ->select('node_id', 'contentobject_id')
                ->from('ezcontentobject_tree')
                ->where($query->expr->in('node_id', [70, 78]))
                ->orderBy('node_id')
        );
    }

    public function testCreateLocation()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();
        $handler->create(
            new CreateStruct(
                [
                    'contentId' => 68,
                    'remoteId' => 'some_id',
                ]
            ),
            [
                'node_id' => '77',
                'depth' => '2',
                'path_string' => '/1/2/77/',
            ]
        );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            [
                [70, '/1/2/69/70/'],
                [77, '/1/2/77/'],
                [228, '/1/2/77/228/'],
            ],
            $query
                ->select('node_id', 'path_string')
                ->from('ezcontentobject_tree')
                ->where($query->expr->in('contentobject_id', [68, 75]))
                ->orderBy('node_id')
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase::getMainNodeId
     * @depends testCreateLocation
     */
    public function testGetMainNodeId()
    {
        // $this->insertDatabaseFixture( __DIR__ . '/_fixtures/full_example_tree.php' );
        $handler = $this->getLocationGateway();

        $parentLocationData = [
            'node_id' => '77',
            'depth' => '2',
            'path_string' => '/1/2/77/',
        ];

        // main location
        $mainLocation = $handler->create(
            new CreateStruct(
                [
                    'contentId' => 68,
                    'contentVersion' => 1,
                    'remoteId' => 'some_id',
                    'mainLocationId' => true,
                ]
            ),
            $parentLocationData
        );

        // secondary location
        $handler->create(
            new CreateStruct(
                [
                    'contentId' => 68,
                    'contentVersion' => 1,
                    'remoteId' => 'some_id',
                    'mainLocationId' => $mainLocation->id,
                ]
            ),
            $parentLocationData
        );

        $handlerReflection = new \ReflectionObject($handler);
        $methodReflection = $handlerReflection->getMethod('getMainNodeId');
        $methodReflection->setAccessible(true);
        self::assertEquals($mainLocation->id, $res = $methodReflection->invoke($handler, 68));
    }

    public static function getCreateLocationValues()
    {
        return [
            ['contentobject_id', 68],
            ['contentobject_is_published', 1],
            ['contentobject_version', 1],
            ['depth', 3],
            ['is_hidden', 0],
            ['is_invisible', 0],
            ['main_node_id', 42],
            ['parent_node_id', 77],
            ['path_identification_string', ''],
            ['priority', 1],
            ['remote_id', 'some_id'],
            ['sort_field', 1],
            ['sort_order', 1],
        ];
    }

    /**
     * @depends testCreateLocation
     * @dataProvider getCreateLocationValues
     */
    public function testCreateLocationValues($field, $value)
    {
        if ($value === null) {
            $this->markTestIncomplete('Proper value setting yet unknown.');
        }

        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();
        $handler->create(
            new CreateStruct(
                [
                    'contentId' => 68,
                    'contentVersion' => 1,
                    'mainLocationId' => 42,
                    'priority' => 1,
                    'remoteId' => 'some_id',
                    'sortField' => 1,
                    'sortOrder' => 1,
                ]
            ),
            [
                'node_id' => '77',
                'depth' => '2',
                'path_string' => '/1/2/77/',
            ]
        );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            [[$value]],
            $query
                ->select($field)
                ->from('ezcontentobject_tree')
                ->where($query->expr->eq('node_id', 228))
        );
    }

    public static function getCreateLocationReturnValues()
    {
        return [
            ['id', 228],
            ['priority', 1],
            ['hidden', false],
            ['invisible', false],
            ['remoteId', 'some_id'],
            ['contentId', '68'],
            ['parentId', '77'],
            ['pathIdentificationString', ''],
            ['pathString', '/1/2/77/228/'],
            ['depth', 3],
            ['sortField', 1],
            ['sortOrder', 1],
        ];
    }

    /**
     * @depends testCreateLocation
     * @dataProvider getCreateLocationReturnValues
     */
    public function testCreateLocationReturnValues($field, $value)
    {
        if ($value === null) {
            $this->markTestIncomplete('Proper value setting yet unknown.');
        }

        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();
        $location = $handler->create(
            new CreateStruct(
                [
                    'contentId' => 68,
                    'contentVersion' => 1,
                    'mainLocationId' => true,
                    'priority' => 1,
                    'remoteId' => 'some_id',
                    'sortField' => 1,
                    'sortOrder' => 1,
                ]
            ),
            [
                'node_id' => '77',
                'depth' => '2',
                'path_string' => '/1/2/77/',
            ]
        );

        $this->assertTrue($location instanceof Location);
        $this->assertEquals($value, $location->$field);
    }

    public static function getUpdateLocationData()
    {
        return [
            ['priority', 23],
            ['remote_id', 'someNewHash'],
            ['sort_field', 4],
            ['sort_order', 4],
        ];
    }

    /**
     * @dataProvider getUpdateLocationData
     */
    public function testUpdateLocation($field, $value)
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();
        $handler->update(
            new Location\UpdateStruct(
                [
                    'priority' => 23,
                    'remoteId' => 'someNewHash',
                    'sortField' => 4,
                    'sortOrder' => 4,
                ]
            ),
            70
        );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            [[$value]],
            $query
                ->select($field)
                ->from('ezcontentobject_tree')
                ->where($query->expr->in('node_id', [70]))
        );
    }

    public static function getNodeAssignmentValues()
    {
        return [
            ['contentobject_version', 1],
            ['from_node_id', 0],
            ['id', 215],
            ['is_main', 0],
            ['op_code', 3],
            ['parent_node', 77],
            ['parent_remote_id', 'some_id'],
            ['remote_id', '0'],
            ['sort_field', 2],
            ['sort_order', 0],
            ['is_main', 0],
            ['priority', 1],
            ['is_hidden', 1],
        ];
    }

    /**
     * @depends testCreateLocation
     * @dataProvider getNodeAssignmentValues
     */
    public function testCreateLocationNodeAssignmentCreation($field, $value)
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();
        $handler->createNodeAssignment(
            new CreateStruct(
                [
                    'contentId' => 68,
                    'contentVersion' => 1,
                    'mainLocationId' => 1,
                    'priority' => 1,
                    'remoteId' => 'some_id',
                    'sortField' => 2,
                    'sortOrder' => 0,
                    'hidden' => 1,
                ]
            ),
            '77',
            DoctrineDatabase::NODE_ASSIGNMENT_OP_CODE_CREATE
        );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            [[$value]],
            $query
                ->select($field)
                ->from('eznode_assignment')
                ->where(
                    $query->expr->lAnd(
                        $query->expr->eq('contentobject_id', 68),
                        $query->expr->eq('parent_node', 77)
                    )
                )
        );
    }

    /**
     * @depends testCreateLocation
     */
    public function testCreateLocationNodeAssignmentCreationMainLocation()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();
        $handler->createNodeAssignment(
            new CreateStruct(
                [
                    'contentId' => 68,
                    'contentVersion' => 1,
                    'mainLocationId' => true,
                    'priority' => 1,
                    'remoteId' => 'some_id',
                    'sortField' => 1,
                    'sortOrder' => 1,
                ]
            ),
            '77',
            DoctrineDatabase::NODE_ASSIGNMENT_OP_CODE_CREATE
        );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            [[1]],
            $query
                ->select('is_main')
                ->from('eznode_assignment')
                ->where(
                    $query->expr->lAnd(
                        $query->expr->eq('contentobject_id', 68),
                        $query->expr->eq('parent_node', 77)
                    )
                )
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase::updateLocationsContentVersionNo
     */
    public function testUpdateLocationsContentVersionNo()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $gateway = $this->getLocationGateway();

        $gateway->create(
            new CreateStruct(
                [
                    'contentId' => 4096,
                    'remoteId' => 'some_id',
                    'contentVersion' => 1,
                ]
            ),
            [
                'node_id' => '77',
                'depth' => '2',
                'path_string' => '/1/2/77/',
            ]
        );

        $gateway->updateLocationsContentVersionNo(4096, 2);

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            [
                [2],
            ],
            $query->select(
                'contentobject_version'
            )->from(
                'ezcontentobject_tree'
            )->where(
                $query->expr->eq(
                    'contentobject_id',
                    4096
                )
            )
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase::deleteNodeAssignment
     */
    public function testDeleteNodeAssignment()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();

        $handler->deleteNodeAssignment(11);

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            [[0]],
            $query
                ->select('count(*)')
                ->from('eznode_assignment')
                ->where(
                    $query->expr->lAnd(
                        $query->expr->eq('contentobject_id', 11)
                    )
                )
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase::deleteNodeAssignment
     */
    public function testDeleteNodeAssignmentWithSecondArgument()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $handler = $this->getLocationGateway();

        $query = $this->handler->createSelectQuery();
        $query
            ->select('count(*)')
            ->from('eznode_assignment')
            ->where(
                $query->expr->lAnd(
                    $query->expr->eq('contentobject_id', 11)
                )
            );
        $statement = $query->prepare();
        $statement->execute();
        $nodeAssignmentsCount = (int)$statement->fetchColumn();

        $handler->deleteNodeAssignment(11, 1);

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            [[$nodeAssignmentsCount - 1]],
            $query
                ->select('count(*)')
                ->from('eznode_assignment')
                ->where(
                    $query->expr->lAnd(
                        $query->expr->eq('contentobject_id', 11)
                    )
                )
        );
    }

    public static function getConvertNodeAssignmentsLocationValues()
    {
        return [
            ['contentobject_id', '68'],
            ['contentobject_is_published', '1'],
            ['contentobject_version', '1'],
            ['depth', '3'],
            ['is_hidden', '1'],
            ['is_invisible', '1'],
            ['main_node_id', '70'],
            ['modified_subnode', time()],
            ['node_id', '228'],
            ['parent_node_id', '77'],
            ['path_identification_string', null],
            ['path_string', '/1/2/77/228/'],
            ['priority', '101'],
            ['remote_id', 'some_id'],
            ['sort_field', '1'],
            ['sort_order', '1'],
        ];
    }

    /**
     * @depends testCreateLocationNodeAssignmentCreation
     * @dataProvider getConvertNodeAssignmentsLocationValues
     */
    public function testConvertNodeAssignments($field, $value)
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');

        $handler = $this->getLocationGateway();
        $handler->createNodeAssignment(
            new CreateStruct(
                [
                    'contentId' => 68,
                    'contentVersion' => 1,
                    'mainLocationId' => false,
                    'priority' => 101,
                    'remoteId' => 'some_id',
                    'sortField' => 1,
                    'sortOrder' => 1,
                    'hidden' => true,
                    // Note: not stored in node assignment, will be calculated from parent
                    // visibility upon Location creation from node assignment
                    'invisible' => false,
                ]
            ),
            '77',
            DoctrineDatabase::NODE_ASSIGNMENT_OP_CODE_CREATE
        );

        $handler->createLocationsFromNodeAssignments(68, 1);

        $query = $this->handler->createSelectQuery();
        $query
            ->select($field)
            ->from('ezcontentobject_tree')
            ->where(
                $query->expr->lAnd(
                    $query->expr->eq('contentobject_id', 68),
                    $query->expr->eq('parent_node_id', 77)
                )
            );

        if ($field === 'modified_subnode') {
            $statement = $query->prepare();
            $statement->execute();
            $result = $statement->fetch(\PDO::FETCH_ASSOC);
            $this->assertGreaterThanOrEqual($value, $result);
        } else {
            $this->assertQueryResult(
                [[$value]],
                $query
            );
        }
    }

    /**
     * @depends testCreateLocationNodeAssignmentCreation
     */
    public function testConvertNodeAssignmentsMainLocation()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');

        $handler = $this->getLocationGateway();
        $handler->createNodeAssignment(
            new CreateStruct(
                [
                    'contentId' => 68,
                    'contentVersion' => 1,
                    'mainLocationId' => true,
                    'priority' => 101,
                    'remoteId' => 'some_id',
                    'sortField' => 1,
                    'sortOrder' => 1,
                    'hidden' => true,
                    // Note: not stored in node assignment, will be calculated from parent
                    // visibility upon Location creation from node assignment
                    'invisible' => false,
                ]
            ),
            '77',
            DoctrineDatabase::NODE_ASSIGNMENT_OP_CODE_CREATE
        );

        $handler->createLocationsFromNodeAssignments(68, 1);

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            [[228]],
            $query
                ->select('main_node_id')
                ->from('ezcontentobject_tree')
                ->where(
                    $query->expr->lAnd(
                        $query->expr->eq('contentobject_id', 68),
                        $query->expr->eq('parent_node_id', 77)
                    )
                )
        );
    }

    /**
     * @depends testCreateLocationNodeAssignmentCreation
     */
    public function testConvertNodeAssignmentsParentHidden()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');

        $handler = $this->getLocationGateway();
        $handler->createNodeAssignment(
            new CreateStruct(
                [
                    'contentId' => 68,
                    'contentVersion' => 1,
                    'mainLocationId' => true,
                    'priority' => 101,
                    'remoteId' => 'some_id',
                    'sortField' => 1,
                    'sortOrder' => 1,
                    'hidden' => false,
                    // Note: not stored in node assignment, will be calculated from parent
                    // visibility upon Location creation from node assignment
                    'invisible' => false,
                ]
            ),
            '224',
            DoctrineDatabase::NODE_ASSIGNMENT_OP_CODE_CREATE
        );

        $handler->createLocationsFromNodeAssignments(68, 1);

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            [[0, 1]],
            $query
                ->select('is_hidden, is_invisible')
                ->from('ezcontentobject_tree')
                ->where(
                    $query->expr->lAnd(
                        $query->expr->eq('contentobject_id', 68),
                        $query->expr->eq('parent_node_id', 224)
                    )
                )
        );
    }

    /**
     * @depends testCreateLocationNodeAssignmentCreation
     */
    public function testConvertNodeAssignmentsParentInvisible()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');

        $handler = $this->getLocationGateway();
        $handler->createNodeAssignment(
            new CreateStruct(
                [
                    'contentId' => 68,
                    'contentVersion' => 1,
                    'mainLocationId' => true,
                    'priority' => 101,
                    'remoteId' => 'some_id',
                    'sortField' => 1,
                    'sortOrder' => 1,
                    'hidden' => false,
                    // Note: not stored in node assignment, will be calculated from parent
                    // visibility upon Location creation from node assignment
                    'invisible' => false,
                ]
            ),
            '225',
            DoctrineDatabase::NODE_ASSIGNMENT_OP_CODE_CREATE
        );

        $handler->createLocationsFromNodeAssignments(68, 1);

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            [[0, 1]],
            $query
                ->select('is_hidden, is_invisible')
                ->from('ezcontentobject_tree')
                ->where(
                    $query->expr->lAnd(
                        $query->expr->eq('contentobject_id', 68),
                        $query->expr->eq('parent_node_id', 225)
                    )
                )
        );
    }

    /**
     * @depends testCreateLocationNodeAssignmentCreation
     */
    public function testConvertNodeAssignmentsUpdateAssignment()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');

        $handler = $this->getLocationGateway();
        $handler->createNodeAssignment(
            new CreateStruct(
                [
                    'contentId' => 68,
                    'contentVersion' => 1,
                    'mainLocationId' => 1,
                    'priority' => 1,
                    'remoteId' => 'some_id',
                    'sortField' => 1,
                    'sortOrder' => 1,
                ]
            ),
            '77',
            DoctrineDatabase::NODE_ASSIGNMENT_OP_CODE_CREATE
        );

        $handler->createLocationsFromNodeAssignments(68, 1);

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            [[DoctrineDatabase::NODE_ASSIGNMENT_OP_CODE_CREATE_NOP]],
            $query
                ->select('op_code')
                ->from('eznode_assignment')
                ->where(
                    $query->expr->lAnd(
                        $query->expr->eq('contentobject_id', 68),
                        $query->expr->eq('parent_node', 77)
                    )
                )
        );
    }

    /**
     * Test for the setSectionForSubtree() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase::setSectionForSubtree
     */
    public function testSetSectionForSubtree()
    {
        $this->insertDatabaseFixture(__DIR__ . '/../../_fixtures/contentobjects.php');
        $handler = $this->getLocationGateway();
        $handler->setSectionForSubtree('/1/2/69/70/', 23);

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            [[68], [69]],
            $query
                ->select('id')
                ->from('ezcontentobject')
                ->where($query->expr->eq('section_id', 23))
        );
    }

    /**
     * Test for the changeMainLocation() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase::changeMainLocation
     */
    public function testChangeMainLocation()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        // Create additional location and assignment for test purpose
        $query = $this->handler->createInsertQuery();
        $query->insertInto($this->handler->quoteTable('ezcontentobject_tree'))
            ->set($this->handler->quoteColumn('contentobject_id'), $query->bindValue(10, null, \PDO::PARAM_INT))
            ->set($this->handler->quoteColumn('contentobject_version'), $query->bindValue(2, null, \PDO::PARAM_INT))
            ->set($this->handler->quoteColumn('main_node_id'), $query->bindValue(15, null, \PDO::PARAM_INT))
            ->set($this->handler->quoteColumn('node_id'), $query->bindValue(228, null, \PDO::PARAM_INT))
            ->set($this->handler->quoteColumn('parent_node_id'), $query->bindValue(227, null, \PDO::PARAM_INT))
            ->set($this->handler->quoteColumn('path_string'), $query->bindValue('/1/5/13/228/', null, \PDO::PARAM_STR))
            ->set($this->handler->quoteColumn('remote_id'), $query->bindValue('asdfg123437', null, \PDO::PARAM_STR));
        $query->prepare()->execute();
        $query = $this->handler->createInsertQuery();
        $query->insertInto($this->handler->quoteTable('eznode_assignment'))
            ->set($this->handler->quoteColumn('contentobject_id'), $query->bindValue(10, null, \PDO::PARAM_INT))
            ->set($this->handler->quoteColumn('contentobject_version'), $query->bindValue(2, null, \PDO::PARAM_INT))
            ->set($this->handler->quoteColumn('id'), $query->bindValue(0, null, \PDO::PARAM_INT))
            ->set($this->handler->quoteColumn('is_main'), $query->bindValue(0, null, \PDO::PARAM_INT))
            ->set($this->handler->quoteColumn('parent_node'), $query->bindValue(227, null, \PDO::PARAM_INT))
            ->set($this->handler->quoteColumn('parent_remote_id'), $query->bindValue('5238a276bf8231fbcf8a986cdc82a6a5', null, \PDO::PARAM_STR));
        $query->prepare()->execute();

        $gateway = $this->getLocationGateway();

        $gateway->changeMainLocation(
            10, // content id
            228, // new main location id
            2, // content version number
            227 // new main location parent id
        );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            [[228], [228]],
            $query->select(
                'main_node_id'
            )->from(
                'ezcontentobject_tree'
            )->where(
                $query->expr->eq('contentobject_id', 10)
            )
        );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            [[1]],
            $query->select(
                'is_main'
            )->from(
                'eznode_assignment'
            )->where(
                $query->expr->lAnd(
                    $query->expr->eq('contentobject_id', 10),
                    $query->expr->eq('contentobject_version', 2),
                    $query->expr->eq('parent_node', 227)
                )
            )
        );

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            [[0]],
            $query->select(
                'is_main'
            )->from(
                'eznode_assignment'
            )->where(
                $query->expr->lAnd(
                    $query->expr->eq('contentobject_id', 10),
                    $query->expr->eq('contentobject_version', 2),
                    $query->expr->eq('parent_node', 44)
                )
            )
        );
    }

    /**
     * Test for the getChildren() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase::getChildren
     */
    public function testGetChildren()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');

        $gateway = $this->getLocationGateway();
        $childrenRows = $gateway->getChildren(213);

        $this->assertCount(2, $childrenRows);
        $this->assertCount(16, $childrenRows[0]);
        $this->assertEquals(214, $childrenRows[0]['node_id']);
        $this->assertCount(16, $childrenRows[1]);
        $this->assertEquals(215, $childrenRows[1]['node_id']);
    }

    /**
     * Test for the getFallbackMainNodeData() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase::getFallbackMainNodeData
     */
    public function testGetFallbackMainNodeData()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        // Create additional location for test purpose
        $query = $this->handler->createInsertQuery();
        $query->insertInto($this->handler->quoteTable('ezcontentobject_tree'))
            ->set($this->handler->quoteColumn('contentobject_id'), $query->bindValue(12, null, \PDO::PARAM_INT))
            ->set($this->handler->quoteColumn('contentobject_version'), $query->bindValue(1, null, \PDO::PARAM_INT))
            ->set($this->handler->quoteColumn('main_node_id'), $query->bindValue(13, null, \PDO::PARAM_INT))
            ->set($this->handler->quoteColumn('node_id'), $query->bindValue(228, null, \PDO::PARAM_INT))
            ->set($this->handler->quoteColumn('parent_node_id'), $query->bindValue(227, null, \PDO::PARAM_INT))
            ->set($this->handler->quoteColumn('path_string'), $query->bindValue('/1/5/13/228/', null, \PDO::PARAM_STR))
            ->set($this->handler->quoteColumn('remote_id'), $query->bindValue('asdfg123437', null, \PDO::PARAM_STR));
        $query->prepare()->execute();

        $gateway = $this->getLocationGateway();
        $data = $gateway->getFallbackMainNodeData(12, 13);

        $this->assertEquals(228, $data['node_id']);
        $this->assertEquals(1, $data['contentobject_version']);
        $this->assertEquals(227, $data['parent_node_id']);
    }

    /**
     * Test for the removeLocation() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase::removeLocation
     */
    public function testRemoveLocation()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');

        $gateway = $this->getLocationGateway();
        $gateway->removeLocation(13);

        try {
            $gateway->getBasicNodeData(13);
            $this->fail('Location was not deleted!');
        } catch (NotFoundException $e) {
            // Do nothing
        }
    }

    public function providerForTestUpdatePathIdentificationString()
    {
        return [
            [77, 2, 'new_solutions', 'new_solutions'],
            [75, 69, 'stylesheets', 'products/stylesheets'],
        ];
    }

    /**
     * Test for the updatePathIdentificationString() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase::updatePathIdentificationString
     * @dataProvider providerForTestUpdatePathIdentificationString
     */
    public function testUpdatePathIdentificationString($locationId, $parentLocationId, $text, $expected)
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');

        $gateway = $this->getLocationGateway();
        $gateway->updatePathIdentificationString($locationId, $parentLocationId, $text);

        $query = $this->handler->createSelectQuery();
        $this->assertQueryResult(
            [[$expected]],
            $query->select(
                'path_identification_string'
            )->from(
                'ezcontentobject_tree'
            )->where(
                $query->expr->eq('node_id', $locationId)
            )
        );
    }
}
