<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\Location\Gateway;

use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\ParameterType;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway;
use eZ\Publish\Core\Persistence\Legacy\Tests\Content\LanguageAwareTestCase;
use eZ\Publish\SPI\Persistence\Content\Location;
use eZ\Publish\SPI\Persistence\Content\Location\CreateStruct;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Test case for eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase.
 */
class DoctrineDatabaseTest extends LanguageAwareTestCase
{
    protected function getLocationGateway()
    {
        return new DoctrineDatabase(
            $this->getDatabaseConnection(),
            $this->getLanguageMaskGenerator()
        );
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
        $gateway = $this->getLocationGateway();
        $data = $gateway->getBasicNodeDataByRemoteId('dbc2f3c8716c12f32c379dbf0b1cb133');

        self::assertLoadLocationProperties($data);
    }

    public function testLoadLocation()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $gateway = $this->getLocationGateway();
        $data = $gateway->getBasicNodeData(77);

        self::assertLoadLocationProperties($data);
    }

    public function testLoadLocationList()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $gateway = $this->getLocationGateway();
        $locationsData = $gateway->getNodeDataList([77]);

        self::assertCount(1, $locationsData);

        $locationRow = reset($locationsData);

        self::assertLoadLocationProperties($locationRow);
    }

    public function testLoadInvalidLocation()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\NotFoundException::class);

        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $gateway = $this->getLocationGateway();
        $gateway->getBasicNodeData(1337);
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
        $gateway = $this->getLocationGateway();
        $gateway->moveSubtreeNodes(
            [
                'path_string' => '/1/2/69/',
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

        $query = $this->getDatabaseConnection()->createQueryBuilder();
        $this->assertQueryResult(
            [
                [65, '/1/2/', '', 1, 1, 0, 0],
                [67, '/1/2/77/69/', 'solutions/products', 77, 3, 0, 0],
                [69, '/1/2/77/69/70/71/', 'solutions/products/software/os_type_i', 70, 5, 0, 0],
                [73, '/1/2/77/69/72/75/', 'solutions/products/boxes/cd_dvd_box_iii', 72, 5, 0, 0],
                [75, '/1/2/77/', 'solutions', 2, 2, 0, 0],
            ],
            $query
                ->select(
                    'contentobject_id',
                    'path_string',
                    'path_identification_string',
                    'parent_node_id',
                    'depth',
                    'is_hidden',
                    'is_invisible'
                )
                ->from('ezcontentobject_tree')
                ->where($query->expr()->in('node_id', [69, 71, 75, 77, 2]))
                ->orderBy('contentobject_id')
        );
    }

    public function testMoveHiddenDestinationUpdate()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $gateway = $this->getLocationGateway();
        $gateway->hideSubtree('/1/2/77/');
        $gateway->moveSubtreeNodes(
            [
                'path_string' => '/1/2/69/',
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

        $query = $this->getDatabaseConnection()->createQueryBuilder();
        $this->assertQueryResult(
            [
                [65, '/1/2/', '', 1, 1, 0, 0],
                [67, '/1/2/77/69/', 'solutions/products', 77, 3, 0, 1],
                [69, '/1/2/77/69/70/71/', 'solutions/products/software/os_type_i', 70, 5, 0, 1],
                [73, '/1/2/77/69/72/75/', 'solutions/products/boxes/cd_dvd_box_iii', 72, 5, 0, 1],
                [75, '/1/2/77/', 'solutions', 2, 2, 1, 1],
            ],
            $query
                ->select(
                    'contentobject_id',
                    'path_string',
                    'path_identification_string',
                    'parent_node_id',
                    'depth',
                    'is_hidden',
                    'is_invisible'
                )
                ->from('ezcontentobject_tree')
                ->where($query->expr()->in('node_id', [69, 71, 75, 77, 2]))
                ->orderBy('contentobject_id')
        );
    }

    public function testMoveHiddenSourceUpdate()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $gateway = $this->getLocationGateway();
        $gateway->hideSubtree('/1/2/69/');
        $gateway->moveSubtreeNodes(
            [
                'path_string' => '/1/2/69/',
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

        $query = $this->getDatabaseConnection()->createQueryBuilder();
        $this->assertQueryResult(
            [
                [65, '/1/2/', '', 1, 1, 0, 0],
                [67, '/1/2/77/69/', 'solutions/products', 77, 3, 1, 1],
                [69, '/1/2/77/69/70/71/', 'solutions/products/software/os_type_i', 70, 5, 0, 1],
                [73, '/1/2/77/69/72/75/', 'solutions/products/boxes/cd_dvd_box_iii', 72, 5, 0, 1],
                [75, '/1/2/77/', 'solutions', 2, 2, 0, 0],
            ],
            $query
                ->select(
                    'contentobject_id',
                    'path_string',
                    'path_identification_string',
                    'parent_node_id',
                    'depth',
                    'is_hidden',
                    'is_invisible'
                )
                ->from('ezcontentobject_tree')
                ->where($query->expr()->in('node_id', [69, 71, 75, 77, 2]))
                ->orderBy('contentobject_id')
        );
    }

    public function testMoveHiddenSourceChildUpdate()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $gateway = $this->getLocationGateway();
        $gateway->hideSubtree('/1/2/69/70/');

        $gateway->moveSubtreeNodes(
            [
                'path_string' => '/1/2/69/',
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

        $query = $this->getDatabaseConnection()->createQueryBuilder();
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
                ->select(
                    'contentobject_id',
                    'path_string',
                    'path_identification_string',
                    'parent_node_id',
                    'depth',
                    'is_hidden',
                    'is_invisible'
                )
                ->from('ezcontentobject_tree')
                ->where($query->expr()->in('node_id', [69, 70, 71, 75, 77, 2]))
                ->orderBy('contentobject_id')
        );
    }

    /**
     * @throws \Exception
     */
    public function testMoveSubtreeAssignmentUpdate()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $gateway = $this->getLocationGateway();
        $gateway->updateNodeAssignment(67, 2, 77, 5);

        $query = $this->getDatabaseConnection()->createQueryBuilder();
        $this->assertQueryResult(
            [
                [67, 1, 0, 53, 1, 5, 77, '9cec85d730eec7578190ee95ce5a36f5', 0, 2, 1, 0, 0],
            ],
            $query
                ->select(
                    [
                        'contentobject_id',
                        'contentobject_version',
                        'from_node_id',
                        'id',
                        'is_main',
                        'op_code',
                        'parent_node',
                        'parent_remote_id',
                        'remote_id',
                        'sort_field',
                        'sort_order',
                        'priority',
                        'is_hidden',
                    ]
                )
                ->from('eznode_assignment')
                ->where($query->expr()->eq('contentobject_id', 67))
        );
    }

    public function testUpdateSubtreeModificationTime()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $gateway = $this->getLocationGateway();
        $time = time();
        $gateway->updateSubtreeModificationTime('/1/2/69/');

        $query = $this->getDatabaseConnection()->createQueryBuilder();
        $this->assertQueryResult(
            [
                ['/1/'],
                ['/1/2/'],
                ['/1/2/69/'],
            ],
            $query
                ->select('path_string')
                ->from('ezcontentobject_tree')
                ->where($query->expr()->gte('modified_subnode', $time))
                ->orderBy('path_string')
        );
    }

    public function testHideUpdateHidden()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $gateway = $this->getLocationGateway();
        $gateway->hideSubtree('/1/2/69/');

        $query = $this->getDatabaseConnection()->createQueryBuilder();
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
                ->where($query->expr()->in('node_id', [1, 2, 69, 75]))
                ->orderBy('node_id')
        );
    }

    /**
     * @depends testHideUpdateHidden
     */
    public function testHideUnhideUpdateHidden()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $gateway = $this->getLocationGateway();
        $gateway->hideSubtree('/1/2/69/');
        $gateway->unhideSubtree('/1/2/69/');

        $query = $this->getDatabaseConnection()->createQueryBuilder();
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
                ->where($query->expr()->in('node_id', [1, 2, 69, 75]))
                ->orderBy('node_id')
        );
    }

    /**
     * @depends testHideUpdateHidden
     */
    public function testHideUnhideParentTree()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $gateway = $this->getLocationGateway();
        $gateway->hideSubtree('/1/2/69/');
        $gateway->hideSubtree('/1/2/69/70/');
        $gateway->unhideSubtree('/1/2/69/');

        $query = $this->getDatabaseConnection()->createQueryBuilder();
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
                ->where($query->expr()->in('node_id', [1, 2, 69, 70, 71, 75]))
                ->orderBy('node_id')
        );
    }

    /**
     * @depends testHideUpdateHidden
     */
    public function testHideUnhidePartialSubtree()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $gateway = $this->getLocationGateway();
        $gateway->hideSubtree('/1/2/69/');
        $gateway->hideSubtree('/1/2/69/70/');
        $gateway->unhideSubtree('/1/2/69/70/');

        $query = $this->getDatabaseConnection()->createQueryBuilder();
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
                ->where($query->expr()->in('node_id', [1, 2, 69, 70, 71, 75]))
                ->orderBy('node_id')
        );
    }

    public function testSwapLocations()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $gateway = $this->getLocationGateway();
        $gateway->swap(70, 78);

        $query = $this->getDatabaseConnection()->createQueryBuilder();
        $this->assertQueryResult(
            [
                [70, 76],
                [78, 68],
            ],
            $query
                ->select('node_id', 'contentobject_id')
                ->from('ezcontentobject_tree')
                ->where($query->expr()->in('node_id', [70, 78]))
                ->orderBy('node_id')
        );
    }

    public function testCreateLocation()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $gateway = $this->getLocationGateway();
        $gateway->create(
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

        $query = $this->getDatabaseConnection()->createQueryBuilder();
        $this->assertQueryResult(
            [
                [70, '/1/2/69/70/'],
                [77, '/1/2/77/'],
                [228, '/1/2/77/228/'],
            ],
            $query
                ->select('node_id', 'path_string')
                ->from('ezcontentobject_tree')
                ->where($query->expr()->in('contentobject_id', [68, 75]))
                ->orderBy('node_id')
        );
    }

    /**
     * @covers  \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase::getMainNodeId
     * @depends testCreateLocation
     */
    public function testGetMainNodeId()
    {
        $gateway = $this->getLocationGateway();

        $parentLocationData = [
            'node_id' => '77',
            'depth' => '2',
            'path_string' => '/1/2/77/',
        ];

        // main location
        $mainLocation = $gateway->create(
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
        $gateway->create(
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

        $gatewayReflection = new \ReflectionObject($gateway);
        $methodReflection = $gatewayReflection->getMethod('getMainNodeId');
        $methodReflection->setAccessible(true);
        self::assertEquals($mainLocation->id, $res = $methodReflection->invoke($gateway, 68));
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
     * @depends      testCreateLocation
     * @dataProvider getCreateLocationValues
     */
    public function testCreateLocationValues($field, $value)
    {
        if ($value === null) {
            $this->markTestIncomplete('Proper value setting yet unknown.');
        }

        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $gateway = $this->getLocationGateway();
        $gateway->create(
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

        $query = $this->getDatabaseConnection()->createQueryBuilder();
        $this->assertQueryResult(
            [[$value]],
            $query
                ->select($field)
                ->from('ezcontentobject_tree')
                ->where($query->expr()->eq('node_id', 228))
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
     * @depends      testCreateLocation
     * @dataProvider getCreateLocationReturnValues
     */
    public function testCreateLocationReturnValues($field, $value)
    {
        if ($value === null) {
            $this->markTestIncomplete('Proper value setting yet unknown.');
        }

        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $gateway = $this->getLocationGateway();
        $location = $gateway->create(
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
        $gateway = $this->getLocationGateway();
        $gateway->update(
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

        $query = $this->getDatabaseConnection()->createQueryBuilder();
        $this->assertQueryResult(
            [[$value]],
            $query
                ->select($field)
                ->from('ezcontentobject_tree')
                ->where($query->expr()->in('node_id', [70]))
        );
    }

    public static function getNodeAssignmentValues()
    {
        return [
            ['contentobject_version', [1]],
            ['from_node_id', [0]],
            ['id', [215]],
            ['is_main', [0]],
            ['op_code', [3]],
            ['parent_node', [77]],
            ['parent_remote_id', ['some_id']],
            ['remote_id', ['0']],
            ['sort_field', [2]],
            ['sort_order', [0]],
            ['is_main', [0]],
            ['priority', [1]],
            ['is_hidden', [1]],
        ];
    }

    private function buildGenericNodeSelectContentWithParentQuery(
        int $contentId,
        int $parentLocationId,
        string $nodeTable,
        string $parentNodeIdColumnName,
        array $fields
    ): QueryBuilder {
        $query = $this->getDatabaseConnection()->createQueryBuilder();
        $expr = $query->expr();
        $query
            ->select($fields)
            ->from($nodeTable)
            ->where(
                $expr->eq(
                    'contentobject_id',
                    $query->createPositionalParameter($contentId, ParameterType::INTEGER)
                )
            )
            ->andWhere(
                $expr->eq(
                    $parentNodeIdColumnName,
                    $query->createPositionalParameter($parentLocationId, ParameterType::INTEGER)
                )
            );

        return $query;
    }

    private function buildNodeAssignmentSelectContentWithParentQuery(
        int $contentId,
        int $parentLocationId,
        array $fields
    ): QueryBuilder {
        return $this->buildGenericNodeSelectContentWithParentQuery(
            $contentId,
            $parentLocationId,
            'eznode_assignment',
            'parent_node',
            $fields
        );
    }

    private function buildContentTreeSelectContentWithParentQuery(
        int $contentId,
        int $parentLocationId,
        array $fields
    ): QueryBuilder {
        return $this->buildGenericNodeSelectContentWithParentQuery(
            $contentId,
            $parentLocationId,
            Gateway::CONTENT_TREE_TABLE,
            'parent_node_id',
            $fields
        );
    }

    /**
     * @depends      testCreateLocation
     * @dataProvider getNodeAssignmentValues
     *
     * @param string $field
     * @param array $expectedResult
     */
    public function testCreateLocationNodeAssignmentCreation(string $field, array $expectedResult)
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $gateway = $this->getLocationGateway();
        $gateway->createNodeAssignment(
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
            77,
            DoctrineDatabase::NODE_ASSIGNMENT_OP_CODE_CREATE
        );

        $this->assertQueryResult(
            [$expectedResult],
            $this->buildNodeAssignmentSelectContentWithParentQuery(68, 77, [$field])
        );
    }

    /**
     * @depends testCreateLocation
     */
    public function testCreateLocationNodeAssignmentCreationMainLocation()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $gateway = $this->getLocationGateway();
        $gateway->createNodeAssignment(
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

        $this->assertQueryResult(
            [[1]],
            $this->buildNodeAssignmentSelectContentWithParentQuery(68, 77, ['is_main'])
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

        $query = $this->getDatabaseConnection()->createQueryBuilder();
        $this->assertQueryResult(
            [
                [2],
            ],
            $query->select(
                'contentobject_version'
            )->from(
                'ezcontentobject_tree'
            )->where(
                $query->expr()->eq(
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
        $gateway = $this->getLocationGateway();

        $gateway->deleteNodeAssignment(11);

        $query = $this->getDatabaseConnection()->createQueryBuilder();
        $this->assertQueryResult(
            [[0]],
            $query
                ->select('count(*)')
                ->from('eznode_assignment')
                ->where(
                    $query->expr()->eq('contentobject_id', 11)
                )
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase::deleteNodeAssignment
     */
    public function testDeleteNodeAssignmentWithSecondArgument()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        $gateway = $this->getLocationGateway();

        $query = $this->getDatabaseConnection()->createQueryBuilder();
        $query
            ->select('count(*)')
            ->from('eznode_assignment')
            ->where(
                $query->expr()->eq('contentobject_id', 11)
            );
        $statement = $query->execute();
        $nodeAssignmentsCount = (int)$statement->fetchColumn();

        $gateway->deleteNodeAssignment(11, 1);

        $query = $this->getDatabaseConnection()->createQueryBuilder();
        $this->assertQueryResult(
            [[$nodeAssignmentsCount - 1]],
            $query
                ->select('count(*)')
                ->from('eznode_assignment')
                ->where(
                    $query->expr()->eq('contentobject_id', 11)
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
     * @depends      testCreateLocationNodeAssignmentCreation
     * @dataProvider getConvertNodeAssignmentsLocationValues
     */
    public function testConvertNodeAssignments($field, $value)
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');

        $gateway = $this->getLocationGateway();
        $gateway->createNodeAssignment(
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

        $gateway->createLocationsFromNodeAssignments(68, 1);

        $query = $this->getDatabaseConnection()->createQueryBuilder();
        $expr = $query->expr();
        $query
            ->select($field)
            ->from(Gateway::CONTENT_TREE_TABLE)
            ->where(
                $expr->eq(
                    'contentobject_id',
                    $query->createPositionalParameter(68, ParameterType::INTEGER)
                )
            )
            ->andWhere(
                $expr->eq(
                    'parent_node_id',
                    $query->createPositionalParameter(77, ParameterType::INTEGER)
                )
            );

        if ($field === 'modified_subnode') {
            $statement = $query->execute();
            $result = $statement->fetch(FetchMode::ASSOCIATIVE);
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

        $gateway = $this->getLocationGateway();
        $gateway->createNodeAssignment(
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
            77,
            DoctrineDatabase::NODE_ASSIGNMENT_OP_CODE_CREATE
        );

        $gateway->createLocationsFromNodeAssignments(68, 1);

        $this->assertQueryResult(
            [[228]],
            $this->buildContentTreeSelectContentWithParentQuery(68, 77, ['main_node_id'])
        );
    }

    /**
     * @depends testCreateLocationNodeAssignmentCreation
     */
    public function testConvertNodeAssignmentsParentHidden()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');

        $gateway = $this->getLocationGateway();
        $gateway->createNodeAssignment(
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
            224,
            DoctrineDatabase::NODE_ASSIGNMENT_OP_CODE_CREATE
        );

        $gateway->createLocationsFromNodeAssignments(68, 1);

        $this->assertQueryResult(
            [[0, 1]],
            $this->buildContentTreeSelectContentWithParentQuery(
                68,
                224,
                ['is_hidden, is_invisible']
            )
        );
    }

    /**
     * @depends testCreateLocationNodeAssignmentCreation
     */
    public function testConvertNodeAssignmentsParentInvisible()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');

        $gateway = $this->getLocationGateway();
        $gateway->createNodeAssignment(
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
            225,
            DoctrineDatabase::NODE_ASSIGNMENT_OP_CODE_CREATE
        );

        $gateway->createLocationsFromNodeAssignments(68, 1);

        $this->assertQueryResult(
            [[0, 1]],
            $this->buildContentTreeSelectContentWithParentQuery(
                68,
                225,
                ['is_hidden, is_invisible']
            )
        );
    }

    /**
     * @depends testCreateLocationNodeAssignmentCreation
     */
    public function testConvertNodeAssignmentsUpdateAssignment()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');

        $gateway = $this->getLocationGateway();
        $gateway->createNodeAssignment(
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

        $gateway->createLocationsFromNodeAssignments(68, 1);

        $this->assertQueryResult(
            [[DoctrineDatabase::NODE_ASSIGNMENT_OP_CODE_CREATE_NOP]],
            $this->buildNodeAssignmentSelectContentWithParentQuery(68, 77, ['op_code'])
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
        $gateway = $this->getLocationGateway();
        $gateway->setSectionForSubtree('/1/2/69/70/', 23);

        $query = $this->getDatabaseConnection()->createQueryBuilder();
        $this->assertQueryResult(
            [[68], [69]],
            $query
                ->select('id')
                ->from('ezcontentobject')
                ->where($query->expr()->eq('section_id', 23))
        );
    }

    /**
     * Test for the changeMainLocation() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase::changeMainLocation
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testChangeMainLocation()
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        // Create additional location and assignment for test purpose
        $connection = $this->getDatabaseConnection();
        $query = $connection->createQueryBuilder();
        $query
            ->insert('ezcontentobject_tree')
            ->values(
                [
                    'contentobject_id' => $query->createPositionalParameter(
                        10,
                        ParameterType::INTEGER
                    ),
                    'contentobject_version' => $query->createPositionalParameter(
                        2,
                        ParameterType::INTEGER
                    ),
                    'main_node_id' => $query->createPositionalParameter(15, ParameterType::INTEGER),
                    'node_id' => $query->createPositionalParameter(228, ParameterType::INTEGER),
                    'parent_node_id' => $query->createPositionalParameter(
                        227,
                        ParameterType::INTEGER
                    ),
                    'path_string' => $query->createPositionalParameter(
                        '/1/5/13/228/',
                        ParameterType::STRING
                    ),
                    'remote_id' => $query->createPositionalParameter(
                        'asdfg123437',
                        ParameterType::STRING
                    ),
                ]
            );
        $query->execute();

        $query = $connection->createQueryBuilder();
        $query
            ->insert('eznode_assignment')
            ->values(
                [
                    'contentobject_id' => $query->createPositionalParameter(
                        10,
                        ParameterType::INTEGER
                    ),
                    'contentobject_version' => $query->createPositionalParameter(
                        2,
                        ParameterType::INTEGER
                    ),
                    'id' => $query->createPositionalParameter(0, ParameterType::INTEGER),
                    'is_main' => $query->createPositionalParameter(0, ParameterType::INTEGER),
                    'parent_node' => $query->createPositionalParameter(227, ParameterType::INTEGER),
                    'parent_remote_id' => $query->createPositionalParameter(
                        '5238a276bf8231fbcf8a986cdc82a6a5',
                        ParameterType::STRING
                    ),
                ]
            );
        $query->execute();

        $gateway = $this->getLocationGateway();

        $gateway->changeMainLocation(
            10, // content id
            228, // new main location id
            2, // content version number
            227 // new main location parent id
        );

        $query = $connection->createQueryBuilder();
        $this->assertQueryResult(
            [[228], [228]],
            $query
                ->select('main_node_id')
                ->from('ezcontentobject_tree')
                ->where(
                    $query->expr()->eq(
                        'contentobject_id',
                        $query->createPositionalParameter(10, ParameterType::INTEGER)
                    )
                )
        );

        $query = $connection->createQueryBuilder();
        $this->assertQueryResult(
            [[1]],
            $query
                ->select('is_main')
                ->from('eznode_assignment')
                ->where(
                    $query->expr()->andX(
                        $query->expr()->eq(
                            'contentobject_id',
                            $query->createPositionalParameter(10, ParameterType::INTEGER)
                        ),
                        $query->expr()->eq(
                            'contentobject_version',
                            $query->createPositionalParameter(2, ParameterType::INTEGER)
                        ),
                        $query->expr()->eq(
                            'parent_node',
                            $query->createPositionalParameter(227, ParameterType::INTEGER)
                        )
                    )
                )
        );

        $query = $connection->createQueryBuilder();
        $this->assertQueryResult(
            [[0]],
            $query
                ->select('is_main')
                ->from('eznode_assignment')
                ->where(
                    $query->expr()->andX(
                        $query->expr()->eq(
                            'contentobject_id',
                            $query->createPositionalParameter(10, ParameterType::INTEGER)
                        ),
                        $query->expr()->eq(
                            'contentobject_version',
                            $query->createPositionalParameter(2, ParameterType::INTEGER)
                        ),
                        $query->expr()->eq(
                            'parent_node',
                            $query->createPositionalParameter(44, ParameterType::INTEGER)
                        )
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
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function testGetFallbackMainNodeData(): void
    {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');
        // Create additional location for test purpose
        $connection = $this->getDatabaseConnection();
        $query = $connection->createQueryBuilder();
        $query
            ->insert('ezcontentobject_tree')
            ->values(
                [
                    'contentobject_id' => $query->createPositionalParameter(
                        12,
                        ParameterType::INTEGER
                    ),
                    'contentobject_version' => $query->createPositionalParameter(
                        1,
                        ParameterType::INTEGER
                    ),
                    'main_node_id' => $query->createPositionalParameter(13, ParameterType::INTEGER),
                    'node_id' => $query->createPositionalParameter(228, ParameterType::INTEGER),
                    'parent_node_id' => $query->createPositionalParameter(
                        227,
                        ParameterType::INTEGER
                    ),
                    'path_string' => $query->createPositionalParameter(
                        '/1/5/13/228/',
                        ParameterType::STRING
                    ),
                    'remote_id' => $query->createPositionalParameter(
                        'asdfg123437',
                        ParameterType::STRING
                    ),
                ]
            );
        $query->execute();

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
     * @covers       \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase::updatePathIdentificationString
     * @dataProvider providerForTestUpdatePathIdentificationString
     */
    public function testUpdatePathIdentificationString(
        $locationId,
        $parentLocationId,
        $text,
        $expected
    ) {
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/full_example_tree.php');

        $gateway = $this->getLocationGateway();
        $gateway->updatePathIdentificationString($locationId, $parentLocationId, $text);

        $query = $this->getDatabaseConnection()->createQueryBuilder();
        $this->assertQueryResult(
            [[$expected]],
            $query->select(
                'path_identification_string'
            )->from(
                'ezcontentobject_tree'
            )->where(
                $query->expr()->eq('node_id', $locationId)
            )
        );
    }
}
