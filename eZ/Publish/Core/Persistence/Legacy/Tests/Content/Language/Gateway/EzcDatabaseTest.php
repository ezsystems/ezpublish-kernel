<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\Language\Gateway\EzcDatabaseTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\Language\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway\EzcDatabase;
use eZ\Publish\SPI\Persistence\Content\Language;

/**
 * Test case for eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway\EzcDatabase.
 */
class EzcDatabaseTest extends TestCase
{
    /**
     * Database gateway to test.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway\EzcDatabase
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
            __DIR__ . '/../../_fixtures/languages.php'
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway\EzcDatabase::__construct
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
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway\EzcDatabase::insertLanguage
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway\EzcDatabase::setCommonLanguageColumns
     */
    public function testInsertLanguage()
    {
        $gateway = $this->getDatabaseGateway();

        $gateway->insertLanguage( $this->getLanguageFixture() );

        $this->assertQueryResult(
            array(
                array(
                    'id' => '8',
                    'locale' => 'de-DE',
                    'name' => 'Deutsch (Deutschland)',
                    'disabled' => '0',
                )
            ),
            $this->getDatabaseHandler()->createSelectQuery()
                ->select( 'id', 'locale', 'name', 'disabled' )
                ->from( 'ezcontent_language' )
                ->where( 'id=8' )
        );
    }

    /**
     * Returns a Language fixture
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Language
     */
    protected function getLanguageFixture()
    {
        $language = new Language();

        $language->languageCode = 'de-DE';
        $language->name = 'Deutsch (Deutschland)';
        $language->isEnabled = true;

        return $language;
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway\EzcDatabase::updateLanguage
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway\EzcDatabase::setCommonLanguageColumns
     */
    public function testUpdateLanguage()
    {
        $gateway = $this->getDatabaseGateway();

        $language = $this->getLanguageFixture();
        $language->id = 2;

        $gateway->updateLanguage( $language );

        $this->assertQueryResult(
            array(
                array(
                    'id' => '2',
                    'locale' => 'de-DE',
                    'name' => 'Deutsch (Deutschland)',
                    'disabled' => '0',
                )
            ),
            $this->getDatabaseHandler()->createSelectQuery()
                ->select( 'id', 'locale', 'name', 'disabled' )
                ->from( 'ezcontent_language' )
                ->where( 'id=2' )
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway\EzcDatabase::loadLanguageData
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway\EzcDatabase::createFindQuery
     */
    public function testLoadLanguageData()
    {
        $gateway = $this->getDatabaseGateway();

        $result = $gateway->loadLanguageData( 2 );

        $this->assertEquals(
            array(
                array(
                    'id' => '2',
                    'locale' => 'eng-US',
                    'name' => 'English (American)',
                    'disabled' => '0',
                )
            ),
            $result
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway\EzcDatabase::loadAllLanguagesData
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway\EzcDatabase::createFindQuery
     */
    public function testLoadAllLanguagesData()
    {
        $gateway = $this->getDatabaseGateway();

        $result = $gateway->loadAllLanguagesData();

        $this->assertEquals(
            array(
                array(
                    'id' => '2',
                    'locale' => 'eng-US',
                    'name' => 'English (American)',
                    'disabled' => '0',
                ),
                array(
                    'id' => '4',
                    'locale' => 'eng-GB',
                    'name' => 'English (United Kingdom)',
                    'disabled' => '0',
                )
            ),
            $result
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway\EzcDatabase::deleteLanguage
     *
     * @return void
     */
    public function testDeleteLanguage()
    {
        $gateway = $this->getDatabaseGateway();

        $result = $gateway->deleteLanguage( 2 );

        $this->assertQueryResult(
            array(
                array(
                    'count' => '1'
                )
            ),
            $this->getDatabaseHandler()->createSelectQuery()
                ->select( 'COUNT( * ) AS count' )
                ->from( 'ezcontent_language' )
        );

        $this->assertQueryResult(
            array(
                array(
                    'count' => '0'
                )
            ),
            $this->getDatabaseHandler()->createSelectQuery()
                ->select( 'COUNT( * ) AS count' )
                ->from( 'ezcontent_language' )
                ->where( 'id=2' )
        );
    }

    /**
     * Returns a ready to test EzcDatabase gateway
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway\EzcDatabase
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
