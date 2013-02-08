<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\ObjectState\Gateway\EzcDatabaseTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\ObjectState\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Tests\Content\LanguageAwareTestCase;
use eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\EzcDatabase;
use eZ\Publish\SPI\Persistence\Content\ObjectState;
use eZ\Publish\SPI\Persistence\Content\ObjectState\Group;

/**
 * Test case for eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\EzcDatabase.
 */
class EzcDatabaseTest extends LanguageAwareTestCase
{
    /**
     * Database gateway to test.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\EzcDatabase
     */
    protected $databaseGateway;

    /**
     * Language mask generator
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator
     */
    protected $languageMaskGenerator;

    /**
     * Inserts DB fixture.
     *
     * @return void
     */
    public function setUp()
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
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\EzcDatabase::__construct
     *
     * @return void
     */
    public function testCtor()
    {
        $handler = $this->getDatabaseHandler();
        $gateway = $this->getDatabaseGateway();

        $this->assertAttributeSame(
            $handler,
            'dbHandler',
            $gateway
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\EzcDatabase::loadObjectStateData
     *
     * @return void
     */
    public function testLoadObjectStateData()
    {
        $gateway = $this->getDatabaseGateway();

        $result = $gateway->loadObjectStateData( 1 );

        $this->assertEquals(
            array(
                array(
                    'ezcobj_state_default_language_id' => 2,
                    'ezcobj_state_group_id' => 2,
                    'ezcobj_state_id' => 1,
                    'ezcobj_state_identifier' => 'not_locked',
                    'ezcobj_state_language_mask' => 3,
                    'ezcobj_state_priority' => 0,
                    'ezcobj_state_language_description' => '',
                    'ezcobj_state_language_language_id' => 3,
                    'ezcobj_state_language_name' => 'Not locked'
                )
            ),
            $result
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\EzcDatabase::loadObjectStateDataByIdentifier
     *
     * @return void
     */
    public function testLoadObjectStateDataByIdentifier()
    {
        $gateway = $this->getDatabaseGateway();

        $result = $gateway->loadObjectStateDataByIdentifier( 'not_locked', 2 );

        $this->assertEquals(
            array(
                array(
                    'ezcobj_state_default_language_id' => 2,
                    'ezcobj_state_group_id' => 2,
                    'ezcobj_state_id' => 1,
                    'ezcobj_state_identifier' => 'not_locked',
                    'ezcobj_state_language_mask' => 3,
                    'ezcobj_state_priority' => 0,
                    'ezcobj_state_language_description' => '',
                    'ezcobj_state_language_language_id' => 3,
                    'ezcobj_state_language_name' => 'Not locked'
                )
            ),
            $result
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\EzcDatabase::loadObjectStateListData
     *
     * @return void
     */
    public function testLoadObjectStateListData()
    {
        $gateway = $this->getDatabaseGateway();

        $result = $gateway->loadObjectStateListData( 2 );

        $this->assertEquals(
            array(
                array(
                    array(
                        'ezcobj_state_default_language_id' => 2,
                        'ezcobj_state_group_id' => 2,
                        'ezcobj_state_id' => 1,
                        'ezcobj_state_identifier' => 'not_locked',
                        'ezcobj_state_language_mask' => 3,
                        'ezcobj_state_priority' => 0,
                        'ezcobj_state_language_description' => '',
                        'ezcobj_state_language_language_id' => 3,
                        'ezcobj_state_language_name' => 'Not locked'
                    )
                ),
                array(
                    array(
                        'ezcobj_state_default_language_id' => 2,
                        'ezcobj_state_group_id' => 2,
                        'ezcobj_state_id' => 2,
                        'ezcobj_state_identifier' => 'locked',
                        'ezcobj_state_language_mask' => 3,
                        'ezcobj_state_priority' => 1,
                        'ezcobj_state_language_description' => '',
                        'ezcobj_state_language_language_id' => 3,
                        'ezcobj_state_language_name' => 'Locked'
                    )
                )
            ),
            $result
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\EzcDatabase::loadObjectStateGroupData
     *
     * @return void
     */
    public function testLoadObjectStateGroupData()
    {
        $gateway = $this->getDatabaseGateway();

        $result = $gateway->loadObjectStateGroupData( 2 );

        $this->assertEquals(
            array(
                array(
                    'ezcobj_state_group_default_language_id' => 2,
                    'ezcobj_state_group_id' => 2,
                    'ezcobj_state_group_identifier' => 'ez_lock',
                    'ezcobj_state_group_language_mask' => 3,
                    'ezcobj_state_group_language_description' => '',
                    'ezcobj_state_group_language_language_id' => 3,
                    'ezcobj_state_group_language_real_language_id' => 2,
                    'ezcobj_state_group_language_name' => 'Lock'
                )
            ),
            $result
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\EzcDatabase::loadObjectStateGroupDataByIdentifier
     *
     * @return void
     */
    public function testLoadObjectStateGroupDataByIdentifier()
    {
        $gateway = $this->getDatabaseGateway();

        $result = $gateway->loadObjectStateGroupDataByIdentifier( 'ez_lock' );

        $this->assertEquals(
            array(
                array(
                    'ezcobj_state_group_default_language_id' => 2,
                    'ezcobj_state_group_id' => 2,
                    'ezcobj_state_group_identifier' => 'ez_lock',
                    'ezcobj_state_group_language_mask' => 3,
                    'ezcobj_state_group_language_description' => '',
                    'ezcobj_state_group_language_language_id' => 3,
                    'ezcobj_state_group_language_real_language_id' => 2,
                    'ezcobj_state_group_language_name' => 'Lock'
                )
            ),
            $result
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\EzcDatabase::loadObjectStateGroupListData
     *
     * @return void
     */
    public function testLoadObjectStateGroupListData()
    {
        $gateway = $this->getDatabaseGateway();

        $result = $gateway->loadObjectStateGroupListData( 0, -1 );

        $this->assertEquals(
            array(
                array(
                    array(
                        'ezcobj_state_group_default_language_id' => 2,
                        'ezcobj_state_group_id' => 2,
                        'ezcobj_state_group_identifier' => 'ez_lock',
                        'ezcobj_state_group_language_mask' => 3,
                        'ezcobj_state_group_language_description' => '',
                        'ezcobj_state_group_language_language_id' => 3,
                        'ezcobj_state_group_language_real_language_id' => 2,
                        'ezcobj_state_group_language_name' => 'Lock'
                    )
                )
            ),
            $result
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\EzcDatabase::insertObjectState
     *
     * @return void
     */
    public function testInsertObjectState()
    {
        $gateway = $this->getDatabaseGateway();

        $gateway->insertObjectState( $this->getObjectStateFixture(), 2 );

        $this->assertEquals(
            array(
                array(
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
                    'ezcobj_state_language_name' => 'Test state'
                )
            ),
            // The new state should be added with state ID = 3
            $this->getDatabaseGateway()->loadObjectStateData( 3 )
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\EzcDatabase::insertObjectState
     *
     * @return void
     */
    public function testInsertObjectStateInEmptyGroup()
    {
        $gateway = $this->getDatabaseGateway();

        $gateway->insertObjectStateGroup( $this->getObjectStateGroupFixture() );
        $gateway->insertObjectState( $this->getObjectStateFixture(), 3 );

        $this->assertEquals(
            array(
                array(
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
                    'ezcobj_state_language_name' => 'Test state'
                )
            ),
            // The new state should be added with state ID = 3
            $this->getDatabaseGateway()->loadObjectStateData( 3 )
        );

        $this->assertEquals(
            // 185 is the number of objects in the fixture
            185,
            $gateway->getContentCount( 3 )
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\EzcDatabase::updateObjectState
     *
     * @return void
     */
    public function testUpdateObjectState()
    {
        $gateway = $this->getDatabaseGateway();

        $objectStateFixture = $this->getObjectStateFixture();
        $objectStateFixture->id = 1;

        $gateway->updateObjectState( $objectStateFixture );

        $this->assertEquals(
            array(
                array(
                    'ezcobj_state_default_language_id' => 4,
                    'ezcobj_state_group_id' => 2,
                    'ezcobj_state_id' => 1,
                    'ezcobj_state_identifier' => 'test_state',
                    'ezcobj_state_language_mask' => 5,
                    'ezcobj_state_priority' => 0,
                    'ezcobj_state_language_description' => 'Test state description',
                    'ezcobj_state_language_language_id' => 5,
                    'ezcobj_state_language_name' => 'Test state'
                )
            ),
            $this->getDatabaseGateway()->loadObjectStateData( 1 )
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\EzcDatabase::deleteObjectState
     *
     * @return void
     */
    public function testDeleteObjectState()
    {
        $gateway = $this->getDatabaseGateway();

        $gateway->deleteObjectState( 1 );

        $this->assertEquals(
            array(),
            $this->getDatabaseGateway()->loadObjectStateData( 1 )
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\EzcDatabase::updateObjectStateLinks
     *
     * @return void
     */
    public function testUpdateObjectStateLinks()
    {
        $gateway = $this->getDatabaseGateway();

        $gateway->updateObjectStateLinks( 1, 2 );

        $query = $this->getDatabaseHandler()->createSelectQuery();
        $query
        ->select( $query->expr->count( '*' ) )
        ->from( 'ezcobj_state_link' )
        ->where( 'contentobject_state_id = 1' );

        $statement = $query->prepare();
        $statement->execute();

        $this->assertEquals(
            0,
            $statement->fetchColumn()
        );

        $query = $this->getDatabaseHandler()->createSelectQuery();
        $query
        ->select( $query->expr->count( '*' ) )
        ->from( 'ezcobj_state_link' )
        ->where( 'contentobject_state_id = 2' );

        $statement = $query->prepare();
        $statement->execute();

        $this->assertEquals(
            // The number of objects in the fixtures
            185,
            $statement->fetchColumn()
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\EzcDatabase::deleteObjectStateLinks
     *
     * @return void
     */
    public function testDeleteObjectStateLinks()
    {
        $gateway = $this->getDatabaseGateway();

        $gateway->deleteObjectStateLinks( 1 );

        $query = $this->getDatabaseHandler()->createSelectQuery();
        $query
            ->select( $query->expr->count( '*' ) )
            ->from( 'ezcobj_state_link' )
            ->where( 'contentobject_state_id = 1' );

        $statement = $query->prepare();
        $statement->execute();

        $this->assertEquals(
            0,
            $statement->fetchColumn()
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\EzcDatabase::insertObjectStateGroup
     *
     * @return void
     */
    public function testInsertObjectStateGroup()
    {
        $gateway = $this->getDatabaseGateway();

        $gateway->insertObjectStateGroup( $this->getObjectStateGroupFixture() );

        $this->assertEquals(
            array(
                array(
                    'ezcobj_state_group_default_language_id' => 4,
                    // The new state group should be added with state group ID = 3
                    'ezcobj_state_group_id' => 3,
                    'ezcobj_state_group_identifier' => 'test_group',
                    'ezcobj_state_group_language_mask' => 5,
                    'ezcobj_state_group_language_description' => 'Test group description',
                    'ezcobj_state_group_language_language_id' => 5,
                    'ezcobj_state_group_language_real_language_id' => 4,
                    'ezcobj_state_group_language_name' => 'Test group'
                )
            ),
            // The new state group should be added with state group ID = 3
            $this->getDatabaseGateway()->loadObjectStateGroupData( 3 )
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\EzcDatabase::updateObjectStateGroup
     *
     * @return void
     */
    public function testUpdateObjectStateGroup()
    {
        $gateway = $this->getDatabaseGateway();

        $groupFixture = $this->getObjectStateGroupFixture();
        $groupFixture->id = 2;

        $gateway->updateObjectStateGroup( $groupFixture );

        $this->assertEquals(
            array(
                array(
                    'ezcobj_state_group_default_language_id' => 4,
                    'ezcobj_state_group_id' => 2,
                    'ezcobj_state_group_identifier' => 'test_group',
                    'ezcobj_state_group_language_mask' => 5,
                    'ezcobj_state_group_language_description' => 'Test group description',
                    'ezcobj_state_group_language_language_id' => 5,
                    'ezcobj_state_group_language_real_language_id' => 4,
                    'ezcobj_state_group_language_name' => 'Test group'
                )
            ),
            $this->getDatabaseGateway()->loadObjectStateGroupData( 2 )
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\EzcDatabase::deleteObjectStateGroup
     *
     * @return void
     */
    public function testDeleteObjectStateGroup()
    {
        $gateway = $this->getDatabaseGateway();

        $gateway->deleteObjectStateGroup( 2 );

        $this->assertEquals(
            array(),
            $this->getDatabaseGateway()->loadObjectStateGroupData( 2 )
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\EzcDatabase::setContentState
     *
     * @return void
     */
    public function testSetContentState()
    {
        $gateway = $this->getDatabaseGateway();

        $gateway->setContentState( 42, 2, 2 );

        $this->assertQueryResult(
            array(
                array(
                    'contentobject_id' => 42,
                    'contentobject_state_id' => 2
                )
            ),
            $this->getDatabaseHandler()->createSelectQuery()
                ->select( 'contentobject_id', 'contentobject_state_id' )
                ->from( 'ezcobj_state_link' )
                ->where( 'contentobject_id = 42' )
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\EzcDatabase::loadObjectStateDataForContent
     *
     * @return void
     */
    public function testLoadObjectStateDataForContent()
    {
        $gateway = $this->getDatabaseGateway();

        $result = $gateway->loadObjectStateDataForContent( 42, 2 );

        $this->assertEquals(
            array(
                array(
                    'ezcobj_state_default_language_id' => 2,
                    'ezcobj_state_group_id' => 2,
                    'ezcobj_state_id' => 1,
                    'ezcobj_state_identifier' => 'not_locked',
                    'ezcobj_state_language_mask' => 3,
                    'ezcobj_state_priority' => 0,
                    'ezcobj_state_language_description' => '',
                    'ezcobj_state_language_language_id' => 3,
                    'ezcobj_state_language_name' => 'Not locked'
                )
            ),
            $result
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\EzcDatabase::getContentCount
     *
     * @return void
     */
    public function testGetContentCount()
    {
        $gateway = $this->getDatabaseGateway();

        $result = $gateway->getContentCount( 1 );

        // 185 is the number of objects in the fixture
        $this->assertEquals( 185, $result );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\EzcDatabase::updateObjectStatePriority
     *
     * @return void
     */
    public function testUpdateObjectStatePriority()
    {
        $gateway = $this->getDatabaseGateway();

        $gateway->updateObjectStatePriority( 1, 10 );

        $objectStateData = $gateway->loadObjectStateData( 1 );

        $this->assertEquals(
            array(
                array(
                    'ezcobj_state_default_language_id' => 2,
                    'ezcobj_state_group_id' => 2,
                    'ezcobj_state_id' => 1,
                    'ezcobj_state_identifier' => 'not_locked',
                    'ezcobj_state_language_mask' => 3,
                    'ezcobj_state_priority' => 10,
                    'ezcobj_state_language_description' => '',
                    'ezcobj_state_language_language_id' => 3,
                    'ezcobj_state_language_name' => 'Not locked'
                )
            ),
            $objectStateData
        );
    }

    /**
     * Returns an object state fixture
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState
     */
    protected function getObjectStateFixture()
    {
        $objectState = new ObjectState();
        $objectState->identifier = 'test_state';
        $objectState->defaultLanguage = 'eng-GB';
        $objectState->languageCodes = array( 'eng-GB' );
        $objectState->name = array( 'eng-GB' => 'Test state' );
        $objectState->description = array( 'eng-GB' => 'Test state description' );

        return $objectState;
    }

    /**
     * Returns an object state group fixture
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState\Group
     */
    protected function getObjectStateGroupFixture()
    {
        $group = new Group();
        $group->identifier = 'test_group';
        $group->defaultLanguage = 'eng-GB';
        $group->languageCodes = array( 'eng-GB' );
        $group->name = array( 'eng-GB' => 'Test group' );
        $group->description = array( 'eng-GB' => 'Test group description' );

        return $group;
    }

    /**
     * Returns a ready to test EzcDatabase gateway
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\ObjectState\Gateway\EzcDatabase
     */
    protected function getDatabaseGateway()
    {
        if ( !isset( $this->databaseGateway ) )
        {
            $this->databaseGateway = new EzcDatabase(
                $this->getDatabaseHandler(),
                $this->getLanguageMaskGenerator()
            );
        }
        return $this->databaseGateway;
    }
}
