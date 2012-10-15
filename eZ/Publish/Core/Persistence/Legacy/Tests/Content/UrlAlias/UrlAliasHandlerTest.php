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
    eZ\Publish\Core\Persistence\Legacy\Content\Language\Handler as LanguageHandler,
    eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway\EzcDatabase as LanguageGateway,
    eZ\Publish\Core\Persistence\Legacy\Content\Language\Mapper as LanguageMapper,
    eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator as LanguageMaskGenerator,
    eZ\Publish\Core\Persistence\Legacy\Content\Search\TransformationProcessor,
    eZ\Publish\Core\Persistence\Legacy\Content\Search\TransformationParser,
    eZ\Publish\Core\Persistence\Legacy\Content\Search\TransformationPcreCompiler,
    eZ\Publish\Core\Persistence\Legacy\Content\Search\Utf8Converter,
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

        $urlAlias = $handler->lookup( "jedan" );
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
        $handler->lookup( "wooden/iron" );
    }

    public function providerForTestLookupLocationUrlAlias()
    {
        return array(
            array(
                "jedan",
                array(
                    array(
                        "always-available" => true,
                        "translations" => array(
                            "cro-HR" => "jedan"
                        )
                    )
                ),
                array(
                    array(
                        "always-available" => true,
                        "language-codes" => array( "cro-HR" )
                    )
                ),
                array( "cro-HR" ),
                true,
                314,
                "0-6896260129051a949051c3847c34466f"
            ),
            array(
                "jedan/dva",
                array(
                    array(
                        "always-available" => true,
                        "translations" => array(
                            "cro-HR" => "jedan"
                        )
                    ),
                    array(
                        "always-available" => false,
                        "translations" => array(
                            "cro-HR" => "dva",
                            "eng-GB" => "two",
                        )
                    )
                ),
                array(
                    array(
                        "always-available" => true,
                        "language-codes" => array( "cro-HR" )
                    ),
                    array(
                        "always-available" => false,
                        "language-codes" => array( "cro-HR" )
                    )
                ),
                array( "cro-HR" ),
                false,
                315,
                "2-c67ed9a09ab136fae610b6a087d82e21"
            ),
            array(
                "jedan/two",
                array(
                    array(
                        "always-available" => true,
                        "translations" => array(
                            "cro-HR" => "jedan"
                        )
                    ),
                    array(
                        "always-available" => false,
                        "translations" => array(
                            "cro-HR" => "dva",
                            "eng-GB" => "two",
                        )
                    )
                ),
                array(
                    array(
                        "always-available" => true,
                        "language-codes" => array( "cro-HR" )
                    ),
                    array(
                        "always-available" => false,
                        "language-codes" => array( "eng-GB" )
                    )
                ),
                array( "eng-GB" ),
                false,
                315,
                "2-b8a9f715dbb64fd5c56e7783c6820a61"
            ),
            array(
                "jedan/dva/tri",
                array(
                    array(
                        "always-available" => true,
                        "translations" => array(
                            "cro-HR" => "jedan"
                        )
                    ),
                    array(
                        "always-available" => false,
                        "translations" => array(
                            "cro-HR" => "dva",
                            "eng-GB" => "two",
                        )
                    ),
                    array(
                        "always-available" => false,
                        "translations" => array(
                            "cro-HR" => "tri",
                            "eng-GB" => "three",
                            "ger-DE" => "drei",
                        )
                    ),
                ),
                array(
                    array(
                        "always-available" => true,
                        "language-codes" => array( "cro-HR" )
                    ),
                    array(
                        "always-available" => false,
                        "language-codes" => array( "cro-HR" )
                    ),
                    array(
                        "always-available" => false,
                        "language-codes" => array( "cro-HR" )
                    )
                ),
                array( "cro-HR" ),
                false,
                316,
                "3-d2cfe69af2d64330670e08efb2c86df7"
            ),
            array(
                "jedan/two/three",
                array(
                    array(
                        "always-available" => true,
                        "translations" => array(
                            "cro-HR" => "jedan"
                        )
                    ),
                    array(
                        "always-available" => false,
                        "translations" => array(
                            "cro-HR" => "dva",
                            "eng-GB" => "two",
                        )
                    ),
                    array(
                        "always-available" => false,
                        "translations" => array(
                            "cro-HR" => "tri",
                            "eng-GB" => "three",
                            "ger-DE" => "drei",
                        )
                    ),
                ),
                array(
                    array(
                        "always-available" => true,
                        "language-codes" => array( "cro-HR" )
                    ),
                    array(
                        "always-available" => false,
                        "language-codes" => array( "eng-GB" )
                    ),
                    array(
                        "always-available" => false,
                        "language-codes" => array( "eng-GB" )
                    )
                ),
                array( "eng-GB" ),
                false,
                316,
                "3-35d6d33467aae9a2e3dccb4b6b027878"
            ),
            array(
                "jedan/dva/three",
                array(
                    array(
                        "always-available" => true,
                        "translations" => array(
                            "cro-HR" => "jedan"
                        )
                    ),
                    array(
                        "always-available" => false,
                        "translations" => array(
                            "cro-HR" => "dva",
                            "eng-GB" => "two",
                        )
                    ),
                    array(
                        "always-available" => false,
                        "translations" => array(
                            "cro-HR" => "tri",
                            "eng-GB" => "three",
                            "ger-DE" => "drei",
                        )
                    ),
                ),
                array(
                    array(
                        "always-available" => true,
                        "language-codes" => array( "cro-HR" )
                    ),
                    array(
                        "always-available" => false,
                        "language-codes" => array( "cro-HR" )
                    ),
                    array(
                        "always-available" => false,
                        "language-codes" => array( "eng-GB" )
                    )
                ),
                array( "eng-GB" ),
                false,
                316,
                "3-35d6d33467aae9a2e3dccb4b6b027878"
            ),
            array(
                "jedan/two/tri",
                array(
                    array(
                        "always-available" => true,
                        "translations" => array(
                            "cro-HR" => "jedan"
                        )
                    ),
                    array(
                        "always-available" => false,
                        "translations" => array(
                            "cro-HR" => "dva",
                            "eng-GB" => "two",
                        )
                    ),
                    array(
                        "always-available" => false,
                        "translations" => array(
                            "cro-HR" => "tri",
                            "eng-GB" => "three",
                            "ger-DE" => "drei",
                        )
                    ),
                ),
                array(
                    array(
                        "always-available" => true,
                        "language-codes" => array( "cro-HR" )
                    ),
                    array(
                        "always-available" => false,
                        "language-codes" => array( "eng-GB" )
                    ),
                    array(
                        "always-available" => false,
                        "language-codes" => array( "cro-HR" )
                    )
                ),
                array( "cro-HR" ),
                false,
                316,
                "3-d2cfe69af2d64330670e08efb2c86df7"
            ),
            array(
                "jedan/dva/drei",
                array(
                    array(
                        "always-available" => true,
                        "translations" => array(
                            "cro-HR" => "jedan"
                        )
                    ),
                    array(
                        "always-available" => false,
                        "translations" => array(
                            "cro-HR" => "dva",
                            "eng-GB" => "two",
                        )
                    ),
                    array(
                        "always-available" => false,
                        "translations" => array(
                            "cro-HR" => "tri",
                            "eng-GB" => "three",
                            "ger-DE" => "drei",
                        )
                    ),
                ),
                array(
                    array(
                        "always-available" => true,
                        "language-codes" => array( "cro-HR" )
                    ),
                    array(
                        "always-available" => false,
                        "language-codes" => array( "cro-HR" )
                    ),
                    array(
                        "always-available" => false,
                        "language-codes" => array( "ger-DE" )
                    )
                ),
                array( "ger-DE" ),
                false,
                316,
                "3-1d8d2fd0a99802b89eb356a86e029d25"
            ),
            array(
                "jedan/two/drei",
                array(
                    array(
                        "always-available" => true,
                        "translations" => array(
                            "cro-HR" => "jedan"
                        )
                    ),
                    array(
                        "always-available" => false,
                        "translations" => array(
                            "cro-HR" => "dva",
                            "eng-GB" => "two",
                        )
                    ),
                    array(
                        "always-available" => false,
                        "translations" => array(
                            "cro-HR" => "tri",
                            "eng-GB" => "three",
                            "ger-DE" => "drei",
                        )
                    ),
                ),
                array(
                    array(
                        "always-available" => true,
                        "language-codes" => array( "cro-HR" )
                    ),
                    array(
                        "always-available" => false,
                        "language-codes" => array( "eng-GB" )
                    ),
                    array(
                        "always-available" => false,
                        "language-codes" => array( "ger-DE" )
                    )
                ),
                array( "ger-DE" ),
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
     * @dataProvider providerForTestLookupLocationUrlAlias
     * @_depends testLookup
     * @group location
     */
    public function testLookupLocationUrlAlias(
        $url,
        array $pathData,
        array $pathLanguageData,
        array $languageCodes,
        $alwaysAvailable,
        $locationId,
        $id
    )
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_location.php" );

        $urlAlias = $handler->lookup( $url );

        self::assertInstanceOf( "eZ\\Publish\\SPI\\Persistence\\Content\\UrlAlias", $urlAlias );
        self::assertEquals( UrlAlias::LOCATION, $urlAlias->type );
        self::assertEquals( $alwaysAvailable, $urlAlias->alwaysAvailable );
        self::assertEquals( $locationId, $urlAlias->destination );
        self::assertEquals( $id, $urlAlias->id );
        self::assertFalse( $urlAlias->forward );
        self::assertFalse( $urlAlias->isCustom );
        self::assertFalse( $urlAlias->isHistory );
        self::assertEquals( $languageCodes, $urlAlias->languageCodes );
        self::assertEquals( $pathLanguageData, $urlAlias->pathLanguageData );
        self::assertEquals( $pathData, $urlAlias->pathData );
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
     * @dataProvider providerForTestLookupLocationUrlAlias
     * @_depends testLookup
     * @group case-correction
     * @group location
     * @todo refactor, only forward pretinent
     */
    public function testLookupLocationCaseCorrection(
        $url,
        array $pathData,
        array $pathLanguageData,
        array $languageCodes,
        $alwaysAvailable,
        $locationId,
        $id )
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_location.php" );

        $urlAlias = $handler->lookup( strtoupper( $url ) );

        self::assertInstanceOf( "eZ\\Publish\\SPI\\Persistence\\Content\\UrlAlias", $urlAlias );
        self::assertEquals( UrlAlias::LOCATION, $urlAlias->type );
        self::assertEquals( $alwaysAvailable, $urlAlias->alwaysAvailable );
        self::assertEquals( $locationId, $urlAlias->destination );
        self::assertEquals( $id, $urlAlias->id );
        self::assertFalse( $urlAlias->forward );
        self::assertFalse( $urlAlias->isCustom );
        self::assertFalse( $urlAlias->isHistory );
        self::assertEquals( $languageCodes, $urlAlias->languageCodes );
        self::assertEquals( $pathLanguageData, $urlAlias->pathLanguageData );
        self::assertEquals( $pathData, $urlAlias->pathData );
    }

    public function providerForTestLookupLocationMultipleLanguages()
    {
        return array(
            array(
                "jedan/dva",
                array(
                    array(
                        "always-available" => true,
                        "translations" => array(
                            "cro-HR" => "jedan"
                        )
                    ),
                    array(
                        "always-available" => false,
                        "translations" => array(
                            "cro-HR" => "dva",
                            "eng-GB" => "dva",
                        )
                    ),
                ),
                array(
                    array(
                        "always-available" => true,
                        "language-codes" => array( "cro-HR" )
                    ),
                    array(
                        "always-available" => false,
                        "language-codes" => array( "cro-HR", "eng-GB" )
                    )
                ),
                array( "cro-HR", "eng-GB" ),
                false,
                315,
                "2-c67ed9a09ab136fae610b6a087d82e21"
            ),
            array(
                "jedan/dva/tri",
                array(
                    array(
                        "always-available" => true,
                        "translations" => array(
                            "cro-HR" => "jedan"
                        )
                    ),
                    array(
                        "always-available" => false,
                        "translations" => array(
                            "cro-HR" => "dva",
                            "eng-GB" => "dva",
                        )
                    ),
                    array(
                        "always-available" => false,
                        "translations" => array(
                            "cro-HR" => "tri",
                            "eng-GB" => "three",
                        )
                    ),
                ),
                array(
                    array(
                        "always-available" => true,
                        "language-codes" => array( "cro-HR" )
                    ),
                    array(
                        "always-available" => false,
                        "language-codes" => array( "cro-HR", "eng-GB" )
                    ),
                    array(
                        "always-available" => false,
                        "language-codes" => array( "cro-HR" )
                    )
                ),
                array( "cro-HR" ),
                false,
                316,
                "3-d2cfe69af2d64330670e08efb2c86df7"
            ),
            array(
                "jedan/dva/three",
                array(
                    array(
                        "always-available" => true,
                        "translations" => array(
                            "cro-HR" => "jedan"
                        )
                    ),
                    array(
                        "always-available" => false,
                        "translations" => array(
                            "cro-HR" => "dva",
                            "eng-GB" => "dva",
                        )
                    ),
                    array(
                        "always-available" => false,
                        "translations" => array(
                            "cro-HR" => "tri",
                            "eng-GB" => "three",
                        )
                    ),
                ),
                array(
                    array(
                        "always-available" => true,
                        "language-codes" => array( "cro-HR" )
                    ),
                    array(
                        "always-available" => false,
                        "language-codes" => array( "cro-HR", "eng-GB" )
                    ),
                    array(
                        "always-available" => false,
                        "language-codes" => array( "eng-GB" )
                    )
                ),
                array( "eng-GB" ),
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
     * @_depends testLookup2
     * @group multiple-languages
     * @group location
     */
    public function testLookupLocationMultipleLanguages(
        $url,
        array $pathData,
        array $pathLanguageData,
        array $languageCodes,
        $alwaysAvailable,
        $locationId,
        $id )
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_location_multilang.php" );

        $urlAlias = $handler->lookup( $url );

        self::assertInstanceOf( "eZ\\Publish\\SPI\\Persistence\\Content\\UrlAlias", $urlAlias );
        self::assertEquals( UrlAlias::LOCATION, $urlAlias->type );
        self::assertEquals( $alwaysAvailable, $urlAlias->alwaysAvailable );
        self::assertEquals( $locationId, $urlAlias->destination );
        self::assertEquals( $id, $urlAlias->id );
        self::assertFalse( $urlAlias->forward );
        self::assertFalse( $urlAlias->isCustom );
        self::assertFalse( $urlAlias->isHistory );
        self::assertEquals( $languageCodes, $urlAlias->languageCodes );
        self::assertEquals( $pathLanguageData, $urlAlias->pathLanguageData );
        self::assertEquals( $pathData, $urlAlias->pathData );
    }

    /**
     * Test for the lookup() method.
     *
     * @todo document
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::lookup
     * @_depends testLookup
     * @group history
     * @group location
     */
    public function testLookupLocationHistoryUrlAlias()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_location.php" );

        $urlAlias = $handler->lookup( "jedan/dva/tri-history" );

        self::assertEquals(
            new UrlAlias(
                array(
                    "id" => "3-5f46413bb0ba5998caef84ab1ea590e1",
                    "type" => UrlAlias::LOCATION,
                    "destination" => "316",
                    "pathData" => array(
                        array(
                            "always-available" => true,
                            "translations" => array( "cro-HR" => "jedan" )
                        ),
                        array(
                            "always-available" => false,
                            "translations" => array(
                                "cro-HR" => "dva",
                                "eng-GB" => "two",
                            )
                        ),
                        array(
                            "always-available" => false,
                            "translations" => array(
                                "cro-HR" => "tri-history",
                            )
                        ),
                    ),
                    "pathLanguageData" => array(
                        array( "always-available" => true, "language-codes" => array( "cro-HR" ) ),
                        array( "always-available" => false, "language-codes" => array( "cro-HR" ) ),
                        array( "always-available" => false, "language-codes" => array( "cro-HR" ) ),
                    ),
                    "languageCodes" => array( "cro-HR" ),
                    "alwaysAvailable" => false,
                    "isHistory" => true,
                    "isCustom" => false,
                    "forward" => false,
                )
            ),
            $urlAlias
        );
    }

    public function providerForTestLookupCustomLocationUrlAlias()
    {
        return array(
            array(
                "autogenerated-hello/everybody",
                array(
                    array(
                        "always-available" => true,
                        "translations" => array(
                            "eng-GB" => "autogenerated-hello",
                        )
                    ),
                    array(
                        "always-available" => true,
                        "translations" => array(
                            "eng-GB" => "everybody",
                        )
                    ),
                ),
                array(
                    array(
                        "always-available" => true,
                        "language-codes" => array( "eng-GB" )
                    ),
                    array(
                        "always-available" => true,
                        "language-codes" => array( "eng-GB" )
                    )
                ),
                array( "eng-GB" ),
                false,
                true,
                315,
                "2-88150d7d17390010ba6222de68bfafb5"
            ),
            array(
                "hello",
                array(
                    array(
                        "always-available" => false,
                        "translations" => array(
                            "eng-GB" => "hello",
                        )
                    )
                ),
                array(
                    array(
                        "always-available" => false,
                        "language-codes" => array( "eng-GB" )
                    ),
                ),
                array( "eng-GB" ),
                true,
                false,
                314,
                "0-5d41402abc4b2a76b9719d911017c592"
            ),
            array(
                "hello/and/goodbye",
                array(
                    array(
                        "always-available" => false,
                        "translations" => array(
                            "eng-GB" => "hello",
                        )
                    ),
                    array(
                        "always-available" => true,
                        "translations" => array(
                            "always-available" => "and",
                        )
                    ),
                    array(
                        "always-available" => false,
                        "translations" => array(
                            "eng-GB" => "goodbye",
                        )
                    ),
                ),
                array(
                    array(
                        "always-available" => false,
                        "language-codes" => array( "eng-GB" )
                    ),
                    array(
                        "always-available" => true,
                        "language-codes" => array( "always-available" )
                    ),
                    array(
                        "always-available" => false,
                        "language-codes" => array( "eng-GB" )
                    ),
                ),
                array( "eng-GB" ),
                true,
                false,
                316,
                "8-69faab6268350295550de7d587bc323d"
            ),
            array(
                "hello/everyone",
                array(
                    array(
                        "always-available" => false,
                        "translations" => array(
                            "eng-GB" => "hello",
                        )
                    ),
                    array(
                        "always-available" => false,
                        "translations" => array(
                            "eng-GB" => "everyone",
                        )
                    ),
                ),
                array(
                    array(
                        "always-available" => false,
                        "language-codes" => array( "eng-GB" )
                    ),
                    array(
                        "always-available" => false,
                        "language-codes" => array( "eng-GB" )
                    ),
                ),
                array( "eng-GB" ),
                true,
                false,
                315,
                "6-ed881bac6397ede33c0a285c9f50bb83"
            ),
            array(
                "well/ha-ha-ha",
                array(
                    array(
                        "always-available" => true,
                        "translations" => array(
                            "always-available" => "well",
                        )
                    ),
                    array(
                        "always-available" => false,
                        "translations" => array(
                            "eng-GB" => "ha-ha-ha",
                        )
                    ),
                ),
                array(
                    array(
                        "always-available" => true,
                        "language-codes" => array( "always-available" )
                    ),
                    array(
                        "always-available" => false,
                        "language-codes" => array( "eng-GB" )
                    ),
                ),
                array( "eng-GB" ),
                false,
                false,
                317,
                "10-17a197f4bbe127c368b889a67effd1b3"
            ),
        );
    }

    /**
     * Test for the lookup() method.
     *
     * Testing that UrlAlias is found and has expected state.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::lookup
     * @dataProvider providerForTestLookupCustomLocationUrlAlias
     * @_depends testLookup
     * @group location
     * @group custom
     */
    public function testLookupCustomLocationUrlAlias(
        $url,
        array $pathData,
        array $pathLanguageData,
        array $languageCodes,
        $forward,
        $alwaysAvailable,
        $destination,
        $id )
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_location_custom.php" );

        $urlAlias = $handler->lookup( $url );
        self::assertInstanceOf( "eZ\\Publish\\SPI\\Persistence\\Content\\UrlAlias", $urlAlias );
        self::assertEquals( UrlAlias::LOCATION, $urlAlias->type );
        self::assertEquals( $alwaysAvailable, $urlAlias->alwaysAvailable );
        self::assertEquals( $destination, $urlAlias->destination );
        self::assertEquals( $id, $urlAlias->id );
        self::assertEquals( $forward, $urlAlias->forward );
        self::assertTrue( $urlAlias->isCustom );
        self::assertFalse( $urlAlias->isHistory );
        self::assertEquals( $languageCodes, $urlAlias->languageCodes );
        self::assertEquals( $pathLanguageData, $urlAlias->pathLanguageData );
        self::assertEquals( $pathData, $urlAlias->pathData );
    }

    /**
     * Test for the lookup() method.
     *
     * Testing that UrlAlias is found and has expected state.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::lookup
     * @dataProvider providerForTestLookupCustomLocationUrlAlias
     * @_depends testLookup
     * @group location
     * @group custom
     */
    public function testLookupCustomLocationUrlAliasCaseCorrection(
        $url,
        array $pathData,
        array $pathLanguageData,
        array $languageCodes,
        $forward,
        $alwaysAvailable,
        $destination,
        $id )
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_location_custom.php" );

        $urlAlias = $handler->lookup( strtoupper( $url ) );

        self::assertInstanceOf( "eZ\\Publish\\SPI\\Persistence\\Content\\UrlAlias", $urlAlias );
        self::assertEquals( UrlAlias::LOCATION, $urlAlias->type );
        self::assertEquals( $alwaysAvailable, $urlAlias->alwaysAvailable );
        self::assertEquals( $destination, $urlAlias->destination );
        self::assertEquals( $id, $urlAlias->id );
        self::assertEquals( $forward, $urlAlias->forward );
        self::assertTrue( $urlAlias->isCustom );
        self::assertFalse( $urlAlias->isHistory );
        self::assertEquals( $languageCodes, $urlAlias->languageCodes );
        self::assertEquals( $pathLanguageData, $urlAlias->pathLanguageData );
        self::assertEquals( $pathData, $urlAlias->pathData );
    }

    public function providerForTestLookupVirtualUrlAlias()
    {
        return array(
            array(
                "hello/and",
                "6-be5d5d37542d75f93a87094459f76678"
            ),
            array(
                "HELLO/AND",
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
     * @dataProvider providerForTestLookupVirtualUrlAlias
     * @_depends testLookup
     * @group virtual
     */
    public function testLookupVirtualUrlAlias( $url, $id )
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_location_custom.php" );

        $urlAlias = $handler->lookup( $url );

        $this->assertVirtualUrlAliasValid( $urlAlias, $id );
    }

    public function providerForTestLookupResourceUrlAlias()
    {
        return array(
            array(
                "is-alive",
                array(
                    array(
                        "always-available" => true,
                        "translations" => array(
                            "eng-GB" => "is-alive"
                        )
                    ),
                ),
                array(
                    array(
                        "always-available" => true,
                        "language-codes" => array( "eng-GB" )
                    ),
                ),
                array( "eng-GB" ),
                true,
                true,
                "ezinfo/isalive",
                "0-d003895fa282a14c8ec3eddf23ca4ca2"
            ),
            array(
                "is-alive/then/search",
                array(
                    array(
                        "always-available" => true,
                        "translations" => array(
                            "eng-GB" => "is-alive"
                        )
                    ),
                    array(
                        "always-available" => true,
                        "translations" => array(
                            "always-available" => "then"
                        )
                    ),
                    array(
                        "always-available" => false,
                        "translations" => array(
                            "eng-GB" => "search"
                        )
                    ),
                ),
                array(
                    array(
                        "always-available" => true,
                        "language-codes" => array( "eng-GB" )
                    ),
                    array(
                        "always-available" => true,
                        "language-codes" => array( "always-available" )
                    ),
                    array(
                        "always-available" => false,
                        "language-codes" => array( "eng-GB" )
                    )
                ),
                array( "eng-GB" ),
                false,
                false,
                "content/search",
                "3-06a943c59f33a34bb5924aaf72cd2995"
            )
        );
    }

    /**
     * Test for the lookup() method.
     *
     * Testing that UrlAlias is found and has expected state.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::lookup
     * @dataProvider providerForTestLookupResourceUrlAlias
     * @_depends testLookup
     * @group resource
     */
    public function testLookupResourceUrlAlias(
        $url,
        $pathData,
        array $pathLanguageData,
        array $languageCodes,
        $forward,
        $alwaysAvailable,
        $destination,
        $id
    )
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_resource.php" );

        $urlAlias = $handler->lookup( $url );

        self::assertInstanceOf( "eZ\\Publish\\SPI\\Persistence\\Content\\UrlAlias", $urlAlias );
        self::assertEquals( UrlAlias::RESOURCE, $urlAlias->type );
        self::assertEquals( $destination, $urlAlias->destination );
        self::assertEquals( $alwaysAvailable, $urlAlias->alwaysAvailable );
        self::assertEquals( $id, $urlAlias->id );
        self::assertEquals( $forward, $urlAlias->forward );
        self::assertTrue( $urlAlias->isCustom );
        self::assertFalse( $urlAlias->isHistory );
        self::assertEquals( $languageCodes, $urlAlias->languageCodes );
        self::assertEquals( $pathLanguageData, $urlAlias->pathLanguageData );
        self::assertEquals( $pathData, $urlAlias->pathData );
    }

    /**
     * Test for the lookup() method.
     *
     * Testing that UrlAlias is found and has expected state.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::lookup
     * @dataProvider providerForTestLookupResourceUrlAlias
     * @_depends testLookup
     * @group resource
     */
    public function testLookupResourceUrlAliasCaseCorrection(
        $url,
        $pathData,
        array $pathLanguageData,
        array $languageCodes,
        $forward,
        $alwaysAvailable,
        $destination,
        $id
    )
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_resource.php" );

        $urlAlias = $handler->lookup( strtoupper( $url ) );

        self::assertInstanceOf( "eZ\\Publish\\SPI\\Persistence\\Content\\UrlAlias", $urlAlias );
        self::assertEquals( UrlAlias::RESOURCE, $urlAlias->type );
        self::assertEquals( $destination, $urlAlias->destination );
        self::assertEquals( $alwaysAvailable, $urlAlias->alwaysAvailable );
        self::assertEquals( $id, $urlAlias->id );
        self::assertEquals( $forward, $urlAlias->forward );
        self::assertTrue( $urlAlias->isCustom );
        self::assertFalse( $urlAlias->isHistory );
        self::assertEquals( $languageCodes, $urlAlias->languageCodes );
        self::assertEquals( $pathLanguageData, $urlAlias->pathLanguageData );
        self::assertEquals( $pathData, $urlAlias->pathData );
    }

    protected function assertVirtualUrlAliasValid( UrlAlias $urlAlias, $id )
    {
        self::assertInstanceOf( "eZ\\Publish\\SPI\\Persistence\\Content\\UrlAlias", $urlAlias );
        self::assertEquals( UrlAlias::VIRTUAL, $urlAlias->type );
        self::assertEquals( $id, $urlAlias->id );
        self::assertEmpty( $urlAlias->languageCodes );
        self::assertEmpty( $urlAlias->pathLanguageData );
        self::assertEmpty( $urlAlias->destination );
        self::assertTrue( $urlAlias->alwaysAvailable );
        self::assertTrue( $urlAlias->forward );
        self::assertTrue( $urlAlias->isCustom );
        self::assertFalse( $urlAlias->isHistory );
        self::assertEmpty( $urlAlias->pathData );
    }

    /**
     * Test for the listURLAliasesForLocation() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::listURLAliasesForLocation
     * @group x
     */
    public function testListURLAliasesForLocation()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_location.php" );

        $urlAliases = $handler->listURLAliasesForLocation( 315 );

        self::assertEquals(
            array(
                new UrlAlias(
                    array(
                        "id" => "2-b8a9f715dbb64fd5c56e7783c6820a61",
                        "type" => UrlAlias::LOCATION,
                        "destination" => 315,
                        "languageCodes" => array( "eng-GB" ),
                        "pathData" => array(
                            array(
                                "always-available" => true,
                                "translations" => array( "cro-HR" => "jedan" )
                            ),
                            array(
                                "always-available" => false,
                                "translations" => array(
                                    "cro-HR" => "dva",
                                    "eng-GB" => "two",
                                )
                            )
                        ),
                        "pathLanguageData" => null,
                        "alwaysAvailable" => false,
                        "isHistory" => false,
                        "isCustom" => false,
                        "forward" => false
                    )
                ),
                new UrlAlias(
                    array(
                        "id" => "2-c67ed9a09ab136fae610b6a087d82e21",
                        "type" => UrlAlias::LOCATION,
                        "destination" => 315,
                        "languageCodes" => array( "cro-HR" ),
                        "pathData" => array(
                            array(
                                "always-available" => true,
                                "translations" => array( "cro-HR" => "jedan" )
                            ),
                            array(
                                "always-available" => false,
                                "translations" => array(
                                    "cro-HR" => "dva",
                                    "eng-GB" => "two",
                                )
                            )
                        ),
                        "pathLanguageData" => null,
                        "alwaysAvailable" => false,
                        "isHistory" => false,
                        "isCustom" => false,
                        "forward" => false
                    )
                ),
            ),
            $urlAliases
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

        $handler->publishUrlAliasForLocation( 314, 2, "simple", "eng-GB", true );
        $publishedUrlAlias = $handler->lookup( "simple" );
        $handler->publishUrlAliasForLocation( 314, 2, "simple", "eng-GB", true );
        $republishedUrlAlias = $handler->lookup( "simple" );

        self::assertEquals( 2, $this->countRows() );
        self::assertEquals(
            $publishedUrlAlias,
            $republishedUrlAlias
        );
    }

    public function providerForTestPublishUrlAliasForLocationComplex()
    {
        return $this->providerForTestLookupLocationUrlAlias();
    }

    /**
     * Test for the publishUrlAliasForLocation() method.
     *
     * @todo document
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::publishUrlAliasForLocation
     * @dataProvider providerForTestPublishUrlAliasForLocationComplex
     * @_depends testLookupLocationUrlAliasFound
     * @dep_ends testPublishUrlAliasForLocation
     * @group publish
     */
    public function testPublishUrlAliasForLocationComplex(
        $url,
        $pathData,
        array $pathLanguageData,
        array $languageCodes,
        $alwaysAvailable,
        $locationId,
        $id
    )
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/publish_base.php" );

        $handler->publishUrlAliasForLocation( 314, 2, "jedan", "cro-HR", true );
        $handler->publishUrlAliasForLocation( 315, 314, "dva", "cro-HR", false );
        $handler->publishUrlAliasForLocation( 315, 314, "two", "eng-GB", false );
        $handler->publishUrlAliasForLocation( 316, 315, "tri", "cro-HR", false );
        $handler->publishUrlAliasForLocation( 316, 315, "three", "eng-GB", false );
        $handler->publishUrlAliasForLocation( 316, 315, "drei", "ger-DE", false );

        $urlAlias = $handler->lookup( $url );

        self::assertInstanceOf( "eZ\\Publish\\SPI\\Persistence\\Content\\UrlAlias", $urlAlias );
        self::assertEquals( UrlAlias::LOCATION, $urlAlias->type );
        self::assertEquals( $alwaysAvailable, $urlAlias->alwaysAvailable );
        self::assertEquals( $locationId, $urlAlias->destination );
        self::assertEquals( $id, $urlAlias->id );
        self::assertFalse( $urlAlias->forward );
        self::assertFalse( $urlAlias->isCustom );
        self::assertFalse( $urlAlias->isHistory );
        self::assertEquals( $languageCodes, $urlAlias->languageCodes );
        self::assertEquals( $pathLanguageData, $urlAlias->pathLanguageData );
        self::assertEquals( $pathData, $urlAlias->pathData );
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
    public function testPublishUrlAliasForLocationSameAliasForMultipleLanguages()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/publish_base.php" );

        $handler->publishUrlAliasForLocation( 314, 2, "jedan", "cro-HR", false );
        $urlAlias1 = $handler->lookup( "jedan" );
        $handler->publishUrlAliasForLocation( 314, 2, "jedan", "eng-GB", false );
        $urlAlias2 = $handler->lookup( "jedan" );

        self::assertEquals( 2, $this->countRows() );

        foreach ( $urlAlias2 as $propertyName => $propertyValue )
        {
            if ( $propertyName === "languageCodes" )
            {
                self::assertEquals(
                    array( "cro-HR", "eng-GB" ),
                    $urlAlias2->languageCodes
                );
            }
            elseif ( $propertyName === "pathLanguageData" )
            {
                self::assertEquals(
                    array(
                        array(
                            "always-available" => false,
                            "language-codes" => array( "cro-HR", "eng-GB" )
                        )
                    ),
                    $urlAlias2->pathLanguageData
                );
            }
            elseif ( $propertyName === "pathData" )
            {
                self::assertEquals(
                    array(
                        array(
                            "always-available" => false,
                            "translations" => array(
                                "cro-HR" => "jedan",
                                "eng-GB" => "jedan"
                            )
                        ),
                    ),
                    $urlAlias2->pathData
                );
            }
            else
            {
                self::assertEquals(
                    $urlAlias1->$propertyName,
                    $urlAlias2->$propertyName
                );
            }
        }
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
    public function testPublishUrlAliasForLocationDowngradesOldEntryToHistory()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/publish_base.php" );

        $handler->publishUrlAliasForLocation( 314, 2, "jedan", "cro-HR", false );
        $handler->publishUrlAliasForLocation( 314, 2, "dva", "cro-HR", true );

        self::assertEquals( 3, $this->countRows() );

        $newUrlAlias = $handler->lookup( "dva" );

        self::assertEquals(
            new UrlAlias(
                array(
                    "id" => "0-c67ed9a09ab136fae610b6a087d82e21",
                    "type" => 0,
                    "destination" => 314,
                    "languageCodes" => array( "cro-HR" ),
                    "pathData" => array(
                        array(
                            "always-available" => true,
                            "translations" => array(
                                "cro-HR" => "dva"
                            )
                        )
                    ),
                    "pathLanguageData" => array(
                        array(
                            "always-available" => true,
                            "language-codes" => array( "cro-HR" )
                        )
                    ),
                    "alwaysAvailable" => true,
                    "isHistory" => false,
                    "isCustom" => false,
                    "forward" => false
                )
            ),
            $newUrlAlias
        );

        $historyUrlAlias = $handler->lookup( "jedan" );

        self::assertEquals(
            new UrlAlias(
                array(
                    "id" => "0-6896260129051a949051c3847c34466f",
                    "type" => 0,
                    "destination" => 314,
                    "languageCodes" => array( "cro-HR" ),
                    "pathData" => array(
                        array(
                            "always-available" => false,
                            "translations" => array(
                                "cro-HR" => "jedan"
                            )
                        )
                    ),
                    "pathLanguageData" => array(
                        array(
                            "always-available" => false,
                            "language-codes" => array( "cro-HR" )
                        )
                    ),
                    "alwaysAvailable" => false,
                    "isHistory" => true,
                    "isCustom" => false,
                    "forward" => false
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
     * @_depends testPublishUrlAliasForLocation
     * @_depends testPublishUrlAliasForLocationSameAliasForMultipleLanguages
     * @group publish
     * @group downgrade
     */
    public function testPublishUrlAliasForLocationDowngradesOldEntryRemovesLanguage()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/publish_base.php" );

        $handler->publishUrlAliasForLocation( 314, 2, "jedan", "cro-HR", false );
        $handler->publishUrlAliasForLocation( 314, 2, "jedan", "eng-GB", false );
        $handler->publishUrlAliasForLocation( 314, 2, "dva", "eng-GB", false );

        self::assertEquals( 3, $this->countRows() );

        $urlAlias = $handler->lookup( "dva" );
        self::assertEquals(
            new UrlAlias(
                array(
                    "id" => "0-c67ed9a09ab136fae610b6a087d82e21",
                    "type" => UrlAlias::LOCATION,
                    "destination" => 314,
                    "languageCodes" => array( "eng-GB" ),
                    "pathData" => array(
                        array(
                            "always-available" => false,
                            "translations" => array(
                                "cro-HR" => "jedan",
                                "eng-GB" => "dva",
                            )
                        )
                    ),
                    "pathLanguageData" => array(
                        array(
                            "always-available" => false,
                            "language-codes" => array( "eng-GB" )
                        )
                    ),
                    "alwaysAvailable" => false,
                    "isHistory" => false,
                    "isCustom" => false,
                    "forward" => false
                )
            ),
            $urlAlias
        );

        $downgradedUrlAlias = $handler->lookup( "jedan" );
        self::assertEquals(
            new UrlAlias(
                array(
                    "id" => "0-6896260129051a949051c3847c34466f",
                    "type" => UrlAlias::LOCATION,
                    "destination" => 314,
                    "languageCodes" => array( "cro-HR" ),
                    "pathData" => array(
                        array(
                            "always-available" => false,
                            "translations" => array(
                                "cro-HR" => "jedan",
                                "eng-GB" => "dva",
                            )
                        )
                    ),
                    "pathLanguageData" => array(
                        array(
                            "always-available" => false,
                            "language-codes" => array( "cro-HR" )
                        )
                    ),
                    "alwaysAvailable" => false,
                    "isHistory" => false,
                    "isCustom" => false,
                    "forward" => false
                )
            ),
            $downgradedUrlAlias
        );
    }

    /**
     * Test for the publishUrlAliasForLocation() method.
     *
     * @todo document
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::publishUrlAliasForLocation
     * @_depends testPublishUrlAliasForLocationDowngradesOldEntryToHistory
     * @group publish
     */
    public function testPublishUrlAliasForLocationReusesHistory()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/publish_base.php" );

        $handler->publishUrlAliasForLocation( 314, 2, "jedan", "cro-HR", false );
        $urlAlias = $handler->lookup( "jedan" );
        $handler->publishUrlAliasForLocation( 314, 2, "dva", "cro-HR", true );
        $handler->publishUrlAliasForLocation( 314, 2, "jedan", "cro-HR", false );
        $urlAliasReusesHistory = $handler->lookup( "jedan" );

        self::assertEquals( 3, $this->countRows() );
        self::assertEquals(
            $urlAlias,
            $urlAliasReusesHistory
        );
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
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_reusing.php" );

        $countBeforeReusing = $this->countRows();
        $handler->publishUrlAliasForLocation( 314, 2, "custom-hello", "eng-GB", false );
        $urlAlias = $handler->lookup( "custom-hello" );

        self::assertEquals(
            $countBeforeReusing,
            $this->countRows()
        );
        self::assertFalse( $urlAlias->isCustom );
    }

    /**
     * Test for the publishUrlAliasForLocation() method.
     *
     * @todo document
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::publishUrlAliasForLocation
     */
    public function testPublishUrlAliasForLocationReusingNopElement()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_reusing.php" );

        $countBeforeReusing = $this->countRows();
        $virtualUrlAlias = $handler->lookup( "nop-element/search" );
        $handler->publishUrlAliasForLocation( 315, 2, "nop-element", "eng-GB", false );
        $publishedLocationUrlAlias = $handler->lookup( "nop-element" );

        self::assertEquals(
            $countBeforeReusing,
            $this->countRows()
        );

        self::assertInstanceOf( "eZ\\Publish\\SPI\\Persistence\\Content\\UrlAlias", $publishedLocationUrlAlias );
        self::assertEquals(
            new UrlAlias(
                array(
                    "id" => "0-de55c2fff721217cc4cb67b58dc35f85",
                    "type" => UrlAlias::LOCATION,
                    "destination" => 315,
                    "languageCodes" => array( "eng-GB" ),
                    "pathData" => array(
                        array(
                            "always-available" => false,
                            "translations" => array( "eng-GB" => "nop-element" )
                        )
                    ),
                    "pathLanguageData" => array(
                        array(
                            "always-available" => false,
                            "language-codes" => array( "eng-GB" )
                        )
                    ),
                    "alwaysAvailable" => false,
                    "isHistory" => false,
                    "isCustom" => false,
                    "forward" => false
                )
            ),
            $publishedLocationUrlAlias
        );

        $virtualUrlAliasReloaded = $handler->lookup( "nop-element/search" );
        foreach ( $virtualUrlAliasReloaded as $propertyName => $propertyValue )
        {
            if ( $propertyName === "pathLanguageData" )
            {
                self::assertEquals(
                    array(
                        array(
                            "always-available" => false,
                            "language-codes" => array( "eng-GB" )
                        ),
                        array(
                            "always-available" => false,
                            "language-codes" => array( "eng-GB" )
                        )
                    ),
                    $virtualUrlAliasReloaded->pathLanguageData
                );
            }
            elseif ( $propertyName === "pathData" )
            {
                self::assertEquals(
                    array(
                        array(
                            "always-available" => false,
                            "translations" => array( "eng-GB" => "nop-element" )
                        ),
                        array(
                            "always-available" => false,
                            "translations" => array( "eng-GB" => "search" )
                        ),
                    ),
                    $virtualUrlAliasReloaded->pathData
                );
            }
            else
            {
                self::assertEquals(
                    $virtualUrlAlias->$propertyName,
                    $virtualUrlAliasReloaded->$propertyName
                );
            }
        }
    }

    /**
     * Test for the publishUrlAliasForLocation() method.
     *
     * @todo document
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::publishUrlAliasForLocation
     * @depends testPublishUrlAliasForLocationReusingNopElement
     */
    public function testPublishUrlAliasForLocationReusingNopElementChangesCustomPath()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_reusing.php" );

        $countBeforeReusing = $this->countRows();
        $virtualUrlAlias = $handler->lookup( "nop-element/search" );
        $handler->publishUrlAliasForLocation( 315, 2, "nop-element", "eng-GB", false );
        $handler->publishUrlAliasForLocation( 315, 2, "nop-element-renamed", "eng-GB", false );
        $virtualUrlAliasChanged = $handler->lookup( "nop-element-renamed/search" );

        self::assertEquals(
            $countBeforeReusing + 1,
            $this->countRows()
        );

        foreach ( $virtualUrlAliasChanged as $propertyName => $propertyValue )
        {
            if ( $propertyName === "pathLanguageData" )
            {
                self::assertEquals(
                    array(
                        array(
                            "always-available" => false,
                            "language-codes" => array( "eng-GB" )
                        ),
                        array(
                            "always-available" => false,
                            "language-codes" => array( "eng-GB" )
                        )
                    ),
                    $virtualUrlAliasChanged->pathLanguageData
                );
            }
            elseif ( $propertyName === "pathData" )
            {
                self::assertEquals(
                    array(
                        array(
                            "always-available" => false,
                            "translations" => array( "eng-GB" => "nop-element-renamed" )
                        ),
                        array(
                            "always-available" => false,
                            "translations" => array( "eng-GB" => "search" )
                        )
                    ),
                    $virtualUrlAliasChanged->pathData
                );
            }
            else
            {
                self::assertEquals(
                    $virtualUrlAlias->$propertyName,
                    $virtualUrlAliasChanged->$propertyName
                );
            }
        }
    }

    /**
     * Test for the publishUrlAliasForLocation() method.
     *
     * @todo document
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::publishUrlAliasForLocation
     * @depends testPublishUrlAliasForLocationReusingNopElementChangesCustomPath
     */
    public function testPublishUrlAliasForLocationReusingNopElementChangesCustomPathAndCreatesHistory()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_reusing.php" );

        $handler->publishUrlAliasForLocation( 315, 2, "nop-element", "eng-GB", false );
        $handler->publishUrlAliasForLocation( 315, 2, "nop-element-renamed", "eng-GB", false );
        $virtualUrlAliasChanged = $handler->lookup( "nop-element-renamed/search" );
        $virtualUrlAliasHistory = $handler->lookup( "nop-element/search" );

        self::assertEquals(
            $virtualUrlAliasChanged,
            $virtualUrlAliasHistory
        );
    }

    /**
     * Test for the createCustomUrlAlias() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::createCustomUrlAlias
     * @group create
     * @group custom
     */
    public function testCreateCustomUrlAliasBehaviour()
    {
        $handlerMock = $this->getPartlyMockedHandler( array( "createUrlAlias" ) );

        $handlerMock->expects(
            $this->once()
        )->method(
            "createUrlAlias"
        )->with(
            $this->equalTo( "eznode:1" ),
            $this->equalTo( "path" ),
            $this->equalTo( false ),
            $this->equalTo( null ),
            $this->equalTo( false )
        )->will(
            $this->returnValue(
                new UrlAlias()
            )
        );

        $this->assertInstanceOf(
            "eZ\\Publish\\SPI\\Persistence\\Content\\UrlAlias",
            $handlerMock->createCustomUrlAlias( 1, "path" )
        );
    }

    /**
     * Test for the createGlobalUrlAlias() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::createGlobalUrlAlias
     * @group create
     * @group global
     */
    public function testCreateGlobalUrlAliasBehaviour()
    {
        $handlerMock = $this->getPartlyMockedHandler( array( "createUrlAlias" ) );

        $handlerMock->expects(
            $this->once()
        )->method(
            "createUrlAlias"
        )->with(
            $this->equalTo( "module/module" ),
            $this->equalTo( "path" ),
            $this->equalTo( false ),
            $this->equalTo( null ),
            $this->equalTo( false )
        )->will(
            $this->returnValue(
                new UrlAlias()
            )
        );

        $this->assertInstanceOf(
            "eZ\\Publish\\SPI\\Persistence\\Content\\UrlAlias",
            $handlerMock->createGlobalUrlAlias( "module/module", "path" )
        );
    }

    /**
     * Test for the createUrlAlias() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::createUrlAlias
     * @group create
     * @group custom
     */
    public function testCreateCustomUrlAlias()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/publish_base.php" );

        $path = "custom-location-alias";
        $customUrlAlias = $handler->createCustomUrlAlias(
            314,
            $path,
            false,
            "cro-HR",
            false
        );

        self::assertEquals( 2, $this->countRows() );
        self::assertEquals(
            new UrlAlias(
                array(
                    "id" => "0-" . md5( $path ),
                    "type" => UrlAlias::LOCATION,
                    "destination" => 314,
                    "languageCodes" => array( "cro-HR" ),
                    "alwaysAvailable" => false,
                    //"pathLanguageData" => array( array( "cro-HR" ) ),
                    "isHistory" => false,
                    "isCustom" => true,
                    "forward" => false
                )
            ),
            $customUrlAlias
        );
    }

    /**
     * Test for the createUrlAlias() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::createUrlAlias
     * @group create
     * @group custom
     * @todo pathData
     */
    public function testCreatedCustomUrlAliasIsLoadable()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/publish_base.php" );

        $path = "custom-location-alias";
        $customUrlAlias = $handler->createCustomUrlAlias(
            314,
            $path,
            false,
            "cro-HR",
            false
        );
        $loadedCustomUrlAlias = $handler->lookup( "custom-location-alias" );

        self::assertEquals( 2, $this->countRows() );

        foreach ( $loadedCustomUrlAlias as $propertyName => $propertyValue )
        {
            if ( $propertyName === "pathLanguageData" )
            {
                self::assertEquals(
                    array(
                        array(
                            "always-available" => false,
                            "language-codes" => array( "cro-HR" )
                        ),
                    ),
                    $loadedCustomUrlAlias->$propertyName
                );
            }
            elseif ( $propertyName === "pathData" )
            {
                self::assertEquals(
                    array(
                        array(
                            "always-available" => false,
                            "translations" => array( "cro-HR" => "custom-location-alias" )
                        ),
                    ),
                    $loadedCustomUrlAlias->$propertyName
                );
            }
            else
            {
                self::assertEquals(
                    $customUrlAlias->$propertyName,
                    $loadedCustomUrlAlias->$propertyName
                );
            }
        }
    }

    /**
     * Test for the createUrlAlias() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::createUrlAlias
     * @group create
     * @group custom
     */
    public function testCreateCustomUrlAliasWithNopElement()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/publish_base.php" );

        $path = "ribar/palunko";
        $customUrlAlias = $handler->createCustomUrlAlias(
            314,
            $path,
            false,
            "cro-HR",
            true
        );

        self::assertEquals( 3, $this->countRows() );
        self::assertEquals(
            new UrlAlias(
                array(
                    "id" => "2-" . md5( "palunko" ),
                    "type" => UrlAlias::LOCATION,
                    "destination" => 314,
                    "languageCodes" => array( "cro-HR" ),
                    "alwaysAvailable" => true,
                    //"pathLanguageData" => array(),
                    "isHistory" => false,
                    "isCustom" => true,
                    "forward" => false
                )
            ),
            $customUrlAlias
        );

        return $handler;
    }

    /**
     * Test for the createUrlAlias() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::createUrlAlias
     * @depends testCreateCustomUrlAliasWithNopElement
     * @group create
     * @group custom
     */
    public function testCreateUrlAliasWithNopElementCreatesValidNopElement( Handler $handler )
    {
        $url = "ribar";
        $urlAlias = $handler->lookup( $url );

        $this->assertVirtualUrlAliasValid(
            $urlAlias,
            "0-" . md5( $url )
        );
    }

    /**
     * Test for the createUrlAlias() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::createUrlAlias
     * @group create
     * @group custom
     */
    public function testCreateCustomUrlAliasReusesHistoryElement()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_reusing.php" );

        $countBeforeReusing = $this->countRows();
        $historyUrlAlias = $handler->lookup( "history-hello" );
        $handler->createCustomUrlAlias(
            314,
            "history-hello",
            true,
            "eng-GB",
            true
        );

        self::assertEquals(
            $countBeforeReusing,
            $this->countRows()
        );
        self::assertNotEquals(
            $historyUrlAlias,
            $handler->lookup( "history-hello" )
        );
    }

    /**
     * Test for the createUrlAlias() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::createUrlAlias
     * @group create
     * @group custom
     */
    public function testCreateCustomUrlAliasReusesNopElement()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_reusing.php" );

        $countBeforeReusing = $this->countRows();
        $handler->createCustomUrlAlias(
            314,
            "nop-element",
            true,
            "cro-HR",
            true
        );

        self::assertEquals(
            $countBeforeReusing,
            $this->countRows()
        );

        // Check that virtual alias still works as expected
        self::assertEquals(
            new UrlAlias(
                array(
                    "id" => "2-06a943c59f33a34bb5924aaf72cd2995",
                    "type" => UrlAlias::RESOURCE,
                    "destination" => "content/search",
                    "languageCodes" => array( "eng-GB" ),
                    "pathData" => array(
                        array(
                            "always-available" => true,
                            "translations" => array( "cro-HR" => "nop-element" )
                        ),
                        array(
                            "always-available" => false,
                            "translations" => array( "eng-GB" => "search" )
                        )
                    ),
                    "pathLanguageData" => array(
                        array(
                            "always-available" => true,
                            "language-codes" => array( "cro-HR" )
                        ),
                        array(
                            "always-available" => false,
                            "language-codes" => array( "eng-GB" )
                        )
                    ),
                    "alwaysAvailable" => false,
                    "isHistory" => false,
                    "isCustom" => true,
                    "forward" => false
                )
            ),
            $handler->lookup( "nop-element/search" )
        );
    }

    /**
     * Test for the createUrlAlias() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::createUrlAlias
     * @group create
     * @group custom
     */
    public function testCreateCustomUrlAliasReusesLocationElement()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_reusing.php" );

        $countBeforeReusing = $this->countRows();
        $locationUrlAlias = $handler->lookup( "autogenerated-hello" );
        $handler->createCustomUrlAlias(
            315,
            "autogenerated-hello/custom-location-alias-for-315",
            true,
            "cro-HR",
            true
        );

        self::assertEquals(
            $countBeforeReusing + 1,
            $this->countRows()
        );

        // Check that location alias still works as expected
        self::assertEquals(
            $locationUrlAlias,
            $handler->lookup( "autogenerated-hello" )
        );
    }

    /**
     * Test for the locationDeleted() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::locationDeleted
     */
    public function testLocationDeleted()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_location.php" );

        $countBeforeDeleting = $this->countRows();

        $handler->locationDeleted( 315 );

        self::assertEquals(
            $countBeforeDeleting,
            $this->countRows()
        );

        self::assertEmpty(
            $handler->listURLAliasesForLocation( 315 )
        );

        $this->assertVirtualUrlAliasValid(
            $handler->lookup( "jedan/dva" ),
            "2-c67ed9a09ab136fae610b6a087d82e21"
        );

        $this->assertVirtualUrlAliasValid(
            $handler->lookup( "jedan/two" ),
            "2-b8a9f715dbb64fd5c56e7783c6820a61"
        );

        $this->assertVirtualUrlAliasValid(
            $handler->lookup( "jedan/dva/tri" ),
            "3-d2cfe69af2d64330670e08efb2c86df7"
        );

        $this->assertVirtualUrlAliasValid(
            $handler->lookup( "jedan/dva/three" ),
            "3-35d6d33467aae9a2e3dccb4b6b027878"
        );

        $this->assertVirtualUrlAliasValid(
            $handler->lookup( "jedan/dva/drei" ),
            "3-1d8d2fd0a99802b89eb356a86e029d25"
        );

        $this->assertVirtualUrlAliasValid(
            $handler->lookup( "jedan/dva/tri-history" ),
            "3-5f46413bb0ba5998caef84ab1ea590e1"
        );
    }

    /**
     * Test for the locationDeleted() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::locationDeleted
     */
    public function testLocationDeletedRemovesCustomAliases()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture( __DIR__ . "/_fixtures/urlaliases_location_custom.php" );

        $countBeforeDeleting = $this->countRows();

        $handler->locationDeleted( 314 );

        self::assertEquals(
            $countBeforeDeleting,
            $this->countRows()
        );

        self::assertEmpty( $handler->listURLAliasesForLocation( 314 ) );

        self::assertEmpty( $handler->listURLAliasesForLocation( 314, true ) );

        $this->assertVirtualUrlAliasValid(
            $handler->lookup( "autogenerated-hello" ),
            "0-2eb35041e168cb62fe790b7555a0e90d"
        );

        $this->assertVirtualUrlAliasValid(
            $handler->lookup( "hello" ),
            "0-5d41402abc4b2a76b9719d911017c592"
        );
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
     * @return int
     */
    protected function dump()
    {
        /** @var \ezcQuerySelect $query  */
        $query = $this->dbHandler->createSelectQuery();
        $query->select(
            "*"
        )->from(
            $this->dbHandler->quoteTable( "ezurlalias_ml" )
        );

        $statement = $query->prepare();
        $statement->execute();

        var_dump( $statement->fetchAll() );
    }

    /**
     * @var \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler
     */
    protected $dbHandler;

    /**
     * @param array $methods
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getPartlyMockedHandler( array $methods )
    {
        $mock = $this->getMock(
            "eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\UrlAlias\\Handler",
            $methods,
            array(
                self::getMock( "eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\UrlAlias\\Gateway" ),
                self::getMock( "eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\UrlAlias\\Mapper" ),
                self::getMock(
                    "eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Language\\Handler",
                    array(),
                    array(),
                    '',
                    false
                ),
                self::getMock(
                    "eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Language\\MaskGenerator",
                    array(),
                    array(),
                    '',
                    false
                ),
                self::getMock(
                    "eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Search\\TransformationProcessor",
                    array(),
                    array(),
                    '',
                    false
                )
            )
        );

        return $mock;
    }

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
            $languageMaskGenerator
        );
        $mapper = new Mapper();

        return new Handler(
            $gateway,
            $mapper,
            $languageHandler,
            $languageMaskGenerator,
            $this->getProcessor()
        );
    }

    public function getProcessor()
    {
        $rules = array();
        foreach ( glob( __DIR__ . '/_fixtures/transformations/*.tr' ) as $file )
        {
            $rules[] = str_replace( self::getInstallationDir(), '', $file );
        }

        return new TransformationProcessor(
            new TransformationParser( self::getInstallationDir() ),
            new TransformationPcreCompiler( new Utf8Converter() ),
            $rules
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
