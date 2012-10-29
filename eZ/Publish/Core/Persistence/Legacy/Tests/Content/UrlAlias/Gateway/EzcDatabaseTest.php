<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\UrlAlias\Gateway\EzcDatabaseTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\UrlAlias\Gateway;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase,
    eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway\EzcDatabase,
    eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator as LanguageMaskGenerator,
    eZ\Publish\Core\Persistence\Legacy\Content\Language\Handler as LanguageHandler,
    eZ\Publish\Core\Persistence\Legacy\Content\Language\Mapper as LanguageMapper,
    eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway\EzcDatabase as LanguageGateway;

/**
 * Test case for eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway\EzcDatabase.
 *
 * @group urlalias-gateway
 */
class EzcDatabaseTest extends TestCase
{
    /**
     * Database gateway to test.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway
     */
    protected $gateway;

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway\EzcDatabase::__construct
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
     * Test for the loadUrlAliasData() method.
     *
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\EzcDatabase::loadUrlAliasData
     */
    public function testLoadUrlaliasDataNonExistent()
    {
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_simple.php" );
        $gateway = $this->getGateway();

        $rows = $gateway->loadUrlAliasData( array( md5( "tri" ) ) );

        self::assertEmpty( $rows );
    }

    /**
     * Test for the loadUrlAliasData() method.
     *
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\EzcDatabase::loadUrlAliasData
     */
    public function testLoadUrlaliasData()
    {
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_simple.php" );
        $gateway = $this->getGateway();

        $row = $gateway->loadUrlAliasData( array( md5( "jedan" ), md5( "dva" ) ) );

        self::assertEquals(
            array(
                "ezurlalias_ml0_id" => "2",
                "ezurlalias_ml0_link" => "2",
                "ezurlalias_ml0_is_alias" => "0",
                "ezurlalias_ml0_alias_redirects" => "1",
                "ezurlalias_ml0_is_original" => "1",
                "ezurlalias_ml0_action" => "eznode:314",
                "ezurlalias_ml0_action_type" => "eznode",
                "ezurlalias_ml0_lang_mask" => "2",
                "ezurlalias_ml0_text" => "jedan",
                "ezurlalias_ml0_parent" => "0",
                "ezurlalias_ml0_text_md5" => "6896260129051a949051c3847c34466f",
                "id" => "3",
                "link" => "3",
                "is_alias" => "0",
                "alias_redirects" => "1",
                "is_original" => "1",
                "action" => "eznode:315",
                "action_type" => "eznode",
                "lang_mask" => "3",
                "text" => "dva",
                "parent" => "2",
                "text_md5" => "c67ed9a09ab136fae610b6a087d82e21",
            ),
            $row
        );
    }

    /**
     * Test for the loadUrlAliasData() method.
     *
     * Test with fixture containing language mask with multiple languages.
     *
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\EzcDatabase::loadUrlAliasData
     */
    public function testLoadUrlaliasDataMultipleLanguages()
    {
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_multilang.php" );
        $gateway = $this->getGateway();

        $row = $gateway->loadUrlAliasData( array( md5( "jedan" ), md5( "dva" ) ) );

        self::assertEquals(
            array(
                "ezurlalias_ml0_id" => "2",
                "ezurlalias_ml0_link" => "2",
                "ezurlalias_ml0_is_alias" => "0",
                "ezurlalias_ml0_alias_redirects" => "1",
                "ezurlalias_ml0_is_original" => "1",
                "ezurlalias_ml0_action" => "eznode:314",
                "ezurlalias_ml0_action_type" => "eznode",
                "ezurlalias_ml0_lang_mask" => "3",
                "ezurlalias_ml0_text" => "jedan",
                "ezurlalias_ml0_parent" => "0",
                "ezurlalias_ml0_text_md5" => "6896260129051a949051c3847c34466f",
                "id" => "3",
                "link" => "3",
                "is_alias" => "0",
                "alias_redirects" => "1",
                "is_original" => "1",
                "action" => "eznode:315",
                "action_type" => "eznode",
                "lang_mask" => "6",
                "text" => "dva",
                "parent" => "2",
                "text_md5" => "c67ed9a09ab136fae610b6a087d82e21",
            ),
            $row
        );
    }

