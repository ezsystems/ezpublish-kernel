<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\Section\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway;
use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway\DoctrineDatabase;

/**
 * Test case for eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway\DoctrineDatabase.
 */
class DoctrineDatabaseTest extends TestCase
{
    /**
     * Database gateway to test.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway\DoctrineDatabase
     */
    protected $databaseGateway;

    /**
     * Inserts DB fixture.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->insertDatabaseFixture(
            __DIR__ . '/../../_fixtures/sections.php'
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway\DoctrineDatabase::insertSection
     */
    public function testInsertSection()
    {
        $gateway = $this->getDatabaseGateway();

        $gateway->insertSection('New Section', 'new_section');
        $query = $this->getDatabaseConnection()->createQueryBuilder();

        $this->assertQueryResult(
            [
                [
                    'id' => '7',
                    'identifier' => 'new_section',
                    'name' => 'New Section',
                    'locale' => '',
                ],
            ],
            $query
                ->select('id', 'identifier', 'name', 'locale')
                ->from('ezsection')
                ->where(
                    $query->expr()->eq(
                        'identifier',
                        $query->createPositionalParameter('new_section')
                    )
                )
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway\DoctrineDatabase::updateSection
     */
    public function testUpdateSection()
    {
        $gateway = $this->getDatabaseGateway();

        $gateway->updateSection(2, 'New Section', 'new_section');

        $this->assertQueryResult(
            [
                [
                    'id' => '2',
                    'identifier' => 'new_section',
                    'name' => 'New Section',
                    'locale' => '',
                ],
            ],
            $this->getDatabaseConnection()->createQueryBuilder()
                ->select('id', 'identifier', 'name', 'locale')
                ->from('ezsection')
                ->where('id=2')
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway\DoctrineDatabase::loadSectionData
     */
    public function testLoadSectionData()
    {
        $gateway = $this->getDatabaseGateway();

        $result = $gateway->loadSectionData(2);

        $this->assertEquals(
            [
                [
                    'id' => '2',
                    'identifier' => 'users',
                    'name' => 'Users',
                ],
            ],
            $result
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway\DoctrineDatabase::loadAllSectionData
     */
    public function testLoadAllSectionData()
    {
        $gateway = $this->getDatabaseGateway();

        $result = $gateway->loadAllSectionData();

        $expected = [
            [
                'id' => '1',
                'identifier' => 'standard',
                'name' => 'Standard',
            ],

            [
                'id' => '2',
                'identifier' => 'users',
                'name' => 'Users',
            ],

            [
                'id' => '3',
                'identifier' => 'media',
                'name' => 'Media',
            ],

            [
                'id' => '4',
                'identifier' => 'setup',
                'name' => 'Setup',
            ],

            [
                'id' => '5',
                'identifier' => 'design',
                'name' => 'Design',
            ],

            [
                'id' => '6',
                'identifier' => '',
                'name' => 'Restricted',
            ],
        ];
        $this->assertEquals(
            $expected,
            $result
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway\DoctrineDatabase::loadSectionDataByIdentifier
     */
    public function testLoadSectionDataByIdentifier()
    {
        $gateway = $this->getDatabaseGateway();

        $result = $gateway->loadSectionDataByIdentifier('users');

        $this->assertEquals(
            [
                [
                    'id' => '2',
                    'identifier' => 'users',
                    'name' => 'Users',
                ],
            ],
            $result
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway\DoctrineDatabase::countContentObjectsInSection
     */
    public function testCountContentObjectsInSection()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../../_fixtures/contentobjects.php'
        );

        $gateway = $this->getDatabaseGateway();

        $result = $gateway->countContentObjectsInSection(2);

        $this->assertSame(
            7,
            $result
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway\DoctrineDatabase::countRoleAssignmentsUsingSection
     */
    public function testCountRoleAssignmentsUsingSection()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../../../User/_fixtures/roles.php'
        );

        $gateway = $this->getDatabaseGateway();

        $result = $gateway->countRoleAssignmentsUsingSection(2);

        $this->assertSame(
            1,
            $result
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway\DoctrineDatabase::deleteSection
     */
    public function testDeleteSection()
    {
        $gateway = $this->getDatabaseGateway();

        $gateway->deleteSection(2);

        $this->assertQueryResult(
            [
                [
                    'count' => '5',
                ],
            ],
            $this->getDatabaseConnection()->createQueryBuilder()
                ->select('COUNT( * ) AS count')
                ->from('ezsection')
        );

        $this->assertQueryResult(
            [
                [
                    'count' => '0',
                ],
            ],
            $this->getDatabaseConnection()->createQueryBuilder()
                ->select('COUNT( * ) AS count')
                ->from('ezsection')
                ->where('id=2')
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway\DoctrineDatabase::assignSectionToContent
     * @depends testCountContentObjectsInSection
     */
    public function testAssignSectionToContent()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../../_fixtures/contentobjects.php'
        );

        $gateway = $this->getDatabaseGateway();

        $beforeCount = $gateway->countContentObjectsInSection(4);

        $result = $gateway->assignSectionToContent(4, 10);

        $this->assertSame(
            $beforeCount + 1,
            $gateway->countContentObjectsInSection(4)
        );
    }

    /**
     * Returns a ready to test DoctrineDatabase gateway.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway\DoctrineDatabase
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function getDatabaseGateway(): Gateway
    {
        if (!isset($this->databaseGateway)) {
            $this->databaseGateway = new DoctrineDatabase($this->getDatabaseConnection());
        }

        return $this->databaseGateway;
    }
}
