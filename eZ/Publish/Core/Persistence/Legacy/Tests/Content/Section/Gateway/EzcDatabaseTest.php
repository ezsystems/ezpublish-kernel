<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\Section\Gateway\EzcDatabaseTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\Section\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway\EzcDatabase;

/**
 * Test case for eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway\EzcDatabase.
 */
class EzcDatabaseTest extends TestCase
{
    /**
     * Database gateway to test.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway\EzcDatabase
     */
    protected $databaseGateway;

    /**
     * Inserts DB fixture.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->insertDatabaseFixture(
            __DIR__ . '/../../_fixtures/sections.php'
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway\EzcDatabase::__construct
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
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway\EzcDatabase::insertSection
     *
     * @return void
     */
    public function testInsertSection()
    {
        $gateway = $this->getDatabaseGateway();

        $gateway->insertSection( 'New Section', 'new_section' );
        $query = $this->getDatabaseHandler()->createSelectQuery();

        $this->assertQueryResult(
            array(
                array(
                    'id' => '7',
                    'identifier' => 'new_section',
                    'name' => 'New Section',
                    'locale' => '',
                )
            ),
            $query
                ->select( 'id', 'identifier', 'name', 'locale' )
                ->from( 'ezsection' )
                ->where( $query->expr->eq( 'identifier', $query->bindValue( "new_section" ) ) )
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway\EzcDatabase::updateSection
     *
     * @return void
     */
    public function testUpdateSection()
    {
        $gateway = $this->getDatabaseGateway();

        $gateway->updateSection( 2, 'New Section', 'new_section' );

        $this->assertQueryResult(
            array(
                array(
                    'id' => '2',
                    'identifier' => 'new_section',
                    'name' => 'New Section',
                    'locale' => '',
                )
            ),
            $this->getDatabaseHandler()->createSelectQuery()
                ->select( 'id', 'identifier', 'name', 'locale' )
                ->from( 'ezsection' )
                ->where( 'id=2' )
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway\EzcDatabase::loadSectionData
     *
     * @return void
     */
    public function testLoadSectionData()
    {
        $gateway = $this->getDatabaseGateway();

        $result = $gateway->loadSectionData( 2 );

        $this->assertEquals(
            array(
                array(
                    'id' => '2',
                    'identifier' => 'users',
                    'name' => 'Users',
                )
            ),
            $result
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway\EzcDatabase::loadAllSectionData
     *
     * @return void
     */
    public function testLoadAllSectionData()
    {
        $gateway = $this->getDatabaseGateway();

        $result = $gateway->loadAllSectionData();

        $expected = array(
            array(
                'id' => '1',
                'identifier' => 'standard',
                'name' => 'Standard',
            ),

            array(
                'id' => '2',
                'identifier' => 'users',
                'name' => 'Users',
            ),

            array(
                'id' => '3',
                'identifier' => 'media',
                'name' => 'Media',
            ),

            array(
                'id' => '4',
                'identifier' => 'setup',
                'name' => 'Setup',
            ),

            array(
                'id' => '5',
                'identifier' => 'design',
                'name' => 'Design',
            ),

            array(
                'id' => '6',
                'identifier' => '',
                'name' => 'Restricted',
            )
        );
        $this->assertEquals(
            $expected,
            $result
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway\EzcDatabase::loadSectionDataByIdentifier
     *
     * @return void
     */
    public function testLoadSectionDataByIdentifier()
    {
        $gateway = $this->getDatabaseGateway();

        $result = $gateway->loadSectionDataByIdentifier( 'users' );

        $this->assertEquals(
            array(
                array(
                    'id' => '2',
                    'identifier' => 'users',
                    'name' => 'Users',
                )
            ),
            $result
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway\EzcDatabase::countContentObjectsInSection
     *
     * @return void
     */
    public function testCountContentObjectsInSection()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../../_fixtures/contentobjects.php'
        );

        $gateway = $this->getDatabaseGateway();

        $result = $gateway->countContentObjectsInSection( 2 );

        $this->assertSame(
            7,
            $result
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway\EzcDatabase::deleteSection
     *
     * @return void
     */
    public function testDeleteSection()
    {
        $gateway = $this->getDatabaseGateway();

        $result = $gateway->deleteSection( 2 );

        $this->assertQueryResult(
            array(
                array(
                    'count' => '5'
                )
            ),
            $this->getDatabaseHandler()->createSelectQuery()
                ->select( 'COUNT( * ) AS count' )
                ->from( 'ezsection' )
        );

        $this->assertQueryResult(
            array(
                array(
                    'count' => '0'
                )
            ),
            $this->getDatabaseHandler()->createSelectQuery()
                ->select( 'COUNT( * ) AS count' )
                ->from( 'ezsection' )
                ->where( 'id=2' )
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway\EzcDatabase::assignSectionToContent
     * @depends testCountContentObjectsInSection
     */
    public function testAssignSectionToContent()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../../_fixtures/contentobjects.php'
        );

        $gateway = $this->getDatabaseGateway();

        $beforeCount = $gateway->countContentObjectsInSection( 4 );

        $result = $gateway->assignSectionToContent( 4, 10 );

        $this->assertSame(
            $beforeCount + 1,
            $gateway->countContentObjectsInSection( 4 )
        );
    }

    /**
     * Returns a ready to test EzcDatabase gateway
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Section\Gateway\EzcDatabase
     */
    protected function getDatabaseGateway()
    {
        if ( !isset( $this->databaseGateway ) )
        {
            $this->databaseGateway = new EzcDatabase(
                $this->getDatabaseHandler()
            );
        }
        return $this->databaseGateway;
    }
}