    /**
     * @return array
     */
    public function providerForTestLoadPathData()
    {
        return array(
            array(
                2,
                array(
                    array(
                        array( "parent" => "0", "lang_mask" => "3", "text" => "jedan" ),
                    ),
                )
            ),
            array(
                3,
                array(
                    array(
                        array( "parent" => "0", "lang_mask" => "3", "text" => "jedan" ),
                    ),
                    array(
                        array( "parent" => "2", "lang_mask" => "5", "text" => "two" ),
                        array( "parent" => "2", "lang_mask" => "3", "text" => "dva" ),
                    ),
                )
            ),
            array(
                4,
                array(
                    array(
                        array( "parent" => "0", "lang_mask" => "3", "text" => "jedan" ),
                    ),
                    array(
                        array( "parent" => "2", "lang_mask" => "5", "text" => "two" ),
                        array( "parent" => "2", "lang_mask" => "3", "text" => "dva" ),
                    ),
                    array(
                        array( "parent" => "3", "lang_mask" => "9", "text" => "drei" ),
                        array( "parent" => "3", "lang_mask" => "5", "text" => "three" ),
                        array( "parent" => "3", "lang_mask" => "3", "text" => "tri" ),
                    ),
                )
            ),
        );
    }

    /**
     * Test for the loadPathData() method.
     *
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\EzcDatabase::loadPathData
     * @dataProvider providerForTestLoadPathData
     */
    public function testLoadPathData( $id, $pathData )
    {
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_fallback.php" );
        $gateway = $this->getGateway();

        $loadedPathData = $gateway->loadPathData( $id );

        self::assertEquals(
            $pathData,
            $loadedPathData
        );
    }

    /**
     * @return array
     */
    public function providerForTestLoadPathDataMultipleLanguages()
    {
        return array(
            array(
                2,
                array(
                    array(
                        array( "parent" => "0", "lang_mask" => "3", "text" => "jedan" ),
                    ),
                )
            ),
            array(
                3,
                array(
                    array(
                        array( "parent" => "0", "lang_mask" => "3", "text" => "jedan" ),
                    ),
                    array(
                        array( "parent" => "2", "lang_mask" => "6", "text" => "dva" ),
                    ),
                )
            ),
            array(
                4,
                array(
                    array(
                        array( "parent" => "0", "lang_mask" => "3", "text" => "jedan" ),
                    ),
                    array(
                        array( "parent" => "2", "lang_mask" => "6", "text" => "dva" ),
                    ),
                    array(
                        array( "parent" => "3", "lang_mask" => "4", "text" => "three" ),
                        array( "parent" => "3", "lang_mask" => "2", "text" => "tri" ),
                    ),
                )
            ),
        );
    }

    /**
     * Test for the loadPathData() method.
     *
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\EzcDatabase::loadPathData
     * @dataProvider providerForTestLoadPathDataMultipleLanguages
     */
    public function testLoadPathDataMultipleLanguages( $id, $pathData )
    {
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_multilang.php" );
        $gateway = $this->getGateway();

        $loadedPathData = $gateway->loadPathData( $id );

        self::assertEquals(
            $pathData,
            $loadedPathData
        );
    }

    /**
     * @return array
     */
    public function providerForTestDowngradeMarksAsHistory()
    {
        return array(
            array(
                "action" => "eznode:314",
                "languageId" => 2,
                "parentId" => 0,
                "textMD5" => "6896260129051a949051c3847c34466f"
            ),
            array(
                "action" => "eznode:315",
                "languageId" => 2,
                "parentId" => 0,
                "textMD5" => "c67ed9a09ab136fae610b6a087d82e21"
            ),
        );
    }

    /**
     * Test for the downgrade() method.
     *
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\EzcDatabase::downgrade
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\EzcDatabase::markAsHistory
     * @dataProvider providerForTestDowngradeMarksAsHistory
     */
    public function testDowngradeMarksAsHistory( $action, $languageId, $parentId, $textMD5 )
    {
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_downgrade.php" );
        $gateway = $this->getGateway();

        $loadedRow = $gateway->loadRow( $parentId, $textMD5 );

        $gateway->cleanupAfterPublish( $action, $languageId, 42, $parentId, "jabberwocky" );

        $reloadedRow = $gateway->loadRow( $parentId, $textMD5 );
        $loadedRow["is_original"] = "0";
        $loadedRow["link"] = 42;
        $loadedRow["id"] = 6;

        self::assertEquals( $reloadedRow, $loadedRow );
    }

