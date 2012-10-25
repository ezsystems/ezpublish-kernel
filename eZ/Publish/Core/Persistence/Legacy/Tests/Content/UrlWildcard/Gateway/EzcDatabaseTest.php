<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\UrlWildcard\Gateway\EzcDatabaseTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\UrlWildcard\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase,
    eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway\EzcDatabase,
    eZ\Publish\SPI\Persistence\Content\UrlWildcard;

/**
 * Test case for eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway\EzcDatabase.
 */
class EzcDatabaseTest extends TestCase
{
    /**
     * Database gateway to test.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway\EzcDatabase
     */
    protected $gateway;

    protected $fixtureData = array(
        0 => array(
            'id' => '1',
            'source_url' => 'developer/*',
            'destination_url' => 'dev/{1}',
            'type' => '2',
        ),
        1 => array (
            'id' => '2',
            'source_url' => 'repository/*',
            'destination_url' => 'repo/{1}',
            'type' => '2',
        ),
        2 => array(
            'id' => '3',
            'source_url' => 'information/*',
            'destination_url' => 'info/{1}',
            'type' => '2',
        ),
    );

    /**
     * Test for the __construct() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway\EzcDatabase::__construct
     */
    public function testConstructor()
    {
        $dbHandler = $this->getDatabaseHandler();
        $gateway = $this->getGateway();

        $this->assertAttributeSame(
            $dbHandler,
            "dbHandler",
            $gateway
        );
    }

    /**
     * Test for the loadUrlWildcardData() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway\EzcDatabase::loadUrlWildcardData
     */
    public function testLoadUrlWildcardData()
    {
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlwildcards.php" );
        $gateway = $this->getGateway();

        $row = $gateway->loadUrlWildcardData( 1 );

        self::assertEquals(
            $this->fixtureData[0],
            $row
        );
    }

    /**
     * Test for the loadUrlWildcardsData() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway\EzcDatabase::loadUrlWildcardsData
     */
    public function testLoadUrlWildcardsData()
    {
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlwildcards.php" );
        $gateway = $this->getGateway();

        $rows = $gateway->loadUrlWildcardsData();

        self::assertEquals(
            $this->fixtureData,
            $rows
        );
    }

    /**
     * Test for the loadUrlWildcardsData() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway\EzcDatabase::loadUrlWildcardsData
     */
    public function testLoadUrlWildcardsDataWithOffset()
    {
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlwildcards.php" );
        $gateway = $this->getGateway();

        $row = $gateway->loadUrlWildcardsData( 1 );

        self::assertEquals(
            array(
                0 => $this->fixtureData[1],
                1 => $this->fixtureData[2],
            ),
            $row
        );
    }

    /**
     * Test for the loadUrlWildcardsData() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway\EzcDatabase::loadUrlWildcardsData
     */
    public function testLoadUrlWildcardsDataWithOffsetAndLimit()
    {
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlwildcards.php" );
        $gateway = $this->getGateway();

        $row = $gateway->loadUrlWildcardsData( 1, 1 );

        self::assertEquals(
            array(
                0 => $this->fixtureData[1],
            ),
            $row
        );
    }

    /**
     * Test for the insertUrlWildcard() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway\EzcDatabase::insertUrlWildcard
     * @depends testLoadUrlWildcardData
     */
    public function testInsertUrlWildcard()
    {
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlwildcards.php" );
        $gateway = $this->getGateway();

        $id = $gateway->insertUrlWildcard(
            new UrlWildcard(
                array(
                    "sourceUrl" => "/contact-information/*",
                    "destinationUrl" => "/contact/{1}",
                    "forward" => true,
                )
            )
        );

        self::assertEquals(
            array(
                "id" => $id,
                "source_url" => "contact-information/*",
                "destination_url" => "contact/{1}",
                "type" => "1",
            ),
            $gateway->loadUrlWildcardData( $id )
        );
    }

    /**
     * Test for the deleteUrlWildcard() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway\EzcDatabase::deleteUrlWildcard
     * @depends testLoadUrlWildcardData
     */
    public function testDeleteUrlWildcard()
    {
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlwildcards.php" );
        $gateway = $this->getGateway();

        $gateway->deleteUrlWildcard( 1 );

        self::assertEmpty( $gateway->loadUrlWildcardData( 1 ) );
    }

    /**
     * Returns the EzcDatabase gateway to test
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\UrlWildcard\Gateway\EzcDatabase
     */
    protected function getGateway()
    {
        if ( !isset( $this->gateway ) )
        {
            $this->gateway = new EzcDatabase( $this->getDatabaseHandler() );
        }
        return $this->gateway;
    }

    /**
     * Returns the test suite with all tests declared in this class.
     *
     * @return \PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        return new \PHPUnit_Framework_TestSuite( __CLASS__ );
    }
}
