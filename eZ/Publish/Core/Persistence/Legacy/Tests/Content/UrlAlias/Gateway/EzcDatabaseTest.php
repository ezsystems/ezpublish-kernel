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
    eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler as LanguageCachingHandler,
    eZ\Publish\Core\Persistence\Legacy\Content\Language\Handler as LanguageHandler,
    eZ\Publish\Core\Persistence\Legacy\Content\Language\Cache as LanguageCache,
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
            'dbHandler',
            $gateway
        );
    }

    /**
     * Test for the loadBasicUrlAliasData() method.
     *
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\EzcDatabase::loadBasicUrlAliasData
     */
    public function testLoadBasicUrlaliasDataNonExistent()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/urlaliases_simple.php' );
        $gateway = $this->getGateway();

        $rows = $gateway->loadBasicUrlAliasData( array( "tri" ), array( "cro-HR" ) );

        self::assertEmpty( $rows );
    }

    protected function getSimpleFixtureResult()
    {
        return array(
            "id" =>  "3",
            "link" =>  "3",
            "is_alias" => "0",
            "alias_redirects" => "1",
            "action" => "eznode:315",
            "is_original" => "1",
            "ezurlalias_ml0_text" => "jedan",
            "ezurlalias_ml1_text" => "dva",
            "lang_mask" => '3',
            "language_codes" => array( "cro-HR" ),
            'parent' => '2',
            'text_md5' => 'c67ed9a09ab136fae610b6a087d82e21',
            'ezurlalias_ml0_action' => 'eznode:314',
            'ezurlalias_ml1_action' => 'eznode:315'
        );
    }

    /**
     * Test for the loadBasicUrlAliasData() method.
     *
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\EzcDatabase::loadBasicUrlAliasData
     */
    public function testLoadBasicUrlaliasData()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/urlaliases_simple.php' );
        $gateway = $this->getGateway();

        $row = $gateway->loadBasicUrlAliasData( array( "jedan", "dva" ), array( "cro-HR" ) );

        self::assertEquals(
            $this->getSimpleFixtureResult(),
            $row
        );
    }

    /**
     * Test for the loadBasicUrlAliasData() method.
     *
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\EzcDatabase::loadBasicUrlAliasData
     */
    public function testLoadBasicUrlaliasDataIsCaseInsensitive()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/urlaliases_simple.php' );
        $gateway = $this->getGateway();

        $row = $gateway->loadBasicUrlAliasData( array( "JEDAN", "DVA" ), array( "cro-HR" ) );

        self::assertEquals(
            $this->getSimpleFixtureResult(),
            $row
        );
    }

    /**
     * Test for the loadBasicUrlAliasData() method.
     *
     * Test with fixture containing language mask with multiple languages.
     *
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\EzcDatabase::loadBasicUrlAliasData
     */
    public function testLoadBasicUrlaliasDataMultipleLanguages()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/urlaliases_multilang.php' );
        $gateway = $this->getGateway();
        $expectedResult = array(
            "id" =>  "3",
            "link" =>  "3",
            "is_alias" => "0",
            "alias_redirects" => "1",
            "action" => "eznode:315",
            "is_original" => "1",
            "ezurlalias_ml0_text" => "jedan",
            "ezurlalias_ml1_text" => "dva",
            "lang_mask" => '6',
            "language_codes" => array( "cro-HR", "eng-GB" ),
            'parent' => '2',
            'text_md5' => 'c67ed9a09ab136fae610b6a087d82e21',
            'ezurlalias_ml0_action' => 'eznode:314',
            'ezurlalias_ml1_action' => 'eznode:315'
        );

        $row1 = $gateway->loadBasicUrlAliasData( array( "jedan", "dva" ), array( "cro-HR" ) );
        $row2 = $gateway->loadBasicUrlAliasData( array( "jedan", "dva" ), array( "eng-GB" ) );
        $row3 = $gateway->loadBasicUrlAliasData( array( "jedan", "dva" ), array( "cro-HR", "eng-GB" ) );

        self::assertEquals( $expectedResult, $row1 );
        self::assertEquals( $expectedResult, $row2 );
        self::assertEquals( $expectedResult, $row3 );
    }

    public function providerForGetPath()
    {
        return array(
            array(
                2,
                array( "cro-HR" ),
                "jedan"
            ),
            array(
                2,
                array( "eng-GB" ),
                "jedan"
            ),
            array(
                2,
                array( "ger-DE" ),
                "jedan"
            ),
            array(
                2,
                array( "kli-KR" ),
                "jedan"
            ),
            array(
                4,
                array( "cro-HR" ),
                "jedan/dva/tri"
            ),
            array(
                4,
                array( "cro-HR", "eng-GB", "ger-DE" ),
                "jedan/dva/tri"
            ),
            array(
                4,
                array( "cro-HR", "ger-DE", "eng-GB" ),
                "jedan/dva/tri"
            ),
            array(
                4,
                array( "eng-GB" ),
                "jedan/two/three"
            ),
            array(
                4,
                array( "eng-GB", "cro-HR", "ger-DE" ),
                "jedan/two/three"
            ),
            array(
                4,
                array( "eng-GB", "ger-DE", "cro-HR" ),
                "jedan/two/three"
            ),
            array(
                4,
                array( "ger-DE", "cro-HR" ),
                "jedan/dva/drei"
            ),
            array(
                4,
                array( "ger-DE", "cro-HR", "eng-GB" ),
                "jedan/dva/drei"
            ),
            array(
                4,
                array( "ger-DE", "eng-GB", "cro-HR" ),
                "jedan/two/drei"
            )
        );
    }

    /**
     * Test for the getPath() method.
     *
     * @dataProvider providerForGetPath
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\EzcDatabase::getPath
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\EzcDatabase::choosePrioritizedRow
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\EzcDatabase::languageScore
     */
    public function testGetPath( $id, array $prioritizedLanguageCodes, $expectedPath )
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/urlaliases_fallback.php' );
        $gateway = $this->getGateway();

        self::assertEquals(
            $expectedPath,
            $gateway->getPath( $id, $prioritizedLanguageCodes )
        );
    }

    public function providerForGetPathMultipleLanguages()
    {
        return array(
            array(
                3,
                array( "eng-GB" ),
                "jedan/dva"
            ),
            array(
                3,
                array( "cro-HR" ),
                "jedan/dva"
            ),
            array(
                3,
                array( "cro-HR", "eng-GB" ),
                "jedan/dva"
            ),
            array(
                3,
                array( "eng-GB", "cro-HR" ),
                "jedan/dva"
            ),
            array(
                4,
                array( "cro-HR" ),
                "jedan/dva/tri"
            ),
            array(
                4,
                array( "eng-GB" ),
                "jedan/dva/three"
            ),
            array(
                4,
                array( "eng-GB", "cro-HR" ),
                "jedan/dva/three"
            ),
            array(
                4,
                array( "cro-HR", "eng-GB" ),
                "jedan/dva/tri"
            ),
        );
    }

    /**
     * Test for the getPath() method.
     *
     * Test with fixture containing language mask with multiple languages.
     *
     * @dataProvider providerForGetPathMultipleLanguages
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\EzcDatabase::getPath
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\EzcDatabase::choosePrioritizedRow
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\EzcDatabase::languageScore
     */
    public function testGetPathMultipleLanguages( $id, array $prioritizedLanguageCodes, $expectedPath )
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/urlaliases_multilang.php' );
        $gateway = $this->getGateway();

        self::assertEquals(
            $expectedPath,
            $gateway->getPath( $id, $prioritizedLanguageCodes )
        );
    }

    /**
     * Test for the getPath() method.
     *
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway\EzcDatabase::getPath
     */
    public function testGetPathWithFallbackToArbitraryLanguage()
    {
        $this->insertDatabaseFixture( __DIR__ . '/_fixtures/urlaliases_fallback.php' );
        $gateway = $this->getGateway();

        $klingonFriendlyPath = $gateway->getPath( 3, array( "kli-KR" ) );
        $hasMatched = false;

        switch ( $klingonFriendlyPath )
        {
            case "jedan/dva";
                $hasMatched = true;
                break;
            case "jedan/two";
                $hasMatched = true;
                break;
        }

        if ( !$hasMatched )
        {
            self::fail( "Fallback to arbitrary language not matched" );
        }
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
                $languageHandler,
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