    /**
     * @return array
     */
    public function providerForTestDowngradeRemovesLanguage()
    {
        return array(
            array(
                "action" => "eznode:316",
                "languageId" => 2,
                "parentId" => 0,
                "textMD5" => "d2cfe69af2d64330670e08efb2c86df7"
            ),
            array(
                "action" => "eznode:317",
                "languageId" => 2,
                "parentId" => 0,
                "textMD5" => "538dca05643d220317ad233cd7be7a0a"
            ),
        );
    }

    /**
     * Test for the downgrade() method.
     *
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\EzcDatabase::downgrade
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\EzcDatabase::removeLanguage
     * @dataProvider providerForTestDowngradeRemovesLanguage
     */
    public function testDowngradeRemovesLanguage( $action, $languageId, $parentId, $textMD5 )
    {
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_downgrade.php" );
        $gateway = $this->getGateway();

        $loadedRow = $gateway->loadRow( $parentId, $textMD5 );

        $gateway->cleanupAfterPublish( $action, $languageId, 42, $parentId, "jabberwocky" );

        $reloadedRow = $gateway->loadRow( $parentId, $textMD5 );
        $loadedRow["lang_mask"] = $loadedRow["lang_mask"] & ~$languageId;

        self::assertEquals( $reloadedRow, $loadedRow );
    }

    /**
     * Test for the reparent() method.
     *
     * @todo document
     *
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\EzcDatabase::reparent
     */
    public function testReparent()
    {
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_simple.php" );
        $gateway = $this->getGateway();

        $gateway->reparent( 2, 42 );

        self::assertEquals(
            array(
                "action" => "eznode:315",
                "action_type" => "eznode",
                "alias_redirects" => "1",
                "id" => "3",
                "is_alias" => "0",
                "is_original" => "1",
                "lang_mask" => "3",
                "link" => "3",
                "parent" => "42",
                "text" => "dva",
                "text_md5" => "c67ed9a09ab136fae610b6a087d82e21"
            ),
            $gateway->loadRow( 42, "c67ed9a09ab136fae610b6a087d82e21" )
        );
    }

    /**
     * Test for the removeByAction() method.
     *
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\EzcDatabase::removeByAction
     */
    public function testRemoveByAction()
    {
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_reparent.php" );
        $gateway = $this->getGateway();

        $gateway->removeByAction( "eznode:315" );

        self::assertEquals(
            array(
                "action" => "nop:",
                "action_type" => "nop",
                "alias_redirects" => "1",
                "id" => "3",
                "is_alias" => "0",
                "is_original" => "0",
                "lang_mask" => "1",
                "link" => "3",
                "parent" => "2",
                "text" => "new-location",
                "text_md5" => "1cdf796099b4596aed1c1c86c102526f"
            ),
            $gateway->loadRow( 2, "1cdf796099b4596aed1c1c86c102526f" )
        );
        self::assertEquals(
            array(
                "action" => "nop:",
                "action_type" => "nop",
                "alias_redirects" => "1",
                "id" => "6",
                "is_alias" => "0",
                "is_original" => "0",
                "lang_mask" => "1",
                "link" => "6",
                "parent" => "0",
                "text" => "old-location-historized",
                "text_md5" => "e504bfae32f8c3ecd2922bcd1b9a8b6a"
            ),
            $gateway->loadRow( 0, "e504bfae32f8c3ecd2922bcd1b9a8b6a" )
        );
    }








    /**
     * Test for the getNewId() method.
     *
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\EzcDatabase::getNextId
     */
    public function testGetNextId()
    {
        $gateway = $this->getGateway();

        $refObject = new \ReflectionObject( $gateway );
        $refMethod = $refObject->getMethod( "getNextId" );
        $refMethod->setAccessible( true );

        self::assertEquals( 1, $refMethod->invoke( $gateway ) );
        self::assertEquals( 2, $refMethod->invoke( $gateway ) );
    }

    /**
     * Returns the EzcDatabase gateway to test
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway\EzcDatabase
     */
    protected function getGateway()
    {
        if ( !isset( $this->gateway ) )
        {
            $languageHandler = new LanguageHandler(
                new LanguageGateway(
                    $this->getDatabaseHandler()
                ),
                new LanguageMapper()
            );
            $this->gateway = new EzcDatabase(
                $this->getDatabaseHandler(),
                new LanguageMaskGenerator( $languageHandler )
            );
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
