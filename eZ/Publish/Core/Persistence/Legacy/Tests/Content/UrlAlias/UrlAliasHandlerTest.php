<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\UrlAliasHandlerTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase,
    eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler,
    eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Mapper,
    eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway\EzcDatabase,
    eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\EzcDatabase as LocationGateway,
    eZ\Publish\Core\Persistence\Legacy\Content\Language\Handler as LanguageHandler,
    eZ\Publish\Core\Persistence\Legacy\Content\Language\CachingHandler as LanguageCachingHandler,
    eZ\Publish\Core\Persistence\Legacy\Content\Language\Cache as LanguageCache,
    eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway\EzcDatabase as LanguageGateway,
    eZ\Publish\Core\Persistence\Legacy\Content\Language\Mapper as LanguageMapper,
    eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator as LanguageMaskGenerator,
    eZ\Publish\SPI\Persistence\Content\UrlAlias,
    eZ\Publish\Core\Base\Exceptions\NotFoundException;

/**
 * Test case for UrlAliasHandler.
 *
 * @group urlalias-handler
 */
class UrlAliasHandlerTest extends TestCase
{
    /**
     * Test for the lookup() method.
     *
     * Simple lookup case.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::lookup
     * @group location
     * @group virtual
     * @group resource
     * @group case-correction
     * @group multiple-languages
     */
    public function testLookup()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_location.php" );

