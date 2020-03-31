<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\ObjectState\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Tests\Content\LanguageAwareTestCase;
use eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\DoctrineDatabase;
use eZ\Publish\SPI\Persistence\Content\ObjectState;
use eZ\Publish\SPI\Persistence\Content\ObjectState\Group;

/**
 * Test case for eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\DoctrineDatabase.
 */
class DoctrineDatabaseTest extends LanguageAwareTestCase
{
    /**
     * Database gateway to test.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\DoctrineDatabase
     */
    protected $databaseGateway;

    /**
     * Language mask generator.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator
     */
    protected $languageMaskGenerator;

    /**
     * Inserts DB fixture.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->insertDatabaseFixture(
            __DIR__ . '/../../_fixtures/contentobjects.php'
        );

        $this->insertDatabaseFixture(
            __DIR__ . '/../../_fixtures/objectstates.php'
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\DoctrineDatabase::loadObjectStateData
     */
    public function testLoadObjectStateData()
    {
        $gateway = $this->getDatabaseGateway();

        $result = $gateway->loadObjectStateData(1);

        $this->assertEquals(
            [
                [
                    'ezcobj_state_default_language_id' => 2,
                    'ezcobj_state_group_id' => 2,
                    'ezcobj_state_id' => 1,
                    'ezcobj_state_identifier' => 'not_locked',
                    'ezcobj_state_language_mask' => 3,
                    'ezcobj_state_priority' => 0,
                    'ezcobj_state_language_description' => '',
                    'ezcobj_state_language_language_id' => 3,
                    'ezcobj_state_language_name' => 'Not locked',
                ],
            ],
            $result
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\DoctrineDatabase::loadObjectStateDataByIdentifier
     */
    public function testLoadObjectStateDataByIdentifier()
    {
        $gateway = $this->getDatabaseGateway();

        $result = $gateway->loadObjectStateDataByIdentifier('not_locked', 2);

        $this->assertEquals(
            [
                [
                    'ezcobj_state_default_language_id' => 2,
                    'ezcobj_state_group_id' => 2,
                    'ezcobj_state_id' => 1,
                    'ezcobj_state_identifier' => 'not_locked',
                    'ezcobj_state_language_mask' => 3,
                    'ezcobj_state_priority' => 0,
                    'ezcobj_state_language_description' => '',
                    'ezcobj_state_language_language_id' => 3,
                    'ezcobj_state_language_name' => 'Not locked',
                ],
            ],
            $result
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\DoctrineDatabase::loadObjectStateListData
     */
    public function testLoadObjectStateListData()
    {
        $gateway = $this->getDatabaseGateway();

        $result = $gateway->loadObjectStateListData(2);

        $this->assertEquals(
            [
                [
                    [
                        'ezcobj_state_default_language_id' => 2,
                        'ezcobj_state_group_id' => 2,
                        'ezcobj_state_id' => 1,
                        'ezcobj_state_identifier' => 'not_locked',
                        'ezcobj_state_language_mask' => 3,
                        'ezcobj_state_priority' => 0,
                        'ezcobj_state_language_description' => '',
                        'ezcobj_state_language_language_id' => 3,
                        'ezcobj_state_language_name' => 'Not locked',
                    ],
                ],
                [
                    [
                        'ezcobj_state_default_language_id' => 2,
                        'ezcobj_state_group_id' => 2,
                        'ezcobj_state_id' => 2,
                        'ezcobj_state_identifier' => 'locked',
                        'ezcobj_state_language_mask' => 3,
                        'ezcobj_state_priority' => 1,
                        'ezcobj_state_language_description' => '',
                        'ezcobj_state_language_language_id' => 3,
                        'ezcobj_state_language_name' => 'Locked',
                    ],
                ],
            ],
            $result
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\DoctrineDatabase::loadObjectStateGroupData
     */
    public function testLoadObjectStateGroupData()
    {
        $gateway = $this->getDatabaseGateway();

        $result = $gateway->loadObjectStateGroupData(2);

        $this->assertEquals(
            [
                [
                    'ezcobj_state_group_default_language_id' => 2,
                    'ezcobj_state_group_id' => 2,
                    'ezcobj_state_group_identifier' => 'ez_lock',
                    'ezcobj_state_group_language_mask' => 3,
                    'ezcobj_state_group_language_description' => '',
                    'ezcobj_state_group_language_language_id' => 3,
                    'ezcobj_state_group_language_real_language_id' => 2,
                    'ezcobj_state_group_language_name' => 'Lock',
                ],
            ],
            $result
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\DoctrineDatabase::loadObjectStateGroupDataByIdentifier
     */
    public function testLoadObjectStateGroupDataByIdentifier()
    {
        $gateway = $this->getDatabaseGateway();

        $result = $gateway->loadObjectStateGroupDataByIdentifier('ez_lock');

        $this->assertEquals(
            [
                [
                    'ezcobj_state_group_default_language_id' => 2,
                    'ezcobj_state_group_id' => 2,
                    'ezcobj_state_group_identifier' => 'ez_lock',
                    'ezcobj_state_group_language_mask' => 3,
                    'ezcobj_state_group_language_description' => '',
                    'ezcobj_state_group_language_language_id' => 3,
                    'ezcobj_state_group_language_real_language_id' => 2,
                    'ezcobj_state_group_language_name' => 'Lock',
                ],
            ],
            $result
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\DoctrineDatabase::loadObjectStateGroupListData
     */
    public function testLoadObjectStateGroupListData()
    {
        $gateway = $this->getDatabaseGateway();

        $result = $gateway->loadObjectStateGroupListData(0, -1);

        $this->assertEquals(
            [
                [
                    [
                        'ezcobj_state_group_default_language_id' => 2,
                        'ezcobj_state_group_id' => 2,
                        'ezcobj_state_group_identifier' => 'ez_lock',
                        'ezcobj_state_group_language_mask' => 3,
                        'ezcobj_state_group_language_description' => '',
                        'ezcobj_state_group_language_language_id' => 3,
                        'ezcobj_state_group_language_real_language_id' => 2,
                        'ezcobj_state_group_language_name' => 'Lock',
                    ],
                ],
            ],
            $result
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\DoctrineDatabase::insertObjectState
     */
    public function testInsertObjectState()
    {
        $gateway = $this->getDatabaseGateway();

        $gateway->insertObjectState($this->getObjectStateFixture(), 2);

        $this->assertEquals(
            [
                [
                    'ezcobj_state_default_language_id' => 4,
                    'ezcobj_state_group_id' => 2,
                    // The new state should be added with state ID = 3
                    'ezcobj_state_id' => 3,
                    'ezcobj_state_identifier' => 'test_state',
                    'ezcobj_state_language_mask' => 5,
                    // The new state should have priority = 2
                    'ezcobj_state_priority' => 2,
                    'ezcobj_state_language_description' => 'Test state description',
                    'ezcobj_state_language_language_id' => 5,
                    'ezcobj_state_language_name' => 'Test state',
                ],
            ],
            // The new state should be added with state ID = 3
            $this->getDatabaseGateway()->loadObjectStateData(3)
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\DoctrineDatabase::insertObjectState
     */
    public function testInsertObjectStateInEmptyGroup()
    {
        $gateway = $this->getDatabaseGateway();

        $gateway->insertObjectStateGroup($this->getObjectStateGroupFixture());
        $gateway->insertObjectState($this->getObjectStateFixture(), 3);

        $this->assertEquals(
            [
                [
                    'ezcobj_state_default_language_id' => 4,
                    // New group should be added with group ID = 3
                    'ezcobj_state_group_id' => 3,
                    // The new state should be added with state ID = 3
                    'ezcobj_state_id' => 3,
                    'ezcobj_state_identifier' => 'test_state',
                    'ezcobj_state_language_mask' => 5,
                    // The new state should have priority = 0
                    'ezcobj_state_priority' => 0,
                    'ezcobj_state_language_description' => 'Test state description',
                    'ezcobj_state_language_language_id' => 5,
                    'ezcobj_state_language_name' => 'Test state',
                ],
            ],
            // The new state should be added with state ID = 3
            $this->getDatabaseGateway()->loadObjectStateData(3)
        );

        $this->assertEquals(
            // 185 is the number of objects in the fixture
            185,
            $gateway->getContentCount(3)
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\DoctrineDatabase::updateObjectState
     */
    public function testUpdateObjectState()
    {
        $gateway = $this->getDatabaseGateway();

        $objectStateFixture = $this->getObjectStateFixture();
        $objectStateFixture->id = 1;

        $gateway->updateObjectState($objectStateFixture);

        $this->assertEquals(
            [
                [
                    'ezcobj_state_default_language_id' => 4,
                    'ezcobj_state_group_id' => 2,
                    'ezcobj_state_id' => 1,
                    'ezcobj_state_identifier' => 'test_state',
                    'ezcobj_state_language_mask' => 5,
                    'ezcobj_state_priority' => 0,
                    'ezcobj_state_language_description' => 'Test state description',
                    'ezcobj_state_language_language_id' => 5,
                    'ezcobj_state_language_name' => 'Test state',
                ],
            ],
            $this->getDatabaseGateway()->loadObjectStateData(1)
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\DoctrineDatabase::deleteObjectState
     */
    public function testDeleteObjectState()
    {
        $gateway = $this->getDatabaseGateway();

        $gateway->deleteObjectState(1);

        $this->assertEquals(
            [],
            $this->getDatabaseGateway()->loadObjectStateData(1)
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\DoctrineDatabase::updateObjectStateLinks
     */
    public function testUpdateObjectStateLinks()
    {
        $gateway = $this->getDatabaseGateway();

        $gateway->updateObjectStateLinks(1, 2);

        self::assertSame(0, $gateway->getContentCount(1));
        self::assertSame(185, $gateway->getContentCount(2));
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\DoctrineDatabase::deleteObjectStateLinks
     */
    public function testDeleteObjectStateLinks()
    {
        $gateway = $this->getDatabaseGateway();

        $gateway->deleteObjectStateLinks(1);

        self::assertSame(0, $gateway->getContentCount(1));
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\DoctrineDatabase::insertObjectStateGroup
     */
    public function testInsertObjectStateGroup()
    {
        $gateway = $this->getDatabaseGateway();

        $gateway->insertObjectStateGroup($this->getObjectStateGroupFixture());

        $this->assertEquals(
            [
                [
                    'ezcobj_state_group_default_language_id' => 4,
                    // The new state group should be added with state group ID = 3
                    'ezcobj_state_group_id' => 3,
                    'ezcobj_state_group_identifier' => 'test_group',
                    'ezcobj_state_group_language_mask' => 5,
                    'ezcobj_state_group_language_description' => 'Test group description',
                    'ezcobj_state_group_language_language_id' => 5,
                    'ezcobj_state_group_language_real_language_id' => 4,
                    'ezcobj_state_group_language_name' => 'Test group',
                ],
            ],
            // The new state group should be added with state group ID = 3
            $this->getDatabaseGateway()->loadObjectStateGroupData(3)
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\DoctrineDatabase::updateObjectStateGroup
     */
    public function testUpdateObjectStateGroup()
    {
        $gateway = $this->getDatabaseGateway();

        $groupFixture = $this->getObjectStateGroupFixture();
        $groupFixture->id = 2;

        $gateway->updateObjectStateGroup($groupFixture);

        $this->assertEquals(
            [
                [
                    'ezcobj_state_group_default_language_id' => 4,
                    'ezcobj_state_group_id' => 2,
                    'ezcobj_state_group_identifier' => 'test_group',
                    'ezcobj_state_group_language_mask' => 5,
                    'ezcobj_state_group_language_description' => 'Test group description',
                    'ezcobj_state_group_language_language_id' => 5,
                    'ezcobj_state_group_language_real_language_id' => 4,
                    'ezcobj_state_group_language_name' => 'Test group',
                ],
            ],
            $this->getDatabaseGateway()->loadObjectStateGroupData(2)
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\DoctrineDatabase::deleteObjectStateGroup
     */
    public function testDeleteObjectStateGroup()
    {
        $gateway = $this->getDatabaseGateway();

        $gateway->deleteObjectStateGroup(2);

        $this->assertEquals(
            [],
            $this->getDatabaseGateway()->loadObjectStateGroupData(2)
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\DoctrineDatabase::setContentState
     */
    public function testSetContentState()
    {
        $gateway = $this->getDatabaseGateway();

        $gateway->setContentState(42, 2, 2);

        $this->assertQueryResult(
            [
                [
                    'contentobject_id' => 42,
                    'contentobject_state_id' => 2,
                ],
            ],
            $this->getDatabaseConnection()->createQueryBuilder()
                ->select('contentobject_id', 'contentobject_state_id')
                ->from('ezcobj_state_link')
                ->where('contentobject_id = 42')
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\DoctrineDatabase::loadObjectStateDataForContent
     */
    public function testLoadObjectStateDataForContent()
    {
        $gateway = $this->getDatabaseGateway();

        $result = $gateway->loadObjectStateDataForContent(42, 2);

        $this->assertEquals(
            [
                [
                    'ezcobj_state_default_language_id' => 2,
                    'ezcobj_state_group_id' => 2,
                    'ezcobj_state_id' => 1,
                    'ezcobj_state_identifier' => 'not_locked',
                    'ezcobj_state_language_mask' => 3,
                    'ezcobj_state_priority' => 0,
                    'ezcobj_state_language_description' => '',
                    'ezcobj_state_language_language_id' => 3,
                    'ezcobj_state_language_name' => 'Not locked',
                ],
            ],
            $result
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\DoctrineDatabase::getContentCount
     */
    public function testGetContentCount()
    {
        $gateway = $this->getDatabaseGateway();

        $result = $gateway->getContentCount(1);

        // 185 is the number of objects in the fixture
        $this->assertEquals(185, $result);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\DoctrineDatabase::updateObjectStatePriority
     */
    public function testUpdateObjectStatePriority()
    {
        $gateway = $this->getDatabaseGateway();

        $gateway->updateObjectStatePriority(1, 10);

        $objectStateData = $gateway->loadObjectStateData(1);

        $this->assertEquals(
            [
                [
                    'ezcobj_state_default_language_id' => 2,
                    'ezcobj_state_group_id' => 2,
                    'ezcobj_state_id' => 1,
                    'ezcobj_state_identifier' => 'not_locked',
                    'ezcobj_state_language_mask' => 3,
                    'ezcobj_state_priority' => 10,
                    'ezcobj_state_language_description' => '',
                    'ezcobj_state_language_language_id' => 3,
                    'ezcobj_state_language_name' => 'Not locked',
                ],
            ],
            $objectStateData
        );
    }

    /**
     * Returns an object state fixture.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState
     */
    protected function getObjectStateFixture()
    {
        $objectState = new ObjectState();
        $objectState->identifier = 'test_state';
        $objectState->defaultLanguage = 'eng-GB';
        $objectState->languageCodes = ['eng-GB'];
        $objectState->name = ['eng-GB' => 'Test state'];
        $objectState->description = ['eng-GB' => 'Test state description'];

        return $objectState;
    }

    /**
     * Returns an object state group fixture.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState\Group
     */
    protected function getObjectStateGroupFixture()
    {
        $group = new Group();
        $group->identifier = 'test_group';
        $group->defaultLanguage = 'eng-GB';
        $group->languageCodes = ['eng-GB'];
        $group->name = ['eng-GB' => 'Test group'];
        $group->description = ['eng-GB' => 'Test group description'];

        return $group;
    }

    /**
     * Returns a ready to test DoctrineDatabase gateway.
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function getDatabaseGateway(): DoctrineDatabase
    {
        if (!isset($this->databaseGateway)) {
            $this->databaseGateway = new DoctrineDatabase(
                $this->getDatabaseConnection(),
                $this->getLanguageMaskGenerator()
            );
        }

        return $this->databaseGateway;
    }
}