        $urlAlias = $handler->lookup( "jedan", array( "cro-HR" ) );
        self::assertInstanceOf( "eZ\\Publish\\SPI\\Persistence\\Content\\UrlAlias", $urlAlias );
    }

    /**
     * Test for the lookup() method.
     *
     * Trying to lookup non existent URL alias throws NotFoundException.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::lookup
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @group location
     * @group virtual
     * @group resource
     */
    public function testLookupThrowsNotFoundException()
    {
        $handler = $this->getHandler();
        $handler->lookup( "wooden/iron", array( "cro-HR" ) );
    }

    public function providerForTestLookupLocationUrlAliasThrowsNotFoundException()
    {
        return array(
            array( "jedan/two", array( "cro-HR" ) ),
            array( "jedan/dva", array( "eng-GB" ) ),
            array( "jedan/dva", array( "ger-DE" ) ),
            array( "jedan/two", array( "ger-DE" ) ),
            array( "jedan/two", array( "cro-HR", "ger-DE" ) ),
            array( "jedan/dva", array( "eng-GB", "ger-DE" ) ),
            array( "jedan/dva/three", array( "cro-HR" ) ),
            array( "jedan/dva/drei", array( "cro-HR" ) ),
            array( "jedan/two/tri", array( "cro-HR" ) ),
            array( "jedan/two/three", array( "cro-HR" ) ),
            array( "jedan/two/drei", array( "cro-HR" ) ),
            array( "jedan/dva/tri", array( "eng-GB" ) ),
            array( "jedan/dva/three", array( "eng-GB" ) ),
            array( "jedan/dva/drei", array( "eng-GB" ) ),
            array( "jedan/two/tri", array( "eng-GB" ) ),
            array( "jedan/two/drei", array( "eng-GB" ) ),
            array( "jedan/dva/tri", array( "ger-DE" ) ),
            array( "jedan/dva/three", array( "ger-DE" ) ),
            array( "jedan/dva/drei", array( "ger-DE" ) ),
            array( "jedan/two/tri", array( "ger-DE" ) ),
            array( "jedan/two/three", array( "ger-DE" ) ),
            array( "jedan/two/drei", array( "ger-DE" ) ),
            array( "jedan/dva/drei", array( "cro-HR", "eng-GB" ) ),
            array( "jedan/two/drei", array( "cro-HR", "eng-GB" ) ),
            array( "jedan/dva/three", array( "cro-HR", "ger-DE" ) ),
            array( "jedan/two/tri", array( "cro-HR", "ger-DE" ) ),
            array( "jedan/two/three", array( "cro-HR", "ger-DE" ) ),
            array( "jedan/two/drei", array( "cro-HR", "ger-DE" ) ),
            array( "jedan/dva/tri", array( "eng-GB", "ger-DE" ) ),
            array( "jedan/dva/three", array( "eng-GB", "ger-DE" ) ),
            array( "jedan/dva/drei", array( "eng-GB", "ger-DE" ) ),
            array( "jedan/two/tri", array( "eng-GB", "ger-DE" ) ),
        );
    }

    /**
     * Test for the lookup() method.
     *
     * Throws NotFoundException because parts of URL alias are not always available or not in list
     * of prioritized languages.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::lookup
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @dataProvider providerForTestLookupLocationUrlAliasThrowsNotFoundException
     * @depends testLookupThrowsNotFoundException
     * @group location
     */
    public function testLookupLocationUrlAliasThrowsNotFoundException( $url, array $prioritizedLanguageCodes )
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_location.php" );

        $handler->lookup( $url, $prioritizedLanguageCodes );
    }

    public function providerForTestLookupLocationUrlAliasFound()
    {
        return array(
            array(
                "jedan",
                array( "cro-HR" ),
                array( "cro-HR" ),
                true,
                314,
                "0-6896260129051a949051c3847c34466f"
            ),
            array(
                "jedan",
                array( "eng-GB" ),
                array( "eng-GB" ),
                true,
                314,
                "0-6896260129051a949051c3847c34466f"
            ),
            array(
                "jedan",
                array( "ger-DE" ),
                array( "ger-DE" ),
                true,
                314,
                "0-6896260129051a949051c3847c34466f"
            ),
            array(
                "jedan/dva",
                array( "cro-HR" ),
                array( "cro-HR" ),
                false,
                315,
                "2-c67ed9a09ab136fae610b6a087d82e21"
            ),
            array(
                "jedan/two",
                array( "eng-GB" ),
                array( "eng-GB" ),
                false,
                315,
                "2-b8a9f715dbb64fd5c56e7783c6820a61"
            ),
            array(
                "jedan/dva",
                array( "eng-GB", "cro-HR" ),
                array( "eng-GB", "cro-HR" ),
                false,
                315,
                "2-c67ed9a09ab136fae610b6a087d82e21"
            ),
            array(
                "jedan/two",
                array( "eng-GB", "cro-HR" ),
                array( "eng-GB", "cro-HR" ),
                false,
                315,
                "2-b8a9f715dbb64fd5c56e7783c6820a61"
            ),
            array(
                "jedan/dva",
                array( "cro-HR", "ger-DE" ),
                array( "cro-HR" ),
                false,
                315,
                "2-c67ed9a09ab136fae610b6a087d82e21"
            ),
            array(
                "jedan/two",
                array( "eng-GB", "ger-DE" ),
                array( "eng-GB" ),
                false,
                315,
                "2-b8a9f715dbb64fd5c56e7783c6820a61"
            ),
            array(
                "jedan/dva/tri",
                array( "cro-HR" ),
                array( "cro-HR" ),
                false,
                316,
                "3-d2cfe69af2d64330670e08efb2c86df7"
            ),
            array(
                "jedan/two/three",
                array( "eng-GB" ),
                array( "eng-GB" ),
                false,
                316,
                "3-35d6d33467aae9a2e3dccb4b6b027878"
            ),
            array(
                "jedan/dva/tri",
                array( "cro-HR", "eng-GB" ),
                array( "cro-HR", "eng-GB" ),
                false,
                316,
                "3-d2cfe69af2d64330670e08efb2c86df7"
            ),
            array(
                "jedan/dva/three",
                array( "cro-HR", "eng-GB" ),
                array( "cro-HR", "eng-GB" ),
                false,
                316,
                "3-35d6d33467aae9a2e3dccb4b6b027878"
            ),
            array(
                "jedan/two/tri",
                array( "cro-HR", "eng-GB" ),
                array( "cro-HR", "eng-GB" ),
                false,
                316,
                "3-d2cfe69af2d64330670e08efb2c86df7"
            ),
            array(
                "jedan/two/three",
                array( "cro-HR", "eng-GB" ),
                array( "cro-HR", "eng-GB" ),
                false,
                316,
                "3-35d6d33467aae9a2e3dccb4b6b027878"
            ),
            array(
                "jedan/dva/tri",
                array( "cro-HR", "ger-DE" ),
                array( "cro-HR", "ger-DE" ),
                false,
                316,
                "3-d2cfe69af2d64330670e08efb2c86df7"
            ),
            array(
                "jedan/dva/drei",
                array( "cro-HR", "ger-DE" ),
                array( "cro-HR", "ger-DE" ),
                false,
                316,
                "3-1d8d2fd0a99802b89eb356a86e029d25"
            ),
            array(
                "jedan/two/three",
                array( "eng-GB", "ger-DE" ),
                array( "eng-GB", "ger-DE" ),
                false,
                316,
                "3-35d6d33467aae9a2e3dccb4b6b027878"
            ),
            array(
                "jedan/two/drei",
                array( "eng-GB", "ger-DE" ),
                array( "eng-GB", "ger-DE" ),
                false,
                316,
                "3-1d8d2fd0a99802b89eb356a86e029d25"
            ),
            array(
                "jedan/two/tri",
                array( "cro-HR", "eng-GB", "ger-DE" ),
                array( "cro-HR", "eng-GB", "ger-DE" ),
                false,
                316,
                "3-d2cfe69af2d64330670e08efb2c86df7"
            ),
            array(
                "jedan/two/three",
                array( "cro-HR", "eng-GB", "ger-DE" ),
                array( "cro-HR", "eng-GB", "ger-DE" ),
                false,
                316,
                "3-35d6d33467aae9a2e3dccb4b6b027878"
            ),
            array(
                "jedan/two/drei",
                array( "cro-HR", "eng-GB", "ger-DE" ),
                array( "cro-HR", "eng-GB", "ger-DE" ),
                false,
                316,
                "3-1d8d2fd0a99802b89eb356a86e029d25"
            ),
            array(
                "jedan/dva/tri",
                array( "cro-HR", "eng-GB", "ger-DE" ),
                array( "cro-HR", "eng-GB", "ger-DE" ),
                false,
                316,
                "3-d2cfe69af2d64330670e08efb2c86df7"
            ),
            array(
                "jedan/dva/three",
                array( "cro-HR", "eng-GB", "ger-DE" ),
                array( "cro-HR", "eng-GB", "ger-DE" ),
                false,
                316,
                "3-35d6d33467aae9a2e3dccb4b6b027878"
            ),
            array(
                "jedan/dva/drei",
                array( "cro-HR", "eng-GB", "ger-DE" ),
                array( "cro-HR", "eng-GB", "ger-DE" ),
                false,
                316,
                "3-1d8d2fd0a99802b89eb356a86e029d25"
            ),
        );
    }

    /**
     * Test for the lookup() method.
     *
     * Testing that UrlAlias is found and has expected state.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::lookup
     * @dataProvider providerForTestLookupLocationUrlAliasFound
     * @depends testLookup
     * @group location
     */
    public function testLookupLocationUrlAliasFound(
        $url,
        array $prioritizedLanguageCodes,
        array $languageCodes,
        $alwaysAvailable,
        $locationId,
        $id )
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_location.php" );

        $urlAlias = $handler->lookup( $url, $prioritizedLanguageCodes );

        $this->assertLocationUrlAliasCorrect(
            $urlAlias,
            $url,
            $languageCodes,
            $alwaysAvailable,
            $locationId,
            $id
        );
    }

    protected function assertLocationUrlAliasCorrect(
        UrlAlias $urlAlias,
        $url,
        array $languageCodes,
        $alwaysAvailable,
        $locationId,
        $id )
    {
        self::assertInstanceOf( "eZ\\Publish\\SPI\\Persistence\\Content\\UrlAlias", $urlAlias );
        self::assertEquals( UrlAlias::LOCATION, $urlAlias->type );
        self::assertEquals( $url, $urlAlias->path );
        self::assertEquals( $alwaysAvailable, $urlAlias->alwaysAvailable );
        self::assertEquals( $locationId, $urlAlias->destination );
        self::assertEquals( $id, $urlAlias->id );
        self::assertFalse( $urlAlias->forward );
        self::assertFalse( $urlAlias->isCustom );
        self::assertFalse( $urlAlias->isHistory );
        self::assertCount( count( $languageCodes ), array_unique( $urlAlias->languageCodes ) );
        foreach ( $languageCodes as $languageCode )
        {
            self::assertTrue( in_array( $languageCode, $urlAlias->languageCodes ) );
        }
    }

    public function providerForTestLookupLocationCaseCorrection()
    {
        return array(
            array(
                "JEDAN/DVA/TRI",
                array( "cro-HR" ),
                "jedan/dva/tri",
                "3-d2cfe69af2d64330670e08efb2c86df7"
            ),
            array(
                "JEDAN/TWO/DREI",
                array( "cro-HR", "eng-GB", "ger-DE" ),
                "jedan/dva/tri",
                "3-1d8d2fd0a99802b89eb356a86e029d25"
            ),
            array(
                "JEDAN/TWO/DREI",
                array( "eng-GB", "ger-DE" ),
                "jedan/two/three",
                "3-1d8d2fd0a99802b89eb356a86e029d25"
            ),
            array(
                "JEDAN/TWO/THREE",
                array( "eng-GB", "ger-DE" ),
                "jedan/two/three",
                "3-35d6d33467aae9a2e3dccb4b6b027878"
            ),
            array(
                "JEDAN/TWO/THREE",
                array( "ger-DE", "eng-GB" ),
                "jedan/two/drei",
                "3-35d6d33467aae9a2e3dccb4b6b027878"
            ),
            array(
                "JEDAN/TWO/THREE",
                array( "ger-DE", "cro-HR", "eng-GB" ),
                "jedan/dva/drei",
                "3-35d6d33467aae9a2e3dccb4b6b027878"
            ),
        );
    }

    /**
     * Testing that looking up case incorrect URL results in redirection to case correct path.
     *
     * Note that case corrected path is not always equal to case corrected case incorrect path, eg. "JEDAN/TWO/THREE"
     * will not always redirect to "jedan/two/three".
     * In some cases, depending on list of prioritized languages and if Content available in the different language
     * higher in the list of prioritized languages, path showing to that Content will be used.
     * Example: "JEDAN/TWO/DREI" with "eng-GB" and "ger-DE" as prioritized languages will produce redirection
     * to the "jedan/two/three", as "eng-GB" is the most prioritized language and Content that URL alias is pointing
     * to is available in it.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::lookup
     * @dataProvider providerForTestLookupLocationCaseCorrection
     * @depends testLookup
     * @group case-correction
     * @group location
     */
    public function testLookupLocationCaseCorrection( $url, array $prioritizedLanguageCodes, $correctedPath, $id )
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_location.php" );

        $urlAlias = $handler->lookup( $url, $prioritizedLanguageCodes );

        self::assertEquals( $correctedPath, $urlAlias->destination );
        self::assertEquals( strtolower( $url ), $urlAlias->path );
        self::assertEquals( $id, $urlAlias->id );
        self::assertTrue( $urlAlias->forward );

        $urlAliasCorrected = $handler->lookup( $correctedPath, $prioritizedLanguageCodes );

        self::assertEquals( UrlAlias::LOCATION, $urlAlias->type );
        self::assertEquals( $urlAliasCorrected->isCustom, $urlAlias->isCustom );
        self::assertEquals( $urlAliasCorrected->isHistory, $urlAlias->isHistory );
        self::assertEquals( $urlAliasCorrected->alwaysAvailable, $urlAlias->alwaysAvailable );
        self::assertEquals( $urlAliasCorrected->languageCodes, $urlAlias->languageCodes );
        self::assertEquals( $urlAliasCorrected->type, $urlAlias->type );
    }

    public function providerForTestLookupLocationMultipleLanguages()
    {
        return array(
            array(
                "jedan/dva",
                array( "cro-HR" ),
                array( "cro-HR" ),
                false,
                315,
                "2-c67ed9a09ab136fae610b6a087d82e21"
            ),
            array(
                "jedan/dva",
                array( "eng-GB" ),
                array( "eng-GB" ),
                false,
                315,
                "2-c67ed9a09ab136fae610b6a087d82e21"
            ),
            array(
                "jedan/dva",
                array( "cro-HR", "eng-GB" ),
                array( "cro-HR", "eng-GB" ),
                false,
                315,
                "2-c67ed9a09ab136fae610b6a087d82e21"
            ),
            array(
                "jedan/dva/tri",
                array( "cro-HR", "eng-GB" ),
                array( "cro-HR", "eng-GB" ),
                false,
                316,
                "3-d2cfe69af2d64330670e08efb2c86df7"
            ),
            array(
                "jedan/dva/tri",
                array( "cro-HR" ),
                array( "cro-HR" ),
                false,
                316,
                "3-d2cfe69af2d64330670e08efb2c86df7"
            ),
            array(
                "jedan/dva/three",
                array( "eng-GB" ),
                array( "eng-GB" ),
                false,
                316,
                "3-35d6d33467aae9a2e3dccb4b6b027878"
            ),
            array(
                "jedan/dva/three",
                array( "eng-GB", "cro-HR" ),
                array( "eng-GB", "cro-HR" ),
                false,
                316,
                "3-35d6d33467aae9a2e3dccb4b6b027878"
            ),
        );
    }

    /**
     * Test for the lookup() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::lookup
     * @dataProvider providerForTestLookupLocationMultipleLanguages
     * @depends testLookup
     * @group multiple-languages
     * @group location
     */
    public function testLookupLocationMultipleLanguages(
        $url,
        array $prioritizedLanguageCodes,
        array $languageCodes,
        $alwaysAvailable,
        $locationId,
        $id )
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_location_multilang.php" );

        $urlAlias = $handler->lookup( $url, $prioritizedLanguageCodes );

        $this->assertLocationUrlAliasCorrect(
            $urlAlias,
            $url,
            $languageCodes,
            $alwaysAvailable,
            $locationId,
            $id
        );
    }

    /**
     * Test for the lookup() method.
     *
     * @todo document
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::lookup
     * @depends testLookup
     * @group history
     * @group location
     */
    public function testLookupLocationHistoryUrlAlias()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_location.php" );

        $urlAlias = $handler->lookup( "jedan/dva/tri-history", array( "cro-HR" ) );

        self::assertEquals(
            new UrlAlias(
                array(
                    "id" => "3-5f46413bb0ba5998caef84ab1ea590e1",
                    "type" => UrlAlias::LOCATION,
                    "destination" => "jedan/dva/tri",
                    "languageCodes" => array( "cro-HR" ),
                    "alwaysAvailable" => false,
                    "path" => "jedan/dva/tri-history",
                    "isHistory" => true,
                    "isCustom" => false,
                    "forward" => true,
                )
            ),
            $urlAlias
        );
    }

    public function providerForTestLookupVirtualUrlAliasThrowsNotFoundException()
    {
        return array(
            array( "hello", array( "cro-HR" ) ),
            array( "hello/and/goodbye", array( "cro-HR" ) ),
            array( "hello/everyone", array( "cro-HR" ) ),
            array( "well/ha-ha-ha", array( "cro-HR" ) ),
        );
    }

    /**
     * Test for the lookup() method.
     *
     * Throws NotFoundException because parts of URL alias are not always available or not in list
     * of prioritized languages.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::lookup
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @dataProvider providerForTestLookupVirtualUrlAliasThrowsNotFoundException
     * @depends testLookupThrowsNotFoundException
     * @group virtual
     */
    public function testLookupVirtualUrlAliasThrowsNotFoundException( $url, array $prioritizedLanguageCodes )
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_virtual.php" );

        $handler->lookup( $url, $prioritizedLanguageCodes );
    }

    public function providerForTestLookupVirtualUrlAliasFound()
    {
        return array(
            array(
                "autogenerated-hello/everybody",
                array( "cro-HR" ),
                array( "eng-GB" ),
                false,
                true,
                315,
                "2-88150d7d17390010ba6222de68bfafb5"
            ),
            array(
                "hello",
                array( "eng-GB" ),
                array( "eng-GB" ),
                true,
                false,
                "autogenerated-hello",
                "0-5d41402abc4b2a76b9719d911017c592"
            ),
            array(
                "hello/and/goodbye",
                array( "eng-GB" ),
                array( "eng-GB" ),
                true,
                false,
                "autogenerated-goodbye",
                "8-69faab6268350295550de7d587bc323d"
            ),
            array(
                "hello/everyone",
                array( "eng-GB" ),
                array( "eng-GB" ),
                true,
                false,
                "autogenerated-everyone",
                "6-ed881bac6397ede33c0a285c9f50bb83"
            ),
            array(
                "well/ha-ha-ha",
                array( "eng-GB" ),
                array( "eng-GB" ),
                false,
                false,
                317,
                "10-17a197f4bbe127c368b889a67effd1b3"
            ),
            array(
                "autogenerated-hello/everybody",
                array( "eng-GB" ),
                array( "eng-GB" ),
                false,
                true,
                315,
                "2-88150d7d17390010ba6222de68bfafb5"
            ),
            array(
                "hello",
                array( "cro-HR", "eng-GB" ),
                array( "eng-GB" ),
                true,
                false,
                "autogenerated-hello",
                "0-5d41402abc4b2a76b9719d911017c592"
            ),
            array(
                "hello/and/goodbye",
                array( "cro-HR", "eng-GB" ),
                array( "eng-GB" ),
                true,
                false,
                "autogenerated-goodbye",
                "8-69faab6268350295550de7d587bc323d"
            ),
            array(
                "hello/everyone",
                array( "cro-HR", "eng-GB" ),
                array( "eng-GB" ),
                true,
                false,
                "autogenerated-everyone",
                "6-ed881bac6397ede33c0a285c9f50bb83"
            ),
            array(
                "well/ha-ha-ha",
                array( "cro-HR", "eng-GB" ),
                array( "eng-GB" ),
                false,
                false,
                317,
                "10-17a197f4bbe127c368b889a67effd1b3"
            ),
            array(
                "autogenerated-hello/everybody",
                array( "cro-HR", "eng-GB" ),
                array( "eng-GB" ),
                false,
                true,
                315,
                "2-88150d7d17390010ba6222de68bfafb5"
            ),
        );
    }

    /**
     * Test for the lookup() method.
     *
     * Testing that UrlAlias is found and has expected state.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::lookup
     * @dataProvider providerForTestLookupVirtualUrlAliasFound
     * @depends testLookup
     * @group location
     */
    public function testLookupVirtualUrlAliasFound(
        $url,
        array $prioritizedLanguageCodes,
        array $languageCodes,
        $forward,
        $alwaysAvailable,
        $destination,
        $id )
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_virtual.php" );

        $urlAlias = $handler->lookup( $url, $prioritizedLanguageCodes );
        self::assertInstanceOf( "eZ\\Publish\\SPI\\Persistence\\Content\\UrlAlias", $urlAlias );
        self::assertEquals( UrlAlias::VIRTUAL, $urlAlias->type );
        self::assertEquals( $url, $urlAlias->path );
        self::assertEquals( $alwaysAvailable, $urlAlias->alwaysAvailable );
        self::assertEquals( $destination, $urlAlias->destination );
        self::assertEquals( $id, $urlAlias->id );
        self::assertEquals( $forward, $urlAlias->forward );
        self::assertTrue( $urlAlias->isCustom );
        self::assertFalse( $urlAlias->isHistory );
        self::assertCount( count( $languageCodes ), $urlAlias->languageCodes );
        foreach ( $languageCodes as $languageCode )
        {
            self::assertTrue( in_array( $languageCode, $urlAlias->languageCodes ) );
        }
    }

    public function providerForTestLookupVirtualNopForwardsToRoot()
    {
        return array(
            array(
                "hello/and",
                array( "eng-GB" ),
                "6-be5d5d37542d75f93a87094459f76678"
            ),
            array(
                "hello/and",
                array( "cro-HR", "eng-GB" ),
                "6-be5d5d37542d75f93a87094459f76678"
            ),
            array(
                "HELLO/AND",
                array( "eng-GB" ),
                "6-be5d5d37542d75f93a87094459f76678"
            ),
            array(
                "HELLO/AND",
                array( "cro-HR", "eng-GB" ),
                "6-be5d5d37542d75f93a87094459f76678"
            ),
        );
    }

    /**
     * Test for the lookup() method.
     *
     * Testing that NOP action redirects to site root.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::lookup
     * @dataProvider providerForTestLookupVirtualNopForwardsToRoot
     * @depends testLookup
     * @group virtual
     */
    public function testLookupVirtualNopForwardsToRoot( $url, array $prioritizedLanguageCodes, $id )
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_virtual.php" );

        $urlAlias = $handler->lookup( $url, $prioritizedLanguageCodes );
        self::assertInstanceOf( "eZ\\Publish\\SPI\\Persistence\\Content\\UrlAlias", $urlAlias );
        self::assertEquals( UrlAlias::VIRTUAL, $urlAlias->type );
        self::assertEquals( strtolower( $url ), $urlAlias->path );
        self::assertTrue( $urlAlias->alwaysAvailable );
        self::assertEquals( "/", $urlAlias->destination );
        self::assertEquals( $id, $urlAlias->id );
        self::assertTrue( $urlAlias->forward );
        self::assertTrue( $urlAlias->isCustom );
        self::assertFalse( $urlAlias->isHistory );
        self::assertEmpty( $urlAlias->languageCodes );
    }

    public function providerForTestLookupVirtualCaseCorrection()
    {
        return array(
            array(
                "HELLO",
                array( "eng-GB" ),
                array( "eng-GB" ),
                "autogenerated-hello",
                "0-5d41402abc4b2a76b9719d911017c592",
                false
            ),
            array(
                "AUTOGENERATED-HELLO/EVERYBODY",
                array( "cro-HR" ),
                array( "eng-GB" ),
                "autogenerated-hello/everybody",
                "2-88150d7d17390010ba6222de68bfafb5",
                true
            ),
            array(
                "WELL/HA-HA-HA",
                array( "eng-GB" ),
                array( "eng-GB" ),
                "well/ha-ha-ha",
                "10-17a197f4bbe127c368b889a67effd1b3",
                false
            ),
        );
    }

    /**
     * Test for the lookup() method.
     *
     * Testing that looking up case incorrect URL results in redirection to case correct path.
     *
     * Note that case corrected path is not always equal to case corrected case incorrect path, eg. "JEDAN/TWO/THREE"
     * will not always redirect to "jedan/two/three".
     * In some cases, depending on list of prioritized languages and if Content available in the different language
     * higher in the list of prioritized languages, path showing to that Content will be used.
     * Example: "JEDAN/TWO/DREI" with "eng-GB" and "ger-DE" as prioritized languages will produce redirection
     * to the "jedan/two/three", as "eng-GB" is the most prioritized language and Content that URL alias is pointing
     * to is available in it.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::lookup
     * @dataProvider providerForTestLookupVirtualCaseCorrection
     * @depends testLookup
     * @group case-correction
     * @group virtual
     */
    public function testLookupVirtualCaseCorrection(
        $url,
        array $prioritizedLanguageCodes,
        array $languageCodes,
        $correctedPath,
        $id,
        $alwaysAvailable )
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_virtual.php" );

        $urlAlias = $handler->lookup( $url, $prioritizedLanguageCodes );

        self::assertEquals( $correctedPath, $urlAlias->destination );
        self::assertEquals( strtolower( $url ), $urlAlias->path );
        self::assertEquals( $id, $urlAlias->id );
        self::assertTrue( $urlAlias->forward );
        self::assertTrue( $urlAlias->isCustom );
        self::assertEquals( $alwaysAvailable, $urlAlias->alwaysAvailable );
        self::assertEquals( UrlAlias::VIRTUAL, $urlAlias->type );
        self::assertFalse( $urlAlias->isHistory );
        self::assertEquals( $languageCodes, $urlAlias->languageCodes );
    }

    public function providerForTestLookupResourceUrlAliasThrowsNotFoundException()
    {
        return array(
            array( "is-alive/then/search", array( "cro-HR" ) ),
        );
    }

    /**
     * Test for the lookup() method.
     *
     * Throws NotFoundException because parts of URL alias are not always available or not in list
     * of prioritized languages.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::lookup
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @dataProvider providerForTestLookupResourceUrlAliasThrowsNotFoundException
     * @depends testLookupThrowsNotFoundException
     * @group resource
     */
    public function testLookupResourceUrlAliasThrowsNotFoundException( $url, array $prioritizedLanguageCodes )
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_resource.php" );

        $handler->lookup( $url, $prioritizedLanguageCodes );
    }

    public function providerForTestLookupResourceUrlAliasFound()
    {
        return array(
            array(
                "is-alive",
                array( "eng-GB" ),
                array( "eng-GB" ),
                true,
                true,
                "ezinfo/isalive",
                "0-d003895fa282a14c8ec3eddf23ca4ca2"
            ),
            array(
                "is-alive/then/search",
                array( "eng-GB" ),
                array( "eng-GB" ),
                false,
                false,
                "content/search",
                "3-06a943c59f33a34bb5924aaf72cd2995"
            ),
            array(
                "is-alive/then/search",
                array( "cro-HR", "eng-GB" ),
                array( "eng-GB" ),
                false,
                false,
                "content/search",
                "3-06a943c59f33a34bb5924aaf72cd2995"
            ),
        );
    }

    /**
     * Test for the lookup() method.
     *
     * Testing that UrlAlias is found and has expected state.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::lookup
     * @dataProvider providerForTestLookupResourceUrlAliasFound
     * @depends testLookup
     * @group resource
     */
    public function testLookupResourceUrlAliasFound(
        $url,
        array $prioritizedLanguageCodes,
        array $languageCodes,
        $forward,
        $alwaysAvailable,
        $destination,
        $id )
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_resource.php" );

        $urlAlias = $handler->lookup( $url, $prioritizedLanguageCodes );
        self::assertInstanceOf( "eZ\\Publish\\SPI\\Persistence\\Content\\UrlAlias", $urlAlias );
        self::assertEquals( UrlAlias::RESOURCE, $urlAlias->type );
        self::assertEquals( $url, $urlAlias->path );
        self::assertEquals( $alwaysAvailable, $urlAlias->alwaysAvailable );
        self::assertEquals( $destination, $urlAlias->destination );
        self::assertEquals( $id, $urlAlias->id );
        self::assertEquals( $forward, $urlAlias->forward );
        self::assertTrue( $urlAlias->isCustom );
        self::assertFalse( $urlAlias->isHistory );
        self::assertCount( count( $languageCodes ), $urlAlias->languageCodes );
        foreach ( $languageCodes as $languageCode )
        {
            self::assertTrue( in_array( $languageCode, $urlAlias->languageCodes ) );
        }
    }

    public function providerForTestLookupResourceNopForwardsToRoot()
    {
        return array(
            array(
                "is-alive/then",
                array( "cro-HR" ),
                "2-0e5243d9965540f62aac19a985f3f33e"
            ),
            array(
                "is-alive/then",
                array( "eng-GB" ),
                "2-0e5243d9965540f62aac19a985f3f33e"
            ),
            array(
                "IS-ALIVE/THEN",
                array( "cro-HR" ),
                "2-0e5243d9965540f62aac19a985f3f33e"
            ),
            array(
                "IS-ALIVE/THEN",
                array( "eng-GB" ),
                "2-0e5243d9965540f62aac19a985f3f33e"
            ),
        );
    }

    /**
     * Test for the lookup() method.
     *
     * Testing that NOP action redirects to site root.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::lookup
     * @dataProvider providerForTestLookupResourceNopForwardsToRoot
     * @depends testLookup
     * @group resource
     */
    public function testLookupResourceNopForwardsToRoot( $url, array $prioritizedLanguageCodes, $id )
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_resource.php" );

        $urlAlias = $handler->lookup( $url, $prioritizedLanguageCodes );
        self::assertInstanceOf( "eZ\\Publish\\SPI\\Persistence\\Content\\UrlAlias", $urlAlias );
        self::assertEquals( UrlAlias::VIRTUAL, $urlAlias->type );
        self::assertEquals( strtolower( $url ), $urlAlias->path );
        self::assertTrue( $urlAlias->alwaysAvailable );
        self::assertEquals( "/", $urlAlias->destination );
        self::assertEquals( $id, $urlAlias->id );
        self::assertTrue( $urlAlias->forward );
        self::assertTrue( $urlAlias->isCustom );
        self::assertFalse( $urlAlias->isHistory );
        self::assertEmpty( $urlAlias->languageCodes );
    }

    public function providerForTestLookupResourceCaseCorrection()
    {
        return array(
            array(
                "IS-ALIVE",
                array( "eng-GB" ),
                array( "eng-GB" ),
                "ezinfo/isalive",
                "0-d003895fa282a14c8ec3eddf23ca4ca2",
                true
            ),
            array(
                "IS-ALIVE",
                array( "cro-HR" ),
                array( "eng-GB" ),
                "ezinfo/isalive",
                "0-d003895fa282a14c8ec3eddf23ca4ca2",
                true
            ),
            array(
                "IS-ALIVE/THEN/SEARCH",
                array( "eng-GB" ),
                array( "eng-GB" ),
                "is-alive/then/search",
                "3-06a943c59f33a34bb5924aaf72cd2995",
                false
            ),
        );
    }

    /**
     * Test for the lookup() method.
     *
     * Testing that looking up case incorrect URL results in redirection to case correct path.
     *
     * Note that case corrected path is not always equal to case corrected case incorrect path, eg. "JEDAN/TWO/THREE"
     * will not always redirect to "jedan/two/three".
     * In some cases, depending on list of prioritized languages and if Content available in the different language
     * higher in the list of prioritized languages, path showing to that Content will be used.
     * Example: "JEDAN/TWO/DREI" with "eng-GB" and "ger-DE" as prioritized languages will produce redirection
     * to the "jedan/two/three", as "eng-GB" is the most prioritized language and Content that URL alias is pointing
     * to is available in it.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::lookup
     * @dataProvider providerForTestLookupResourceCaseCorrection
     * @depends testLookup
     * @group case-correction
     * @group resource
     */
    public function testLookupResourceCaseCorrection(
        $url,
        array $prioritizedLanguageCodes,
        array $languageCodes,
        $correctedPath,
        $id,
        $alwaysAvailable )
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_resource.php" );

        $urlAlias = $handler->lookup( $url, $prioritizedLanguageCodes );

        self::assertEquals( $correctedPath, $urlAlias->destination );
        self::assertEquals( strtolower( $url ), $urlAlias->path );
        self::assertEquals( $id, $urlAlias->id );
        self::assertTrue( $urlAlias->forward );
        self::assertTrue( $urlAlias->isCustom );
        self::assertEquals( $alwaysAvailable, $urlAlias->alwaysAvailable );
        self::assertEquals( UrlAlias::RESOURCE, $urlAlias->type );
        self::assertFalse( $urlAlias->isHistory );
        self::assertEquals( $languageCodes, $urlAlias->languageCodes );
    }

    /**
     * Test for the publishUrlAliasForLocation() method.
     *
     * @todo document
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::publishUrlAliasForLocation
     * @depends testLookupLocationUrlAliasThrowsNotFoundException
     * @depends testLookupLocationUrlAliasFound
     * @depends testLookupLocationCaseCorrection
     * @group publish
     */
    public function testPublishUrlAliasForLocation()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/publish_base.php" );

        $publishedUrlAlias = $handler->publishUrlAliasForLocation( 314, "simple", "eng-GB", true );

        self::assertEquals( 2, $this->countRows() );
        self::assertEquals(
            new UrlAlias(
                array(
                    "id" => "0-8dbdda48fb8748d6746f1965824e966a",
                    "type" => UrlAlias::LOCATION,
                    "destination" => "314",
                    "languageCodes" => array( "eng-GB" ),
                    "alwaysAvailable" => true,
                    "path" => "simple",
                    "isHistory" => false,
                    "isCustom" => false,
                    "forward" => false,
                )
            ),
            $publishedUrlAlias
        );
    }

    /**
     * Test for the publishUrlAliasForLocation() method.
     *
     * @todo document
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::publishUrlAliasForLocation
     * @_depends testPublishUrlAliasForLocation
     * @group publish
     */
    public function testPublishUrlAliasForLocationRepublish()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/publish_base.php" );

        $publishedUrlAlias = $handler->publishUrlAliasForLocation( 314, "simple", "eng-GB", true );
        $republishedUrlAlias = $handler->publishUrlAliasForLocation( 314, "simple", "eng-GB", true );
        self::assertEquals( 2, $this->countRows() );
        self::assertEquals(
            $publishedUrlAlias,
            $republishedUrlAlias
        );
    }

    public function providerForTestPublishUrlAliasForLocationComplex()
    {
        return $this->providerForTestLookupLocationUrlAliasFound();
    }

    /**
     * Test for the publishUrlAliasForLocation() method.
     *
     * @todo document
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::publishUrlAliasForLocation
     * @dataProvider providerForTestPublishUrlAliasForLocationComplex
     * @depends testLookupLocationUrlAliasFound
     * @depends testPublishUrlAliasForLocation
     * @group publish
     */
    public function testPublishUrlAliasForLocationComplexFound(
        $url,
        array $prioritizedLanguageCodes,
        array $languageCodes,
        $alwaysAvailable,
        $locationId,
        $id )
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/publish_base.php" );

        $handler->publishUrlAliasForLocation( 314, "jedan", "cro-HR", true );
        $handler->publishUrlAliasForLocation( 315, "dva", "cro-HR", false );
        $handler->publishUrlAliasForLocation( 315, "two", "eng-GB", false );
        $handler->publishUrlAliasForLocation( 316, "tri", "cro-HR", false );
        $handler->publishUrlAliasForLocation( 316, "three", "eng-GB", false );
        $handler->publishUrlAliasForLocation( 316, "drei", "ger-DE", false );

        $urlAlias = $handler->lookup( $url, $prioritizedLanguageCodes );

        $this->assertLocationUrlAliasCorrect(
            $urlAlias,
            $url,
            $languageCodes,
            $alwaysAvailable,
            $locationId,
            $id
        );
    }

    public function providerForTestPublishUrlAliasForLocationComplexThrowsNotFoundException()
    {
        return $this->providerForTestLookupLocationUrlAliasThrowsNotFoundException();
    }

    /**
     * Test for the publishUrlAliasForLocation() method.
     *
     * @todo document
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::publishUrlAliasForLocation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @dataProvider providerForTestLookupLocationUrlAliasThrowsNotFoundException
     * @depends testLookupLocationUrlAliasThrowsNotFoundException
     * @depends testLookupLocationUrlAliasFound
     * @depends testLookupLocationCaseCorrection
     * @depends testPublishUrlAliasForLocation
     * @group publish
     */
    public function testPublishUrlAliasForLocationComplexThrowsNotFoundException( $url, array $prioritizedLanguageCodes )
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/publish_base.php" );

        $handler->publishUrlAliasForLocation( 314, "jedan", "cro-HR", true );
        $handler->publishUrlAliasForLocation( 315, "dva", "cro-HR", false );
        $handler->publishUrlAliasForLocation( 315, "two", "eng-GB", false );
        $handler->publishUrlAliasForLocation( 316, "tri", "cro-HR", false );
        $handler->publishUrlAliasForLocation( 316, "three", "eng-GB", false );
        $handler->publishUrlAliasForLocation( 316, "drei", "ger-DE", false );

        $handler->lookup( $url, $prioritizedLanguageCodes );
    }

    /**
     * Test for the publishUrlAliasForLocation() method.
     *
     * @todo document
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::publishUrlAliasForLocation
     * @depends testPublishUrlAliasForLocation
     * @group publish
     */
    public function testPublishUrlAliasForLocationSameAliasForMultipleLanguages()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/publish_base.php" );

        $urlAlias1 = $handler->publishUrlAliasForLocation( 314, "jedan", "cro-HR", true );
        $urlAlias2 = $handler->publishUrlAliasForLocation( 314, "jedan", "eng-GB", false );

        self::assertEquals( 2, $this->countRows() );
        self::assertEquals( $urlAlias1->id, $urlAlias2->id );
        self::assertEquals( array( "cro-HR", "eng-GB" ), $urlAlias2->languageCodes );

        $urlAlias1 = $handler->lookup( "jedan", array( "cro-HR" ) );
        $urlAlias2 = $handler->lookup( "jedan", array( "eng-GB" ) );
        $urlAlias3 = $handler->lookup( "jedan", array( "cro-HR", "eng-GB" ) );

        self::assertEquals( array( "cro-HR" ), $urlAlias1->languageCodes );
        self::assertEquals( array( "eng-GB" ), $urlAlias2->languageCodes );
        self::assertEquals( array( "cro-HR", "eng-GB" ), $urlAlias3->languageCodes );
    }

    /**
     * Test for the publishUrlAliasForLocation() method.
     *
     * @todo document
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::publishUrlAliasForLocation
     * @depends testPublishUrlAliasForLocation
     * @group publish
     */
    public function testPublishUrlAliasForLocationDowngradesOldEntryToHistory()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/publish_base.php" );

        $handler->publishUrlAliasForLocation( 314, "jedan", "cro-HR", false );
        $newUrlAlias = $handler->publishUrlAliasForLocation( 314, "dva", "cro-HR", true );

        self::assertEquals( 3, $this->countRows() );

        self::assertEquals(
            new UrlAlias(
                array(
                    "id" => "0-c67ed9a09ab136fae610b6a087d82e21",
                    "type" => 0,
                    "destination" => 314,
                    "languageCodes" => array( "cro-HR" ),
                    "alwaysAvailable" => true,
                    "path" => "dva",
                    "isHistory" => false,
                    "isCustom" => false,
                    "forward" => false
                )
            ),
            $newUrlAlias
        );

        $historyUrlAlias = $handler->lookup( "jedan", array( "cro-HR" ) );

        self::assertEquals(
            new UrlAlias(
                array(
                    "id" => "0-6896260129051a949051c3847c34466f",
                    "type" => 0,
                    "destination" => "dva",
                    "languageCodes" => array( "cro-HR" ),
                    "alwaysAvailable" => false,
                    "path" => "jedan",
                    "isHistory" => true,
                    "isCustom" => false,
                    "forward" => true
                )
            ),
            $historyUrlAlias
        );
    }

    /**
     * Test for the publishUrlAliasForLocation() method.
     *
     * @todo document
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::publishUrlAliasForLocation
     * @depends testPublishUrlAliasForLocation
     * @depends testPublishUrlAliasForLocationSameAliasForMultipleLanguages
     * @group publish
     * @group downgrade
     */
    public function testPublishUrlAliasForLocationDowngradesOldEntryRemovesLanguage()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/publish_base.php" );

        $handler->publishUrlAliasForLocation( 314, "jedan", "cro-HR", false );
        $handler->publishUrlAliasForLocation( 314, "jedan", "eng-GB", false );
        $newUrlAlias = $handler->publishUrlAliasForLocation( 314, "dva", "eng-GB", false );

        self::assertEquals( 3, $this->countRows() );
        self::assertEquals(
            array( "eng-GB" ),
            $newUrlAlias->languageCodes
        );

        $oldUrlAlias = $handler->lookup( "jedan", array( "cro-HR", "eng-GB" ) );
        self::assertEquals(
            array( "cro-HR", "eng-GB" ),
            $oldUrlAlias->languageCodes
        );

        $newUrlAlias = $handler->lookup( "dva", array( "cro-HR", "eng-GB" ) );
        self::assertEquals(
            array( "cro-HR", "eng-GB" ),
            $newUrlAlias->languageCodes
        );

        $downgradedUrlAlias = $handler->lookup( "jedan", array( "cro-HR" ) );
        self::assertEquals(
            new UrlAlias(
                array(
                    "id" => "0-6896260129051a949051c3847c34466f",
                    "type" => 0,
                    "destination" => 314,
                    "languageCodes" => array( "cro-HR" ),
                    "alwaysAvailable" => false,
                    "path" => "jedan",
                    "isHistory" => false,
                    "isCustom" => false,
                    "forward" => false
                )
            ),
            $downgradedUrlAlias
        );

        try
        {
            $handler->lookup( "jedan", array( "eng-GB" ) );
            self::fail( "Language is not removed!" );
        }
        catch ( NotFoundException $e )
        {
            // expected
        }

        try
        {
            $handler->lookup( "dva", array( "cro-HR" ) );
            self::fail( "URL alias should not be loaded!" );
        }
        catch ( NotFoundException $e )
        {
            // expected
        }
    }

    /**
     * Test for the publishUrlAliasForLocation() method.
     *
     * @todo document
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::publishUrlAliasForLocation
     * @depends testPublishUrlAliasForLocationDowngradesOldEntryToHistory
     * @group publish
     */
    public function testPublishUrlAliasForLocationReusesHistory()
    {
        throw new \Exception( "implement" );
    }

    /**
     * Test for the publishUrlAliasForLocation() method.
     *
     * @todo document
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::publishUrlAliasForLocation
     * @group publish
     */
    public function testPublishUrlAliasForLocationReusesCustomAlias()
    {
        throw new \Exception( "implement" );
    }

    public function providerForTestPublishUrlAliasForLocationReusesNopElement()
    {
        return array(
            array(
                "locationId" => 314,
                "name" => "nop-element",
                "url" => "nop-element",
                "languageCode" => "eng-GB",
                "alwaysAvailable" => false,
                "virtualUrl" => "nop-element/search",
                "virtualUrlPrioritizedLanguageCodes" => array( "eng-GB" ),
                "id" => "0-de55c2fff721217cc4cb67b58dc35f85"
            ),
        );
    }

    /**
     * Test for the publishUrlAliasForLocation() method.
     *
     * @todo document
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::publishUrlAliasForLocation
     * @dataProvider providerForTestPublishUrlAliasForLocationReusesNopElement
     * @_depends testLookupLocationUrlAliasThrowsNotFoundException
     * @_depends testLookupLocationUrlAliasFound
     * @_depends testLookupLocationCaseCorrection
     * @group publish
     */
    public function testPublishUrlAliasForLocationReusingNopElement(
        $locationId,
        $name,
        $url,
        $languageCode,
        $alwaysAvailable,
        $virtualUrl,
        $virtualUrlPrioritizedLanguageCodes,
        $id )
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_reusing.php" );

        $virtualUrlAlias = $handler->lookup( $virtualUrl, $virtualUrlPrioritizedLanguageCodes );
        $publishedLocationUrlAlias = $handler->publishUrlAliasForLocation( $locationId, $name, $languageCode, $alwaysAvailable );

        self::assertEquals( 3, $this->countRows() );
        self::assertEquals( $publishedLocationUrlAlias, $handler->lookup( $url, array( $languageCode ) ) );
        $this->assertLocationUrlAliasCorrect(
            $publishedLocationUrlAlias,
            $url,
            array( $languageCode ),
            $alwaysAvailable,
            $locationId,
            $id
        );

        // Check that virtual alias still works as expected
        self::assertEquals( $virtualUrlAlias, $handler->lookup( $virtualUrl, $virtualUrlPrioritizedLanguageCodes ) );
    }








    /**
     * @return int
     */
    protected function countRows()
    {
        /** @var \ezcQuerySelect $query  */
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            $query->expr->count( "*" )
        )->from(
            $this->dbHandler->quoteTable( "ezurlalias_ml" )
        );

        $statement = $query->prepare();
        $statement->execute();

        return (int)$statement->fetchColumn();
    }

    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler
     */
    protected $dbHandler;

    protected function getHandler()
    {
        $this->dbHandler = $this->getDatabaseHandler();
        $languageHandler = new LanguageHandler(
            new LanguageGateway(
                $this->getDatabaseHandler()
            ),
            new LanguageMapper()
        );
        $languageMaskGenerator = new LanguageMaskGenerator( $languageHandler );
        $gateway = new EzcDatabase(
            $this->dbHandler,
            $languageHandler,
            $languageMaskGenerator
        );
        $mapper = new Mapper();
        $locationGateway = new LocationGateway( $this->dbHandler );

        return new Handler(
            $gateway,
            $mapper,
            $locationGateway,
            $languageHandler,
            $languageMaskGenerator
        );
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
