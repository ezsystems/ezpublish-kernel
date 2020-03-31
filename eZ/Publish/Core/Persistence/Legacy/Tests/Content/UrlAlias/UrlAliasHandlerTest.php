<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content;

use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway as UrlAliasGateway;
use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway as LocationGateway;
use eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Mapper;
use eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway\DoctrineDatabase;
use eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\SlugConverter;
use eZ\Publish\Core\Persistence\Legacy\Content\Gateway;
use eZ\Publish\Core\Persistence\Legacy\Content\Gateway\DoctrineDatabase as ContentGateway;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway\DoctrineDatabase as DoctrineDatabaseLocation;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\Handler as LanguageHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\Gateway\DoctrineDatabase as LanguageGateway;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\Mapper as LanguageMapper;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator as LanguageMaskGenerator;
use eZ\Publish\Core\Persistence\TransformationProcessor\DefinitionBased;
use eZ\Publish\Core\Persistence\TransformationProcessor\DefinitionBased\Parser;
use eZ\Publish\Core\Persistence\TransformationProcessor\PcreCompiler;
use eZ\Publish\Core\Persistence\Utf8Converter;
use eZ\Publish\SPI\Persistence\Content\UrlAlias;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\SPI\Persistence\TransactionHandler;

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
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_location.php');

        $urlAlias = $handler->lookup('jedan');
        self::assertInstanceOf(UrlAlias::class, $urlAlias);
    }

    /**
     * Test for the lookup() method.
     *
     * Trying to lookup non existent URL alias throws NotFoundException.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::lookup
     * @group location
     * @group virtual
     * @group resource
     */
    public function testLookupThrowsNotFoundException()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\NotFoundException::class);

        $handler = $this->getHandler();
        $handler->lookup('wooden/iron');
    }

    /**
     * Test for the lookup() method.
     *
     * Trying to lookup URL alias with exceeded path segments limit
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::lookup
     * @group location
     * @group case-correction
     */
    public function testLookupThrowsInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);

        $handler = $this->getHandler();
        $handler->lookup(str_repeat('/1', 99));
    }

    public function providerForTestLookupLocationUrlAlias()
    {
        return [
            [
                'jedan',
                [
                    [
                        'always-available' => true,
                        'translations' => [
                            'cro-HR' => 'jedan',
                        ],
                    ],
                ],
                ['cro-HR'],
                true,
                314,
                '0-6896260129051a949051c3847c34466f',
            ],
            [
                'jedan/dva',
                [
                    [
                        'always-available' => true,
                        'translations' => [
                            'cro-HR' => 'jedan',
                        ],
                    ],
                    [
                        'always-available' => false,
                        'translations' => [
                            'cro-HR' => 'dva',
                            'eng-GB' => 'two',
                        ],
                    ],
                ],
                ['cro-HR'],
                false,
                315,
                '2-c67ed9a09ab136fae610b6a087d82e21',
            ],
            [
                'jedan/two',
                [
                    [
                        'always-available' => true,
                        'translations' => [
                            'cro-HR' => 'jedan',
                        ],
                    ],
                    [
                        'always-available' => false,
                        'translations' => [
                            'cro-HR' => 'dva',
                            'eng-GB' => 'two',
                        ],
                    ],
                ],
                ['eng-GB'],
                false,
                315,
                '2-b8a9f715dbb64fd5c56e7783c6820a61',
            ],
            [
                'jedan/dva/tri',
                [
                    [
                        'always-available' => true,
                        'translations' => [
                            'cro-HR' => 'jedan',
                        ],
                    ],
                    [
                        'always-available' => false,
                        'translations' => [
                            'cro-HR' => 'dva',
                            'eng-GB' => 'two',
                        ],
                    ],
                    [
                        'always-available' => false,
                        'translations' => [
                            'cro-HR' => 'tri',
                            'eng-GB' => 'three',
                            'ger-DE' => 'drei',
                        ],
                    ],
                ],
                ['cro-HR'],
                false,
                316,
                '3-d2cfe69af2d64330670e08efb2c86df7',
            ],
            [
                'jedan/two/three',
                [
                    [
                        'always-available' => true,
                        'translations' => [
                            'cro-HR' => 'jedan',
                        ],
                    ],
                    [
                        'always-available' => false,
                        'translations' => [
                            'cro-HR' => 'dva',
                            'eng-GB' => 'two',
                        ],
                    ],
                    [
                        'always-available' => false,
                        'translations' => [
                            'cro-HR' => 'tri',
                            'eng-GB' => 'three',
                            'ger-DE' => 'drei',
                        ],
                    ],
                ],
                ['eng-GB'],
                false,
                316,
                '3-35d6d33467aae9a2e3dccb4b6b027878',
            ],
            [
                'jedan/dva/three',
                [
                    [
                        'always-available' => true,
                        'translations' => [
                            'cro-HR' => 'jedan',
                        ],
                    ],
                    [
                        'always-available' => false,
                        'translations' => [
                            'cro-HR' => 'dva',
                            'eng-GB' => 'two',
                        ],
                    ],
                    [
                        'always-available' => false,
                        'translations' => [
                            'cro-HR' => 'tri',
                            'eng-GB' => 'three',
                            'ger-DE' => 'drei',
                        ],
                    ],
                ],
                ['eng-GB'],
                false,
                316,
                '3-35d6d33467aae9a2e3dccb4b6b027878',
            ],
            [
                'jedan/two/tri',
                [
                    [
                        'always-available' => true,
                        'translations' => [
                            'cro-HR' => 'jedan',
                        ],
                    ],
                    [
                        'always-available' => false,
                        'translations' => [
                            'cro-HR' => 'dva',
                            'eng-GB' => 'two',
                        ],
                    ],
                    [
                        'always-available' => false,
                        'translations' => [
                            'cro-HR' => 'tri',
                            'eng-GB' => 'three',
                            'ger-DE' => 'drei',
                        ],
                    ],
                ],
                ['cro-HR'],
                false,
                316,
                '3-d2cfe69af2d64330670e08efb2c86df7',
            ],
            [
                'jedan/dva/drei',
                [
                    [
                        'always-available' => true,
                        'translations' => [
                            'cro-HR' => 'jedan',
                        ],
                    ],
                    [
                        'always-available' => false,
                        'translations' => [
                            'cro-HR' => 'dva',
                            'eng-GB' => 'two',
                        ],
                    ],
                    [
                        'always-available' => false,
                        'translations' => [
                            'cro-HR' => 'tri',
                            'eng-GB' => 'three',
                            'ger-DE' => 'drei',
                        ],
                    ],
                ],
                ['ger-DE'],
                false,
                316,
                '3-1d8d2fd0a99802b89eb356a86e029d25',
            ],
            [
                'jedan/two/drei',
                [
                    [
                        'always-available' => true,
                        'translations' => [
                            'cro-HR' => 'jedan',
                        ],
                    ],
                    [
                        'always-available' => false,
                        'translations' => [
                            'cro-HR' => 'dva',
                            'eng-GB' => 'two',
                        ],
                    ],
                    [
                        'always-available' => false,
                        'translations' => [
                            'cro-HR' => 'tri',
                            'eng-GB' => 'three',
                            'ger-DE' => 'drei',
                        ],
                    ],
                ],
                ['ger-DE'],
                false,
                316,
                '3-1d8d2fd0a99802b89eb356a86e029d25',
            ],
        ];
    }

    /**
     * Test for the lookup() method.
     *
     * Testing that UrlAlias is found and has expected state.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::lookup
     * @dataProvider providerForTestLookupLocationUrlAlias
     * @depends testLookup
     * @group location
     */
    public function testLookupLocationUrlAlias(
        $url,
        array $pathData,
        array $languageCodes,
        $alwaysAvailable,
        $locationId,
        $id
    ) {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_location.php');

        $urlAlias = $handler->lookup($url);

        self::assertInstanceOf(UrlAlias::class, $urlAlias);
        self::assertEquals(
            new UrlAlias(
                [
                    'id' => $id,
                    'type' => UrlAlias::LOCATION,
                    'destination' => $locationId,
                    'languageCodes' => $languageCodes,
                    'pathData' => $pathData,
                    'alwaysAvailable' => $alwaysAvailable,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
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
     * @dataProvider providerForTestLookupLocationUrlAlias
     * @depends testLookup
     * @group case-correction
     * @group location
     *
     * @todo refactor, only forward pertinent
     */
    public function testLookupLocationCaseCorrection(
        $url,
        array $pathData,
        array $languageCodes,
        $alwaysAvailable,
        $locationId,
        $id
    ) {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_location.php');

        $urlAlias = $handler->lookup(strtoupper($url));

        self::assertInstanceOf(UrlAlias::class, $urlAlias);
        self::assertEquals(
            new UrlAlias(
                [
                    'id' => $id,
                    'type' => UrlAlias::LOCATION,
                    'destination' => $locationId,
                    'languageCodes' => $languageCodes,
                    'pathData' => $pathData,
                    'alwaysAvailable' => $alwaysAvailable,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );
    }

    public function providerForTestLookupLocationMultipleLanguages()
    {
        return [
            [
                'jedan/dva',
                [
                    [
                        'always-available' => true,
                        'translations' => [
                            'cro-HR' => 'jedan',
                        ],
                    ],
                    [
                        'always-available' => false,
                        'translations' => [
                            'cro-HR' => 'dva',
                            'eng-GB' => 'dva',
                        ],
                    ],
                ],
                ['cro-HR', 'eng-GB'],
                false,
                315,
                '2-c67ed9a09ab136fae610b6a087d82e21',
            ],
            [
                'jedan/dva/tri',
                [
                    [
                        'always-available' => true,
                        'translations' => [
                            'cro-HR' => 'jedan',
                        ],
                    ],
                    [
                        'always-available' => false,
                        'translations' => [
                            'cro-HR' => 'dva',
                            'eng-GB' => 'dva',
                        ],
                    ],
                    [
                        'always-available' => false,
                        'translations' => [
                            'cro-HR' => 'tri',
                            'eng-GB' => 'three',
                        ],
                    ],
                ],
                ['cro-HR'],
                false,
                316,
                '3-d2cfe69af2d64330670e08efb2c86df7',
            ],
            [
                'jedan/dva/three',
                [
                    [
                        'always-available' => true,
                        'translations' => [
                            'cro-HR' => 'jedan',
                        ],
                    ],
                    [
                        'always-available' => false,
                        'translations' => [
                            'cro-HR' => 'dva',
                            'eng-GB' => 'dva',
                        ],
                    ],
                    [
                        'always-available' => false,
                        'translations' => [
                            'cro-HR' => 'tri',
                            'eng-GB' => 'three',
                        ],
                    ],
                ],
                ['eng-GB'],
                false,
                316,
                '3-35d6d33467aae9a2e3dccb4b6b027878',
            ],
        ];
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
        array $pathData,
        array $languageCodes,
        $alwaysAvailable,
        $locationId,
        $id
    ) {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_location_multilang.php');

        $urlAlias = $handler->lookup($url);

        self::assertInstanceOf(UrlAlias::class, $urlAlias);
        self::assertEquals(
            new UrlAlias(
                [
                    'id' => $id,
                    'type' => UrlAlias::LOCATION,
                    'destination' => $locationId,
                    'languageCodes' => $languageCodes,
                    'pathData' => $pathData,
                    'alwaysAvailable' => $alwaysAvailable,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
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
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_location.php');

        $urlAlias = $handler->lookup('jedan/dva/tri-history');

        self::assertEquals(
            $this->getHistoryAlias(),
            $urlAlias
        );
    }

    public function providerForTestLookupCustomLocationUrlAlias()
    {
        return [
            [
                'autogenerated-hello/everybody',
                [
                    [
                        'always-available' => true,
                        'translations' => [
                            'eng-GB' => 'autogenerated-hello',
                        ],
                    ],
                    [
                        'always-available' => true,
                        'translations' => [
                            'eng-GB' => 'everybody',
                        ],
                    ],
                ],
                ['eng-GB'],
                false,
                true,
                315,
                '2-88150d7d17390010ba6222de68bfafb5',
            ],
            [
                'hello',
                [
                    [
                        'always-available' => false,
                        'translations' => [
                            'eng-GB' => 'hello',
                        ],
                    ],
                ],
                ['eng-GB'],
                true,
                false,
                314,
                '0-5d41402abc4b2a76b9719d911017c592',
            ],
            [
                'hello/and/goodbye',
                [
                    [
                        'always-available' => false,
                        'translations' => [
                            'eng-GB' => 'hello',
                        ],
                    ],
                    [
                        'always-available' => true,
                        'translations' => [
                            'always-available' => 'and',
                        ],
                    ],
                    [
                        'always-available' => false,
                        'translations' => [
                            'eng-GB' => 'goodbye',
                        ],
                    ],
                ],
                ['eng-GB'],
                true,
                false,
                316,
                '8-69faab6268350295550de7d587bc323d',
            ],
            [
                'hello/everyone',
                [
                    [
                        'always-available' => false,
                        'translations' => [
                            'eng-GB' => 'hello',
                        ],
                    ],
                    [
                        'always-available' => false,
                        'translations' => [
                            'eng-GB' => 'everyone',
                        ],
                    ],
                ],
                ['eng-GB'],
                true,
                false,
                315,
                '6-ed881bac6397ede33c0a285c9f50bb83',
            ],
            [
                'well/ha-ha-ha',
                [
                    [
                        'always-available' => true,
                        'translations' => [
                            'always-available' => 'well',
                        ],
                    ],
                    [
                        'always-available' => false,
                        'translations' => [
                            'eng-GB' => 'ha-ha-ha',
                        ],
                    ],
                ],
                ['eng-GB'],
                false,
                false,
                317,
                '10-17a197f4bbe127c368b889a67effd1b3',
            ],
        ];
    }

    /**
     * Test for the lookup() method.
     *
     * Testing that UrlAlias is found and has expected state.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::lookup
     * @dataProvider providerForTestLookupCustomLocationUrlAlias
     * @depends testLookup
     * @group location
     * @group custom
     */
    public function testLookupCustomLocationUrlAlias(
        $url,
        array $pathData,
        array $languageCodes,
        $forward,
        $alwaysAvailable,
        $destination,
        $id
    ) {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_location_custom.php');

        $urlAlias = $handler->lookup($url);
        self::assertInstanceOf(UrlAlias::class, $urlAlias);
        self::assertEquals(
            new UrlAlias(
                [
                    'id' => $id,
                    'type' => UrlAlias::LOCATION,
                    'destination' => $destination,
                    'languageCodes' => $languageCodes,
                    'pathData' => $pathData,
                    'alwaysAvailable' => $alwaysAvailable,
                    'isHistory' => false,
                    'isCustom' => true,
                    'forward' => $forward,
                ]
            ),
            $urlAlias
        );
    }

    /**
     * Test for the lookup() method.
     *
     * Testing that UrlAlias is found and has expected state.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::lookup
     * @dataProvider providerForTestLookupCustomLocationUrlAlias
     * @depends testLookup
     * @group location
     * @group custom
     */
    public function testLookupCustomLocationUrlAliasCaseCorrection(
        $url,
        array $pathData,
        array $languageCodes,
        $forward,
        $alwaysAvailable,
        $destination,
        $id
    ) {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_location_custom.php');

        $urlAlias = $handler->lookup(strtoupper($url));

        self::assertInstanceOf(UrlAlias::class, $urlAlias);
        self::assertEquals(
            new UrlAlias(
                [
                    'id' => $id,
                    'type' => UrlAlias::LOCATION,
                    'destination' => $destination,
                    'languageCodes' => $languageCodes,
                    'pathData' => $pathData,
                    'alwaysAvailable' => $alwaysAvailable,
                    'isHistory' => false,
                    'isCustom' => true,
                    'forward' => $forward,
                ]
            ),
            $urlAlias
        );
    }

    public function providerForTestLookupVirtualUrlAlias()
    {
        return [
            [
                'hello/and',
                '6-be5d5d37542d75f93a87094459f76678',
            ],
            [
                'HELLO/AND',
                '6-be5d5d37542d75f93a87094459f76678',
            ],
        ];
    }

    /**
     * Test for the lookup() method.
     *
     * Testing that NOP action redirects to site root.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::lookup
     * @dataProvider providerForTestLookupVirtualUrlAlias
     * @depends testLookup
     * @group virtual
     */
    public function testLookupVirtualUrlAlias($url, $id)
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_location_custom.php');

        $urlAlias = $handler->lookup($url);

        $this->assertVirtualUrlAliasValid($urlAlias, $id);
    }

    public function providerForTestLookupResourceUrlAlias()
    {
        return [
            [
                'is-alive',
                [
                    [
                        'always-available' => true,
                        'translations' => [
                            'eng-GB' => 'is-alive',
                        ],
                    ],
                ],
                ['eng-GB'],
                true,
                true,
                'ezinfo/isalive',
                '0-d003895fa282a14c8ec3eddf23ca4ca2',
            ],
            [
                'is-alive/then/search',
                [
                    [
                        'always-available' => true,
                        'translations' => [
                            'eng-GB' => 'is-alive',
                        ],
                    ],
                    [
                        'always-available' => true,
                        'translations' => [
                            'always-available' => 'then',
                        ],
                    ],
                    [
                        'always-available' => false,
                        'translations' => [
                            'cro-HR' => 'search',
                        ],
                    ],
                ],
                ['cro-HR'],
                false,
                false,
                'content/search',
                '3-06a943c59f33a34bb5924aaf72cd2995',
            ],
        ];
    }

    /**
     * Test for the lookup() method.
     *
     * Testing that UrlAlias is found and has expected state.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::lookup
     * @dataProvider providerForTestLookupResourceUrlAlias
     * @depends testLookup
     * @group resource
     */
    public function testLookupResourceUrlAlias(
        $url,
        $pathData,
        array $languageCodes,
        $forward,
        $alwaysAvailable,
        $destination,
        $id
    ) {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_resource.php');

        $urlAlias = $handler->lookup($url);

        self::assertInstanceOf(UrlAlias::class, $urlAlias);
        self::assertEquals(
            new UrlAlias(
                [
                    'id' => $id,
                    'type' => UrlAlias::RESOURCE,
                    'destination' => $destination,
                    'languageCodes' => $languageCodes,
                    'pathData' => $pathData,
                    'alwaysAvailable' => $alwaysAvailable,
                    'isHistory' => false,
                    'isCustom' => true,
                    'forward' => $forward,
                ]
            ),
            $urlAlias
        );
    }

    /**
     * Test for the lookup() method.
     *
     * Testing that UrlAlias is found and has expected state.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::lookup
     * @dataProvider providerForTestLookupResourceUrlAlias
     * @depends testLookup
     * @group resource
     */
    public function testLookupResourceUrlAliasCaseInsensitive(
        $url,
        $pathData,
        array $languageCodes,
        $forward,
        $alwaysAvailable,
        $destination,
        $id
    ) {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_resource.php');

        $urlAlias = $handler->lookup(strtoupper($url));

        self::assertInstanceOf(UrlAlias::class, $urlAlias);
        self::assertEquals(
            new UrlAlias(
                [
                    'id' => $id,
                    'type' => UrlAlias::RESOURCE,
                    'destination' => $destination,
                    'languageCodes' => $languageCodes,
                    'pathData' => $pathData,
                    'alwaysAvailable' => $alwaysAvailable,
                    'isHistory' => false,
                    'isCustom' => true,
                    'forward' => $forward,
                ]
            ),
            $urlAlias
        );
    }

    /**
     * Test for the lookup() method with uppercase utf8 characters.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::lookup
     * @depends testLookup
     */
    public function testLookupUppercaseIri()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_location_iri.php');

        $urlAlias = $handler->lookup('ŒÄ');
        self::assertInstanceOf(UrlAlias::class, $urlAlias);
    }

    protected function assertVirtualUrlAliasValid(UrlAlias $urlAlias, $id)
    {
        self::assertInstanceOf(UrlAlias::class, $urlAlias);
        self::assertEquals($id, $urlAlias->id);
        self::assertEquals(UrlAlias::VIRTUAL, $urlAlias->type);
        /*self::assertEquals(
            new UrlAlias(
                array(
                    "id" => $id,
                    "type" => UrlAlias::VIRTUAL,
                    "destination" => null,
                    "languageCodes" => array(),
                    "pathData" => null,
                    "alwaysAvailable" => true,
                    "isHistory" => true,
                    "isCustom" => false,
                    "forward" => false
                )
            ),
            $urlAlias
        );*/
    }

    /**
     * Test for the listURLAliasesForLocation() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::listURLAliasesForLocation
     */
    public function testListURLAliasesForLocation()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_location.php');

        $urlAliases = $handler->listURLAliasesForLocation(315);

        self::assertEquals(
            [
                new UrlAlias(
                    [
                        'id' => '2-b8a9f715dbb64fd5c56e7783c6820a61',
                        'type' => UrlAlias::LOCATION,
                        'destination' => 315,
                        'languageCodes' => ['eng-GB'],
                        'pathData' => [
                            [
                                'always-available' => true,
                                'translations' => ['cro-HR' => 'jedan'],
                            ],
                            [
                                'always-available' => false,
                                'translations' => [
                                    'cro-HR' => 'dva',
                                    'eng-GB' => 'two',
                                ],
                            ],
                        ],
                        'alwaysAvailable' => false,
                        'isHistory' => false,
                        'isCustom' => false,
                        'forward' => false,
                    ]
                ),
                new UrlAlias(
                    [
                        'id' => '2-c67ed9a09ab136fae610b6a087d82e21',
                        'type' => UrlAlias::LOCATION,
                        'destination' => 315,
                        'languageCodes' => ['cro-HR'],
                        'pathData' => [
                            [
                                'always-available' => true,
                                'translations' => ['cro-HR' => 'jedan'],
                            ],
                            [
                                'always-available' => false,
                                'translations' => [
                                    'cro-HR' => 'dva',
                                    'eng-GB' => 'two',
                                ],
                            ],
                        ],
                        'alwaysAvailable' => false,
                        'isHistory' => false,
                        'isCustom' => false,
                        'forward' => false,
                    ]
                ),
            ],
            $urlAliases
        );
    }

    /**
     * Test for the publishUrlAliasForLocation() method.
     *
     * @todo document
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::publishUrlAliasForLocation
     * @depends testLookupLocationUrlAlias
     * @group publish
     */
    public function testPublishUrlAliasForLocation()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/publish_base.php');

        $handler->publishUrlAliasForLocation(314, 2, 'simple', 'eng-GB', true);
        $publishedUrlAlias = $handler->lookup('simple');

        self::assertEquals(4, $this->countRows());
        self::assertEquals(
            new UrlAlias(
                [
                    'id' => '0-' . md5('simple'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 314,
                    'languageCodes' => ['eng-GB'],
                    'pathData' => [
                        [
                            'always-available' => true,
                            'translations' => [
                                'eng-GB' => 'simple',
                                'cro-HR' => 'path314',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => true,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
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
     * @depends testPublishUrlAliasForLocation
     * @group publish
     */
    public function testPublishUrlAliasForLocationRepublish()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/publish_base.php');

        $handler->publishUrlAliasForLocation(314, 2, 'simple', 'eng-GB', true);
        $publishedUrlAlias = $handler->lookup('simple');
        $handler->publishUrlAliasForLocation(314, 2, 'simple', 'eng-GB', true);
        $republishedUrlAlias = $handler->lookup('simple');

        self::assertEquals(4, $this->countRows());
        self::assertEquals(
            $publishedUrlAlias,
            $republishedUrlAlias
        );
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
    public function testPublishUrlAliasCreatesUniqueAlias()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/publish_base.php');

        $handler->publishUrlAliasForLocation(314, 2, 'simple', 'eng-GB', true);
        $handler->publishUrlAliasForLocation(315, 2, 'simple', 'eng-GB', true);
        self::assertEquals(5, $this->countRows());

        $urlAlias = $handler->lookup('simple2');
        self::assertEquals(
            new UrlAlias(
                [
                    'id' => '0-' . md5('simple2'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 315,
                    'languageCodes' => ['eng-GB'],
                    'pathData' => [
                        [
                            'always-available' => true,
                            'translations' => [
                                'eng-GB' => 'simple2',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => true,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );
    }

    /**
     * @return array
     */
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
     * @depends testPublishUrlAliasForLocation
     * @group publish
     */
    public function testPublishUrlAliasForLocationComplex(
        $url,
        $pathData,
        array $languageCodes,
        $alwaysAvailable,
        $locationId,
        $id
    ) {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/publish_base.php');

        $handler->publishUrlAliasForLocation(314, 2, 'jedan', 'cro-HR', true);
        $handler->publishUrlAliasForLocation(315, 314, 'dva', 'cro-HR', false);
        $handler->publishUrlAliasForLocation(315, 314, 'two', 'eng-GB', false);
        $handler->publishUrlAliasForLocation(316, 315, 'tri', 'cro-HR', false);
        $handler->publishUrlAliasForLocation(316, 315, 'three', 'eng-GB', false);
        $handler->publishUrlAliasForLocation(316, 315, 'drei', 'ger-DE', false);

        $urlAlias = $handler->lookup($url);

        self::assertInstanceOf(UrlAlias::class, $urlAlias);
        self::assertEquals(
            new UrlAlias(
                [
                    'id' => $id,
                    'type' => UrlAlias::LOCATION,
                    'destination' => $locationId,
                    'languageCodes' => $languageCodes,
                    'pathData' => $pathData,
                    'alwaysAvailable' => $alwaysAvailable,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );
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
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/publish_base.php');

        $handler->publishUrlAliasForLocation(314, 2, 'jedan', 'cro-HR', false);
        $urlAlias1 = $handler->lookup('jedan');
        $handler->publishUrlAliasForLocation(314, 2, 'jedan', 'eng-GB', false);
        $urlAlias2 = $handler->lookup('jedan');

        self::assertEquals(4, $this->countRows());

        foreach ($urlAlias2 as $propertyName => $propertyValue) {
            if ($propertyName === 'languageCodes') {
                self::assertEquals(
                    ['cro-HR', 'eng-GB'],
                    $urlAlias2->languageCodes
                );
            } elseif ($propertyName === 'pathData') {
                self::assertEquals(
                    [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'jedan',
                                'eng-GB' => 'jedan',
                            ],
                        ],
                    ],
                    $urlAlias2->pathData
                );
            } else {
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
     * @depends testPublishUrlAliasForLocation
     * @group publish
     */
    public function testPublishUrlAliasForLocationDowngradesOldEntryToHistory()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/publish_base.php');

        $handler->publishUrlAliasForLocation(314, 2, 'jedan', 'cro-HR', false);
        $handler->publishUrlAliasForLocation(314, 2, 'dva', 'cro-HR', true);

        self::assertEquals(5, $this->countRows());

        $newUrlAlias = $handler->lookup('dva');

        self::assertEquals(
            new UrlAlias(
                [
                    'id' => '0-c67ed9a09ab136fae610b6a087d82e21',
                    'type' => 0,
                    'destination' => 314,
                    'languageCodes' => ['cro-HR'],
                    'pathData' => [
                        [
                            'always-available' => true,
                            'translations' => [
                                'cro-HR' => 'dva',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => true,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $newUrlAlias
        );

        $historyUrlAlias = $handler->lookup('jedan');

        self::assertEquals(
            new UrlAlias(
                [
                    'id' => '0-6896260129051a949051c3847c34466f',
                    'type' => 0,
                    'destination' => 314,
                    'languageCodes' => ['cro-HR'],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'jedan',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => true,
                    'isCustom' => false,
                    'forward' => false,
                ]
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
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/publish_base.php');

        $handler->publishUrlAliasForLocation(314, 2, 'jedan', 'cro-HR');
        $handler->publishUrlAliasForLocation(314, 2, 'jedan', 'eng-GB');
        $handler->publishUrlAliasForLocation(314, 2, 'dva', 'eng-GB');

        self::assertEquals(5, $this->countRows());

        $urlAlias = $handler->lookup('dva');
        self::assertEquals(
            new UrlAlias(
                [
                    'id' => '0-c67ed9a09ab136fae610b6a087d82e21',
                    'type' => UrlAlias::LOCATION,
                    'destination' => 314,
                    'languageCodes' => ['eng-GB'],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'jedan',
                                'eng-GB' => 'dva',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $downgradedUrlAlias = $handler->lookup('jedan');
        self::assertEquals(
            new UrlAlias(
                [
                    'id' => '0-6896260129051a949051c3847c34466f',
                    'type' => UrlAlias::LOCATION,
                    'destination' => 314,
                    'languageCodes' => ['cro-HR'],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'jedan',
                                'eng-GB' => 'dva',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
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
     * @depends testPublishUrlAliasForLocation
     * @depends testPublishUrlAliasForLocationDowngradesOldEntryToHistory
     * @group publish
     */
    public function testPublishUrlAliasForLocationReusesHistory()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/publish_base.php');

        $handler->publishUrlAliasForLocation(314, 2, 'jedan', 'cro-HR');
        $urlAlias = $handler->lookup('jedan');
        $handler->publishUrlAliasForLocation(314, 2, 'dva', 'cro-HR');
        $countBeforeReusing = $this->countRows();
        $handler->publishUrlAliasForLocation(314, 2, 'jedan', 'cro-HR');
        $urlAliasReusesHistory = $handler->lookup('jedan');

        self::assertEquals(
            $countBeforeReusing,
            $this->countRows()
        );

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
     * @depends testPublishUrlAliasForLocation
     * @depends testPublishUrlAliasForLocationDowngradesOldEntryToHistory
     * @group publish
     */
    public function testPublishUrlAliasForLocationReusesHistoryOfDifferentLanguage()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/publish_base.php');

        $handler->publishUrlAliasForLocation(314, 2, 'jedan', 'cro-HR');
        $handler->publishUrlAliasForLocation(314, 2, 'one-history', 'eng-GB');
        $handler->publishUrlAliasForLocation(314, 2, 'one-new', 'eng-GB');
        $countBeforeReusing = $this->countRows();
        $handler->publishUrlAliasForLocation(314, 2, 'one-history', 'cro-HR');
        $urlAliasReusesHistory = $handler->lookup('one-history');

        self::assertEquals(
            $countBeforeReusing,
            $this->countRows()
        );

        self::assertEquals(
            new UrlAlias(
                [
                    'id' => '0-' . md5('one-history'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 314,
                    'languageCodes' => ['cro-HR'],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'one-history',
                                'eng-GB' => 'one-new',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAliasReusesHistory
        );
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
    public function testPublishUrlAliasForLocationReusesCustomAlias()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_reusing.php');

        $countBeforeReusing = $this->countRows();
        $handler->publishUrlAliasForLocation(314, 2, 'custom-hello', 'eng-GB', false);
        $urlAlias = $handler->lookup('custom-hello');

        self::assertEquals(
            $countBeforeReusing,
            $this->countRows()
        );
        self::assertFalse($urlAlias->isCustom);
    }

    /**
     * Test for the publishUrlAliasForLocation() method.
     *
     * @todo document
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::publishUrlAliasForLocation
     * @depends testPublishUrlAliasForLocation
     */
    public function testPublishUrlAliasForLocationReusingNopElement()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_reusing.php');

        $countBeforeReusing = $this->countRows();
        $virtualUrlAlias = $handler->lookup('nop-element/search');
        $handler->publishUrlAliasForLocation(315, 2, 'nop-element', 'eng-GB', false);
        $publishedLocationUrlAlias = $handler->lookup('nop-element');

        self::assertEquals(
            $countBeforeReusing,
            $this->countRows()
        );

        self::assertInstanceOf(UrlAlias::class, $publishedLocationUrlAlias);
        self::assertEquals(
            new UrlAlias(
                [
                    'id' => '0-de55c2fff721217cc4cb67b58dc35f85',
                    'type' => UrlAlias::LOCATION,
                    'destination' => 315,
                    'languageCodes' => ['eng-GB'],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => ['eng-GB' => 'nop-element'],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $publishedLocationUrlAlias
        );

        $virtualUrlAliasReloaded = $handler->lookup('nop-element/search');
        foreach ($virtualUrlAliasReloaded as $propertyName => $propertyValue) {
            if ($propertyName === 'pathData') {
                self::assertEquals(
                    [
                        [
                            'always-available' => false,
                            'translations' => ['eng-GB' => 'nop-element'],
                        ],
                        [
                            'always-available' => false,
                            'translations' => ['eng-GB' => 'search'],
                        ],
                    ],
                    $virtualUrlAliasReloaded->pathData
                );
            } else {
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
     * @depends testPublishUrlAliasForLocation
     * @depends testPublishUrlAliasForLocationReusingNopElement
     */
    public function testPublishUrlAliasForLocationReusingNopElementChangesCustomPath()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_reusing.php');

        $countBeforeReusing = $this->countRows();
        $virtualUrlAlias = $handler->lookup('nop-element/search');
        $handler->publishUrlAliasForLocation(315, 2, 'nop-element', 'eng-GB', false);
        $handler->publishUrlAliasForLocation(315, 2, 'nop-element-renamed', 'eng-GB', false);
        $virtualUrlAliasChanged = $handler->lookup('nop-element-renamed/search');

        self::assertEquals(
            $countBeforeReusing + 1,
            $this->countRows()
        );

        foreach ($virtualUrlAliasChanged as $propertyName => $propertyValue) {
            if ($propertyName === 'pathData') {
                self::assertEquals(
                    [
                        [
                            'always-available' => false,
                            'translations' => ['eng-GB' => 'nop-element-renamed'],
                        ],
                        [
                            'always-available' => false,
                            'translations' => ['eng-GB' => 'search'],
                        ],
                    ],
                    $virtualUrlAliasChanged->pathData
                );
            } else {
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
     * @depends testPublishUrlAliasForLocation
     * @depends testPublishUrlAliasForLocationReusingNopElementChangesCustomPath
     */
    public function testPublishUrlAliasForLocationReusingNopElementChangesCustomPathAndCreatesHistory()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_reusing.php');

        $handler->publishUrlAliasForLocation(315, 2, 'nop-element', 'eng-GB', false);
        $handler->publishUrlAliasForLocation(315, 2, 'nop-element-renamed', 'eng-GB', false);

        $customUrlAliasChanged = $handler->lookup('nop-element-renamed/search');
        $customUrlAliasHistory = $handler->lookup('nop-element/search');

        self::assertTrue($customUrlAliasHistory->isHistory);
        $customUrlAliasHistory->isHistory = false;
        self::assertEquals(
            $customUrlAliasChanged,
            $customUrlAliasHistory
        );
    }

    /**
     * Test for the publishUrlAliasForLocation() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::publishUrlAliasForLocation
     */
    public function testPublishUrlAliasForLocationUpdatesLocationPathIdentificationString()
    {
        $handler = $this->getHandler();
        $locationGateway = $this->getLocationGateway();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/publish_base.php');

        // Publishes the alias indicating that language is main, triggering updating of path_identification_string
        $handler->publishUrlAliasForLocation(316, 315, 'TEST TEST TEST', 'eng-GB', false, true);

        $locationData = $locationGateway->getBasicNodeData(316);

        self::assertEquals('path314/path315/test_test_test', $locationData['path_identification_string']);
    }

    /**
     * Test for the publishUrlAliasForLocation() method.
     *
     * @group cleanup
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::publishUrlAliasForLocation
     */
    public function testPublishUrlAliasReuseNopCleanupCustomAliasIsDestroyed()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_cleanup_nop.php');

        $handler->lookup('nop-element/search');
        $handler->publishUrlAliasForLocation(314, 2, 'nop-element', 'cro-HR', false);

        $urlAlias = $handler->lookup('jedan');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '0-' . md5('jedan'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 314,
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => ['cro-HR' => 'jedan'],
                        ],
                    ],
                    'languageCodes' => ['cro-HR'],
                    'alwaysAvailable' => false,
                    'isHistory' => true,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('nop-element');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '0-' . md5('nop-element'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 314,
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'nop-element',
                                'eng-GB' => 'dva',
                            ],
                        ],
                    ],
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        try {
            $handler->lookup('nop-element/search');
            $this->fail('Custom alias is not destroyed');
        } catch (NotFoundException $e) {
            // Custom alias is destroyed by reusing NOP entry with existing autogenerated alias
            // on the same level (that means link and ID are updated to the existing alias ID,
            // so custom alias children entries are no longer properly linked (parent-link))
        }
    }

    /**
     * Test for the publishUrlAliasForLocation() method.
     *
     * @group cleanup
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::publishUrlAliasForLocation
     */
    public function testPublishUrlAliasReuseHistoryCleanup()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_cleanup_history.php');

        $handler->publishUrlAliasForLocation(314, 2, 'tri', 'cro-HR', false);

        $urlAlias = $handler->lookup('jedan');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '0-' . md5('jedan'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 314,
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => ['cro-HR' => 'jedan'],
                        ],
                    ],
                    'languageCodes' => ['cro-HR'],
                    'alwaysAvailable' => false,
                    'isHistory' => true,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('tri');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '0-' . md5('tri'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 314,
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'tri',
                                'eng-GB' => 'dva',
                            ],
                        ],
                    ],
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );
    }

    /**
     * Test for the publishUrlAliasForLocation() method.
     *
     * @group cleanup
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::publishUrlAliasForLocation
     */
    public function testPublishUrlAliasReuseAutogeneratedCleanup()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_cleanup_reusing.php');

        $handler->publishUrlAliasForLocation(314, 2, 'dva', 'cro-HR', false);

        $urlAlias = $handler->lookup('jedan');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '0-' . md5('jedan'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 314,
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => ['cro-HR' => 'jedan'],
                        ],
                    ],
                    'languageCodes' => ['cro-HR'],
                    'alwaysAvailable' => false,
                    'isHistory' => true,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('dva');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '0-' . md5('dva'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 314,
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'dva',
                                'eng-GB' => 'dva',
                            ],
                        ],
                    ],
                    'languageCodes' => [
                        'cro-HR',
                        'eng-GB',
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
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
        $handlerMock = $this->getPartlyMockedHandler(['createUrlAlias']);

        $handlerMock->expects(
            $this->once()
        )->method(
            'createUrlAlias'
        )->with(
            $this->equalTo('eznode:1'),
            $this->equalTo('path'),
            $this->equalTo(false),
            $this->equalTo(null),
            $this->equalTo(false)
        )->will(
            $this->returnValue(
                new UrlAlias()
            )
        );

        $this->assertInstanceOf(
            UrlAlias::class,
            $handlerMock->createCustomUrlAlias(1, 'path')
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
        $handlerMock = $this->getPartlyMockedHandler(['createUrlAlias']);

        $handlerMock->expects(
            $this->once()
        )->method(
            'createUrlAlias'
        )->with(
            $this->equalTo('module/module'),
            $this->equalTo('path'),
            $this->equalTo(false),
            $this->equalTo(null),
            $this->equalTo(false)
        )->will(
            $this->returnValue(
                new UrlAlias()
            )
        );

        $this->assertInstanceOf(
            UrlAlias::class,
            $handlerMock->createGlobalUrlAlias('module/module', 'path')
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
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/publish_base.php');

        $path = 'custom-location-alias';
        $customUrlAlias = $handler->createCustomUrlAlias(
            314,
            $path,
            false,
            'cro-HR',
            false
        );

        self::assertEquals(4, $this->countRows());
        self::assertEquals(
            new UrlAlias(
                [
                    'id' => '0-' . md5($path),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 314,
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'custom-location-alias',
                            ],
                        ],
                    ],
                    'languageCodes' => ['cro-HR'],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => true,
                    'forward' => false,
                ]
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
     */
    public function testCreateCustomUrlAliasWithNonameParts()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/publish_base.php');

        $path = 'there-is-a//custom-location-alias//here';
        $customUrlAlias = $handler->createCustomUrlAlias(
            314,
            $path,
            false,
            'cro-HR',
            false
        );

        self::assertEquals(8, $this->countRows());

        self::assertEquals(
            new UrlAlias(
                [
                    'id' => '7-' . md5('here'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 314,
                    'pathData' => [
                        [
                            'always-available' => true,
                            'translations' => [
                                'always-available' => 'there-is-a',
                            ],
                        ],
                        [
                            'always-available' => true,
                            'translations' => [
                                'always-available' => 'noname2',
                            ],
                        ],
                        [
                            'always-available' => true,
                            'translations' => [
                                'always-available' => 'custom-location-alias',
                            ],
                        ],
                        [
                            'always-available' => true,
                            'translations' => [
                                'always-available' => 'noname4',
                            ],
                        ],
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'here',
                            ],
                        ],
                    ],
                    'languageCodes' => ['cro-HR'],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => true,
                    'forward' => false,
                ]
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
     *
     * @todo pathData
     */
    public function testCreatedCustomUrlAliasIsLoadable()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/publish_base.php');

        $path = 'custom-location-alias';
        $customUrlAlias = $handler->createCustomUrlAlias(
            314,
            $path,
            false,
            'cro-HR',
            false
        );
        $loadedCustomUrlAlias = $handler->lookup($path);

        self::assertEquals(4, $this->countRows());

        foreach ($loadedCustomUrlAlias as $propertyName => $propertyValue) {
            if ($propertyName === 'pathData') {
                self::assertEquals(
                    [
                        [
                            'always-available' => false,
                            'translations' => ['cro-HR' => $path],
                        ],
                    ],
                    $loadedCustomUrlAlias->$propertyName
                );
            } else {
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
    public function testCreateCustomUrlAliasWithNopElement(): void
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/publish_base.php');

        $path = 'ribar/palunko';
        $customUrlAlias = $handler->createCustomUrlAlias(
            314,
            $path,
            false,
            'cro-HR',
            true
        );

        self::assertEquals(5, $this->countRows());
        self::assertEquals(
            new UrlAlias(
                [
                    'id' => '4-' . md5('palunko'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 314,
                    'pathData' => [
                        [
                            'always-available' => true,
                            'translations' => [
                                'always-available' => 'ribar',
                            ],
                        ],
                        [
                            'always-available' => true,
                            'translations' => [
                                'cro-HR' => 'palunko',
                            ],
                        ],
                    ],
                    'languageCodes' => ['cro-HR'],
                    'alwaysAvailable' => true,
                    'isHistory' => false,
                    'isCustom' => true,
                    'forward' => false,
                ]
            ),
            $customUrlAlias
        );

        // test that valid NOP element has been created
        $url = 'ribar';
        $urlAlias = $handler->lookup($url);

        $this->assertVirtualUrlAliasValid(
            $urlAlias,
            '0-' . md5($url)
        );
    }

    /**
     * Test for the createUrlAlias() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::createUrlAlias
     * @group create
     * @group custom
     */
    public function testCreateCustomUrlAliasReusesHistory()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_reusing.php');

        $countBeforeReusing = $this->countRows();
        $handler->createCustomUrlAlias(
            314,
            'history-hello',
            true,
            'eng-GB',
            true
        );

        self::assertEquals(
            $countBeforeReusing,
            $this->countRows()
        );
        self::assertEquals(
            new UrlAlias(
                [
                    'id' => '0-da94285592c46d4396d3ca6904a4aa8f',
                    'type' => UrlAlias::LOCATION,
                    'destination' => 314,
                    'languageCodes' => ['eng-GB'],
                    'pathData' => [
                        [
                            'always-available' => true,
                            'translations' => ['eng-GB' => 'history-hello'],
                        ],
                    ],
                    'alwaysAvailable' => true,
                    'isHistory' => false,
                    'isCustom' => true,
                    'forward' => true,
                ]
            ),
            $handler->lookup('history-hello')
        );
    }

    /**
     * Test for the createUrlAlias() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::createUrlAlias
     * @group create
     * @group custom
     */
    public function testCreateCustomUrlAliasAddLanguage()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/publish_base.php');

        $countBeforeReusing = $this->countRows();
        $handler->createCustomUrlAlias(
            314,
            'path314',
            false,
            'eng-GB',
            true
        );

        self::assertEquals(
            $countBeforeReusing,
            $this->countRows()
        );
        self::assertEquals(
            new UrlAlias(
                [
                    'id' => '0-fdbbfa1e24e78ef56cb16ba4482c7771',
                    'type' => UrlAlias::LOCATION,
                    'destination' => '314',
                    'languageCodes' => ['cro-HR', 'eng-GB'],
                    'pathData' => [
                        [
                            'always-available' => true,
                            'translations' => [
                                'cro-HR' => 'path314',
                                'eng-GB' => 'path314',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => true,
                    'isHistory' => false,
                    'isCustom' => true,
                    'forward' => false,
                ]
            ),
            $handler->lookup('path314')
        );
    }

    /**
     * Test for the createUrlAlias() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::createUrlAlias
     * @group create
     * @group custom
     */
    public function testCreateCustomUrlAliasReusesHistoryOfDifferentLanguage()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_reusing.php');

        $countBeforeReusing = $this->countRows();
        $handler->createCustomUrlAlias(
            314,
            'history-hello',
            true,
            'cro-HR',
            true
        );

        self::assertEquals(
            $countBeforeReusing,
            $this->countRows()
        );
        self::assertEquals(
            new UrlAlias(
                [
                    'id' => '0-da94285592c46d4396d3ca6904a4aa8f',
                    'type' => UrlAlias::LOCATION,
                    'destination' => 314,
                    'languageCodes' => ['cro-HR'],
                    'pathData' => [
                        [
                            'always-available' => true,
                            'translations' => ['cro-HR' => 'history-hello'],
                        ],
                    ],
                    'alwaysAvailable' => true,
                    'isHistory' => false,
                    'isCustom' => true,
                    'forward' => true,
                ]
            ),
            $handler->lookup('history-hello')
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
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_reusing.php');

        $countBeforeReusing = $this->countRows();
        $handler->createCustomUrlAlias(
            314,
            'nop-element',
            true,
            'cro-HR',
            true
        );

        self::assertEquals(
            $countBeforeReusing,
            $this->countRows()
        );

        // Check that custom alias whose nop element was reused still works as expected
        self::assertEquals(
            new UrlAlias(
                [
                    'id' => '2-06a943c59f33a34bb5924aaf72cd2995',
                    'type' => UrlAlias::RESOURCE,
                    'destination' => 'content/search',
                    'languageCodes' => ['eng-GB'],
                    'pathData' => [
                        [
                            'always-available' => true,
                            'translations' => ['cro-HR' => 'nop-element'],
                        ],
                        [
                            'always-available' => false,
                            'translations' => ['eng-GB' => 'search'],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => true,
                    'forward' => false,
                ]
            ),
            $handler->lookup('nop-element/search')
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
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_reusing.php');

        $countBeforeReusing = $this->countRows();
        $locationUrlAlias = $handler->lookup('autogenerated-hello');
        $handler->createCustomUrlAlias(
            315,
            'autogenerated-hello/custom-location-alias-for-315',
            true,
            'cro-HR',
            true
        );

        self::assertEquals(
            $countBeforeReusing + 1,
            $this->countRows()
        );

        // Check that location alias still works as expected
        self::assertEquals(
            $locationUrlAlias,
            $handler->lookup('autogenerated-hello')
        );
    }

    /**
     * Test for the listGlobalURLAliases() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::listGlobalURLAliases
     * @depends testLookupResourceUrlAlias
     */
    public function testListGlobalURLAliases()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_resource.php');

        $globalAliasList = $handler->listGlobalURLAliases();

        self::assertEquals(
            [
                $handler->lookup('is-alive'),
                $handler->lookup('is-alive/then/search'),
                $handler->lookup('nop-element/search'),
            ],
            $globalAliasList
        );
    }

    /**
     * Test for the listGlobalURLAliases() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::listGlobalURLAliases
     * @depends testLookupResourceUrlAlias
     */
    public function testListGlobalURLAliasesWithLanguageCode()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_resource.php');

        $globalAliasList = $handler->listGlobalURLAliases('eng-GB');

        self::assertEquals(
            [
                $handler->lookup('is-alive'),
                $handler->lookup('nop-element/search'),
            ],
            $globalAliasList
        );
    }

    /**
     * Test for the listGlobalURLAliases() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::listGlobalURLAliases
     * @depends testLookupResourceUrlAlias
     */
    public function testListGlobalURLAliasesWithOffset()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_resource.php');

        $globalAliasList = $handler->listGlobalURLAliases(null, 2);

        self::assertEquals(
            [
                $handler->lookup('nop-element/search'),
            ],
            $globalAliasList
        );
    }

    /**
     * Test for the listGlobalURLAliases() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::listGlobalURLAliases
     * @depends testLookupResourceUrlAlias
     */
    public function testListGlobalURLAliasesWithOffsetAndLimit()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_resource.php');

        $globalAliasList = $handler->listGlobalURLAliases(null, 1, 1);

        self::assertEquals(
            [
                $handler->lookup('is-alive/then/search'),
            ],
            $globalAliasList
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
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_location_delete.php');

        $countBeforeDeleting = $this->countRows();

        $handler->locationDeleted(5);

        self::assertEquals(
            $countBeforeDeleting - 5,
            $this->countRows()
        );

        self::assertEmpty(
            $handler->listURLAliasesForLocation(5)
        );

        $removedAliases = [
            'moved-original-parent/moved-history',
            'moved-original-parent/sub',
            'moved-original-parent',
            'moved-original-parent-history',
            'custom-below/moved-original-parent-custom',
        ];
        foreach ($removedAliases as $path) {
            try {
                $handler->lookup($path);
                $this->fail("Alias '$path' not removed!");
            } catch (NotFoundException $e) {
                // Do nothing
            }
        }
    }

    /**
     * Test for the locationMoved() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::locationMoved
     */
    public function testLocationMovedHistorize()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_move.php');

        $handler->publishUrlAliasForLocation(4, 3, 'move-this', 'eng-GB', false);
        $handler->locationMoved(4, 2, 3);

        $urlAlias = $handler->lookup('move-this');
        self::assertEquals(
            new UrlAlias(
                [
                    'id' => '0-' . md5('move-this'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => '4',
                    'languageCodes' => ['eng-GB'],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => ['eng-GB' => 'move-this'],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => true,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );
    }

    /**
     * Test for the locationMoved() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::locationMoved
     */
    public function testLocationMovedHistory()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_move.php');

        $handler->publishUrlAliasForLocation(4, 3, 'move-this', 'eng-GB', false);
        $handler->locationMoved(4, 2, 3);

        $urlAlias = $handler->lookup('move-this-history');
        self::assertEquals(
            new UrlAlias(
                [
                    'id' => '0-' . md5('move-this-history'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => '4',
                    'languageCodes' => ['eng-GB'],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => ['eng-GB' => 'move-this-history'],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => true,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );
    }

    /**
     * Test for the locationMoved() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::locationMoved
     */
    public function testLocationMovedHistorySubtree()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_move.php');

        $handler->publishUrlAliasForLocation(4, 3, 'move-this', 'eng-GB', false);
        $handler->locationMoved(4, 2, 3);

        $urlAlias = $handler->lookup('move-this/sub1/sub2');
        self::assertEquals(
            new UrlAlias(
                [
                    'id' => '5-' . md5('sub2'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => '6',
                    'languageCodes' => ['eng-GB'],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => ['eng-GB' => 'move-this'],
                        ],
                        [
                            'always-available' => false,
                            'translations' => ['eng-GB' => 'sub1'],
                        ],
                        [
                            'always-available' => false,
                            'translations' => ['eng-GB' => 'sub2'],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => true,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );
    }

    /**
     * Test for the locationMoved() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::locationMoved
     */
    public function testLocationMovedReparent()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_move.php');

        $handler->publishUrlAliasForLocation(4, 3, 'move-this', 'eng-GB', false);
        $handler->locationMoved(4, 2, 3);

        $urlAlias = $handler->lookup('move-here/move-this/sub1');
        self::assertEquals(
            new UrlAlias(
                [
                    'id' => '9-' . md5('sub1'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => '5',
                    'languageCodes' => ['eng-GB'],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => ['eng-GB' => 'move-here'],
                        ],
                        [
                            'always-available' => false,
                            'translations' => ['eng-GB' => 'move-this'],
                        ],
                        [
                            'always-available' => false,
                            'translations' => ['eng-GB' => 'sub1'],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );
    }

    /**
     * Test for the locationMoved() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::locationMoved
     */
    public function testLocationMovedReparentHistory()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\NotFoundException::class);

        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_move.php');

        $handler->publishUrlAliasForLocation(4, 3, 'move-this', 'eng-GB', false);
        $handler->locationMoved(4, 2, 3);

        $handler->lookup('move-here/move-this-history');
    }

    /**
     * Test for the locationMoved() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::locationMoved
     */
    public function testLocationMovedReparentSubtree()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_move.php');

        $handler->publishUrlAliasForLocation(4, 3, 'move-this', 'eng-GB', false);
        $handler->locationMoved(4, 2, 3);

        $urlAlias = $handler->lookup('move-here/move-this/sub1/sub2');
        self::assertEquals(
            new UrlAlias(
                [
                    'id' => '5-' . md5('sub2'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => '6',
                    'languageCodes' => ['eng-GB'],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => ['eng-GB' => 'move-here'],
                        ],
                        [
                            'always-available' => false,
                            'translations' => ['eng-GB' => 'move-this'],
                        ],
                        [
                            'always-available' => false,
                            'translations' => ['eng-GB' => 'sub1'],
                        ],
                        [
                            'always-available' => false,
                            'translations' => ['eng-GB' => 'sub2'],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );
    }

    /**
     * Test for the locationMoved() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::locationMoved
     */
    public function testLocationMovedReparentSubtreeHistory()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_move.php');

        $handler->publishUrlAliasForLocation(4, 3, 'move-this', 'eng-GB', false);
        $handler->locationMoved(4, 2, 3);

        $urlAlias = $handler->lookup('move-here/move-this/sub1/sub2-history');
        self::assertEquals(
            new UrlAlias(
                [
                    'id' => '5-' . md5('sub2-history'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => '6',
                    'languageCodes' => ['eng-GB'],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => ['eng-GB' => 'move-here'],
                        ],
                        [
                            'always-available' => false,
                            'translations' => ['eng-GB' => 'move-this'],
                        ],
                        [
                            'always-available' => false,
                            'translations' => ['eng-GB' => 'sub1'],
                        ],
                        [
                            'always-available' => false,
                            'translations' => ['eng-GB' => 'sub2-history'],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => true,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );
    }

    /**
     * Test for the locationCopied() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::locationCopied
     */
    public function testLocationCopiedCopiedLocationAliasIsValid()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_copy.php');

        $urlAlias = $handler->lookup('move-this');

        $handler->locationCopied(4, 400, 3);

        self::assertEquals(
            $urlAlias,
            $handler->lookup('move-this')
        );
    }

    /**
     * Test for the locationCopied() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::locationCopied
     */
    public function testLocationCopiedCopiedSubtreeIsValid()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_copy.php');

        $urlAlias = $handler->lookup('move-this/sub1/sub2');

        $handler->locationCopied(4, 400, 3);

        self::assertEquals(
            $urlAlias,
            $handler->lookup('move-this/sub1/sub2')
        );
    }

    /**
     * Test for the locationCopied() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::locationCopied
     */
    public function testLocationCopiedHistoryNotCopied()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\NotFoundException::class);

        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_copy.php');

        $handler->locationCopied(4, 400, 3);

        $handler->lookup('move-here/move-this-history');
    }

    /**
     * Test for the locationCopied() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::locationCopied
     */
    public function testLocationCopiedSubtreeHistoryNotCopied()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\NotFoundException::class);

        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_copy.php');

        $handler->locationCopied(4, 400, 3);

        $handler->lookup('move-here/move-this/sub1/sub2-history');
    }

    /**
     * Test for the locationCopied() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::locationCopied
     */
    public function testLocationCopiedSubtree()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_copy.php');

        $countBeforeCopying = $this->countRows();

        $handler->locationCopied(4, 400, 3);

        self::assertEquals(
            $countBeforeCopying + 2,
            $this->countRows()
        );

        $urlAlias = $handler->lookup('move-here/move-this/sub1/sub2');
        self::assertEquals(
            new UrlAlias(
                [
                    'id' => '10-' . md5('sub2'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 600,
                    'languageCodes' => ['eng-GB'],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => ['eng-GB' => 'move-here'],
                        ],
                        [
                            'always-available' => false,
                            'translations' => ['eng-GB' => 'move-this'],
                        ],
                        [
                            'always-available' => false,
                            'translations' => ['eng-GB' => 'sub1'],
                        ],
                        [
                            'always-available' => false,
                            'translations' => ['eng-GB' => 'sub2'],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );
    }

    /**
     * Test for the loadUrlAlias() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::loadUrlAlias
     * @dataProvider providerForTestLookupLocationMultipleLanguages
     */
    public function testLoadAutogeneratedUrlAlias(
        $url,
        array $pathData,
        array $languageCodes,
        $alwaysAvailable,
        $locationId,
        $id
    ) {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_location_multilang.php');

        $urlAlias = $handler->loadUrlAlias($id);

        self::assertInstanceOf(UrlAlias::class, $urlAlias);
        self::assertEquals(
            new UrlAlias(
                [
                    'id' => $id,
                    'type' => UrlAlias::LOCATION,
                    'destination' => $locationId,
                    'languageCodes' => $languageCodes,
                    'pathData' => $pathData,
                    'alwaysAvailable' => $alwaysAvailable,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );
    }

    /**
     * Test for the loadUrlAlias() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::loadUrlAlias
     * @dataProvider providerForTestLookupResourceUrlAlias
     */
    public function testLoadResourceUrlAlias(
        $url,
        $pathData,
        array $languageCodes,
        $forward,
        $alwaysAvailable,
        $destination,
        $id
    ) {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_resource.php');

        $urlAlias = $handler->loadUrlAlias($id);

        self::assertInstanceOf(UrlAlias::class, $urlAlias);
        self::assertEquals(
            new UrlAlias(
                [
                    'id' => $id,
                    'type' => UrlAlias::RESOURCE,
                    'destination' => $destination,
                    'languageCodes' => $languageCodes,
                    'pathData' => $pathData,
                    'alwaysAvailable' => $alwaysAvailable,
                    'isHistory' => false,
                    'isCustom' => true,
                    'forward' => $forward,
                ]
            ),
            $urlAlias
        );
    }

    /**
     * Test for the loadUrlAlias() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::loadUrlAlias
     * @dataProvider providerForTestLookupVirtualUrlAlias
     */
    public function testLoadVirtualUrlAlias($url, $id)
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_location_custom.php');

        $urlAlias = $handler->loadUrlAlias($id);

        $this->assertVirtualUrlAliasValid($urlAlias, $id);
    }

    protected function getHistoryAlias()
    {
        return new UrlAlias(
            [
                'id' => '3-5f46413bb0ba5998caef84ab1ea590e1',
                'type' => UrlAlias::LOCATION,
                'destination' => '316',
                'pathData' => [
                    [
                        'always-available' => true,
                        'translations' => ['cro-HR' => 'jedan'],
                    ],
                    [
                        'always-available' => false,
                        'translations' => [
                            'cro-HR' => 'dva',
                            'eng-GB' => 'two',
                        ],
                    ],
                    [
                        'always-available' => false,
                        'translations' => [
                            'cro-HR' => 'tri-history',
                        ],
                    ],
                ],
                'languageCodes' => ['cro-HR'],
                'alwaysAvailable' => false,
                'isHistory' => true,
                'isCustom' => false,
                'forward' => false,
            ]
        );
    }

    /**
     * Test for the loadUrlAlias() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::loadUrlAlias
     */
    public function testLoadHistoryUrlAlias()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_location.php');

        $historyAlias = $this->getHistoryAlias();
        $urlAlias = $handler->loadUrlAlias($historyAlias->id);

        self::assertEquals(
            $historyAlias,
            $urlAlias
        );
    }

    /**
     * Test for the loadUrlAlias() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::loadUrlAlias
     */
    public function testLoadUrlAliasThrowsNotFoundException()
    {
        $this->expectException(\eZ\Publish\API\Repository\Exceptions\NotFoundException::class);

        $handler = $this->getHandler();

        $handler->loadUrlAlias('non-existent');
    }

    public function providerForTestPublishUrlAliasForLocationSkipsReservedWord()
    {
        return [
            [
                'section',
                'section2',
            ],
            [
                'claß',
                'class2',
            ],
        ];
    }

    /**
     * Test for the publishUrlAliasForLocation() method.
     *
     * @dataProvider providerForTestPublishUrlAliasForLocationSkipsReservedWord
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::publishUrlAliasForLocation
     * @group publish
     */
    public function testPublishUrlAliasForLocationSkipsReservedWord($text, $alias)
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/publish_base.php');

        $handler->publishUrlAliasForLocation(314, 2, $text, 'kli-KR');

        $urlAlias = $handler->lookup($alias);

        $this->assertEquals(314, $urlAlias->destination);
        $this->assertEquals(['kli-KR'], $urlAlias->languageCodes);
    }

    /**
     * Test for the locationSwapped() method.
     *
     * @group swap
     */
    public function testLocationSwappedSimple()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_swap_simple.php');

        $countBeforeReusing = $this->countRows();

        $handler->locationSwapped(316, 314, 317, 315);

        $this->assertEquals(
            $countBeforeReusing,
            $this->countRows()
        );

        $urlAlias = $handler->lookup('jedan/swap');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '2-' . md5('swap'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 316,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'jedan',
                            ],
                        ],
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'swap',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('dva/swap');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '3-' . md5('swap'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 317,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'dva',
                            ],
                        ],
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'swap',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );
    }

    /**
     * Test for the locationSwapped() method.
     *
     * @group swap
     */
    public function testLocationSwappedSimpleWithHistory()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_swap_simple_history.php');

        $countBeforeReusing = $this->countRows();

        $handler->locationSwapped(316, 314, 317, 315);

        $this->assertEquals(
            $countBeforeReusing,
            $this->countRows()
        );

        $urlAlias = $handler->lookup('jedan/swap');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '2-' . md5('swap'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 316,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'jedan',
                            ],
                        ],
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'swap',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => true,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('dva/swap');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '3-' . md5('swap'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 317,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'dva',
                            ],
                        ],
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'swap',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => true,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('jedan/swap-new');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '2-' . md5('swap-new'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 316,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'jedan',
                            ],
                        ],
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'swap-new',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('dva/swap-new');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '3-' . md5('swap-new'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 317,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'dva',
                            ],
                        ],
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'swap-new',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );
    }

    /**
     * Test for the locationSwapped() method.
     *
     * @group swap
     */
    public function testLocationSwappedSimpleWithConflict()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_swap_simple_conflict.php');

        $urlAlias1TakenExpected = $handler->lookup('jedan/swap-new-2');
        $urlAlias2TakenExpected = $handler->lookup('dva/swap-new-1');

        $urlAlias1HistorizedExpected = $handler->lookup('jedan/swap-new-1');
        $urlAlias1HistorizedExpected->isHistory = true;
        $urlAlias2HistorizedExpected = $handler->lookup('dva/swap-new-2');
        $urlAlias2HistorizedExpected->isHistory = true;

        $countBeforeReusing = $this->countRows();

        $handler->locationSwapped(316, 314, 317, 315);

        $this->assertEquals(
            $countBeforeReusing + 2,
            $this->countRows()
        );

        $urlAlias1Taken = $handler->lookup('jedan/swap-new-2');
        $urlAlias2Taken = $handler->lookup('dva/swap-new-1');

        $urlAlias1Historized = $handler->lookup('jedan/swap-new-1');
        $urlAlias2Historized = $handler->lookup('dva/swap-new-2');

        $this->assertEquals($urlAlias1TakenExpected, $urlAlias1Taken);
        $this->assertEquals($urlAlias2TakenExpected, $urlAlias2Taken);

        $this->assertEquals($urlAlias1HistorizedExpected, $urlAlias1Historized);
        $this->assertEquals($urlAlias2HistorizedExpected, $urlAlias2Historized);

        $urlAlias1New = $handler->lookup('jedan/swap-new-22');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '2-' . md5('swap-new-22'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 316,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'jedan',
                            ],
                        ],
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'swap-new-22',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias1New
        );

        $urlAlias2New = $handler->lookup('dva/swap-new-12');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '3-' . md5('swap-new-12'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 317,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'dva',
                            ],
                        ],
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'swap-new-12',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias2New
        );
    }

    /**
     * Test for the locationSwapped() method.
     *
     * @group swap
     */
    public function testLocationSwappedSiblingsSimple()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_swap_siblings_simple.php');

        $countBeforeReusing = $this->countRows();

        $handler->locationSwapped(314, 2, 315, 2);

        $this->assertEquals(
            $countBeforeReusing,
            $this->countRows()
        );

        $urlAlias = $handler->lookup('jedan');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '0-' . md5('jedan'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 315,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'jedan',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('dva');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '0-' . md5('dva'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 314,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'dva',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );
    }

    /**
     * Test for the locationSwapped() method.
     *
     * @group swap
     */
    public function testLocationSwappedSiblingsSimpleReverse()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_swap_siblings_simple.php');

        $countBeforeReusing = $this->countRows();

        $handler->locationSwapped(315, 2, 314, 2);

        $this->assertEquals(
            $countBeforeReusing,
            $this->countRows()
        );

        $urlAlias = $handler->lookup('jedan');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '0-' . md5('jedan'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 315,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'jedan',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('dva');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '0-' . md5('dva'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 314,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'dva',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );
    }

    /**
     * Test for the locationSwapped() method.
     *
     * @group swap
     */
    public function testLocationSwappedSiblingsSimpleWithHistory()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_swap_siblings_simple_history.php');

        $countBeforeReusing = $this->countRows();

        $handler->locationSwapped(314, 2, 315, 2);

        $this->assertEquals(
            $countBeforeReusing,
            $this->countRows()
        );

        $urlAlias = $handler->lookup('jedan');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '0-' . md5('jedan'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 314,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'jedan',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => true,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('dva');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '0-' . md5('dva'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 315,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'dva',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => true,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('jedan-new');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '0-' . md5('jedan-new'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 315,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'jedan-new',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('dva-new');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '0-' . md5('dva-new'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 314,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'dva-new',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );
    }

    /**
     * Test for the locationSwapped() method.
     *
     * @group swap
     */
    public function testLocationSwappedSiblingsSimpleWithHistoryReverse()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_swap_siblings_simple_history.php');

        $countBeforeReusing = $this->countRows();

        $handler->locationSwapped(315, 2, 314, 2);

        $this->assertEquals(
            $countBeforeReusing,
            $this->countRows()
        );

        $urlAlias = $handler->lookup('jedan');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '0-' . md5('jedan'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 314,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'jedan',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => true,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('dva');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '0-' . md5('dva'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 315,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'dva',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => true,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('jedan-new');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '0-' . md5('jedan-new'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 315,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'jedan-new',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('dva-new');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '0-' . md5('dva-new'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 314,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'dva-new',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );
    }

    /**
     * Test for the locationSwapped() method.
     *
     * @group swap
     */
    public function testLocationSwappedSiblingsSameName()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_swap_siblings_same_name.php');

        $countBeforeReusing = $this->countRows();

        $handler->locationSwapped(314, 2, 315, 2);

        $this->assertEquals(
            $countBeforeReusing,
            $this->countRows()
        );

        $urlAlias = $handler->lookup('swap');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '0-' . md5('swap'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 314,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'swap',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('swap2');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '0-' . md5('swap2'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 315,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'swap2',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );
    }

    /**
     * Test for the locationSwapped() method.
     *
     * @group swap
     */
    public function testLocationSwappedSiblingsSameNameReverse()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_swap_siblings_same_name.php');

        $countBeforeReusing = $this->countRows();

        $handler->locationSwapped(315, 2, 314, 2);

        $this->assertEquals(
            $countBeforeReusing,
            $this->countRows()
        );

        $urlAlias = $handler->lookup('swap');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '0-' . md5('swap'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 314,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'swap',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('swap2');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '0-' . md5('swap2'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 315,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'swap2',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );
    }

    /**
     * Test for the locationSwapped() method.
     *
     * @group swap
     */
    public function testLocationSwappedSiblingsSameNameMultipleLanguages()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_swap_siblings_same_name_multilang.php');

        $countBeforeReusing = $this->countRows();

        $handler->locationSwapped(314, 2, 315, 2);

        $this->assertEquals(
            $countBeforeReusing,
            $this->countRows()
        );

        $urlAlias = $handler->lookup('swap-hr');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '0-' . md5('swap-hr'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 314,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'swap-hr',
                                'eng-GB' => 'swap-en2',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('swap-hr2');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '0-' . md5('swap-hr2'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 315,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'swap-hr2',
                                'eng-GB' => 'swap-en',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('swap-en');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '0-' . md5('swap-en'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 315,
                    'languageCodes' => [
                        'eng-GB',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'swap-hr2',
                                'eng-GB' => 'swap-en',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('swap-en2');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '0-' . md5('swap-en2'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 314,
                    'languageCodes' => [
                        'eng-GB',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'swap-hr',
                                'eng-GB' => 'swap-en2',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );
    }

    /**
     * Test for the locationSwapped() method.
     *
     * @group swap
     */
    public function testLocationSwappedMultipleLanguagesSimple()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_swap_multilang_simple.php');

        $urlAlias1HRExpected = $handler->lookup('jedan/swap-hr');
        $urlAlias1ENExpected = $handler->lookup('jedan/swap-en');
        $urlAlias2HRExpected = $handler->lookup('dva/swap-hr');
        $urlAlias2ENExpected = $handler->lookup('dva/swap-en');

        $countBeforeReusing = $this->countRows();

        $handler->locationSwapped(316, 314, 317, 315);

        $this->assertEquals(
            $countBeforeReusing,
            $this->countRows()
        );

        $urlAlias1HR = $handler->lookup('jedan/swap-hr');
        $urlAlias1EN = $handler->lookup('jedan/swap-en');
        $urlAlias2HR = $handler->lookup('dva/swap-hr');
        $urlAlias2EN = $handler->lookup('dva/swap-en');

        $this->assertEquals($urlAlias1HRExpected, $urlAlias1HR);
        $this->assertEquals($urlAlias1ENExpected, $urlAlias1EN);
        $this->assertEquals($urlAlias2HRExpected, $urlAlias2HR);
        $this->assertEquals($urlAlias2ENExpected, $urlAlias2EN);
    }

    /**
     * Test for the locationSwapped() method.
     *
     * @group swap
     */
    public function testLocationSwappedMultipleLanguagesDifferentLanguagesSimple()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_swap_multilang_diff_simple.php');

        $countBeforeReusing = $this->countRows();

        $handler->locationSwapped(316, 314, 317, 315);

        $this->assertEquals(
            $countBeforeReusing + 2,
            $this->countRows()
        );

        $urlAlias = $handler->lookup('jedan/swap-hr');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '2-' . md5('swap-hr'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 316,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'jedan',
                            ],
                        ],
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'swap-hr',
                                'ger-DE' => 'swap-de',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('jedan/swap-de');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '2-' . md5('swap-de'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 316,
                    'languageCodes' => [
                        'ger-DE',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'jedan',
                            ],
                        ],
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'swap-hr',
                                'ger-DE' => 'swap-de',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('jedan/swap-en');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '2-' . md5('swap-en'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 316,
                    'languageCodes' => [
                        'eng-GB',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'jedan',
                            ],
                        ],
                        [
                            'always-available' => false,
                            'translations' => [
                                'eng-GB' => 'swap-en',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => true,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('dva/swap-hr');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '3-' . md5('swap-hr'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 317,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'dva',
                            ],
                        ],
                        [
                            'always-available' => false,
                            'translations' => [
                                'eng-GB' => 'swap-en',
                                'cro-HR' => 'swap-hr',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('dva/swap-en');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '3-' . md5('swap-en'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 317,
                    'languageCodes' => [
                        'eng-GB',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'dva',
                            ],
                        ],
                        [
                            'always-available' => false,
                            'translations' => [
                                'eng-GB' => 'swap-en',
                                'cro-HR' => 'swap-hr',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('dva/swap-de');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '3-' . md5('swap-de'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 317,
                    'languageCodes' => [
                        'ger-DE',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'dva',
                            ],
                        ],
                        [
                            'always-available' => false,
                            'translations' => [
                                'ger-DE' => 'swap-de',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => true,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );
    }

    /**
     * Test for the locationSwapped() method.
     *
     * @group swap
     */
    public function testLocationSwappedMultipleLanguagesDifferentLanguages()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_swap_multilang_diff.php');

        $countBeforeReusing = $this->countRows();

        $handler->locationSwapped(317, 315, 316, 314);

        $this->assertEquals(
            $countBeforeReusing + 2,
            $this->countRows()
        );

        $urlAlias = $handler->lookup('jedan/swap-this');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '2-' . md5('swap-this'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 316,
                    'languageCodes' => [
                        'ger-DE',
                        'nor-NO',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'jedan',
                            ],
                        ],
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'swap-hr',
                                'ger-DE' => 'swap-this',
                                'nor-NO' => 'swap-this',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('jedan/swap-en');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '2-' . md5('swap-en'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 316,
                    'languageCodes' => [
                        'eng-GB',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'jedan',
                            ],
                        ],
                        [
                            'always-available' => false,
                            'translations' => [
                                'eng-GB' => 'swap-en',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => true,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('dva/swap-hr');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '3-' . md5('swap-hr'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 317,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'dva',
                            ],
                        ],
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'swap-hr',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => true,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('dva/swap-this');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '3-' . md5('swap-this'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 317,
                    'languageCodes' => [
                        'cro-HR',
                        'ger-DE',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'dva',
                            ],
                        ],
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'swap-this',
                                'ger-DE' => 'swap-this',
                                'eng-GB' => 'swap-en',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );
    }

    /**
     * Test for the locationSwapped() method.
     *
     * @group swap
     */
    public function testLocationSwappedMultipleLanguagesWithCompositeHistory()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_swap_multilang_cleanup_composite.php');

        $countBeforeReusing = $this->countRows();

        $handler->locationSwapped(317, 315, 316, 314);

        $this->assertEquals(
            $countBeforeReusing + 4,
            $this->countRows()
        );

        $urlAlias = $handler->lookup('jedan/swap-this');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '2-' . md5('swap-this'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 316,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'jedan',
                            ],
                        ],
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'swap-this',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => true,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('jedan/swap-en');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '2-' . md5('swap-en'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 316,
                    'languageCodes' => [
                        'eng-GB',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'jedan',
                            ],
                        ],
                        [
                            'always-available' => false,
                            'translations' => [
                                'eng-GB' => 'swap-en',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => true,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('jedan/swap-hr');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '2-' . md5('swap-hr'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 316,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'jedan',
                            ],
                        ],
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'swap-hr',
                                'ger-DE' => 'swap-that',
                                'nor-NO' => 'swap-that',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('jedan/swap-that');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '2-' . md5('swap-that'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 316,
                    'languageCodes' => [
                        'ger-DE',
                        'nor-NO',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'jedan',
                            ],
                        ],
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'swap-hr',
                                'ger-DE' => 'swap-that',
                                'nor-NO' => 'swap-that',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('dva/swap-hr');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '3-' . md5('swap-hr'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 317,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'dva',
                            ],
                        ],
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'swap-hr',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => true,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('dva/swap-that');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '3-' . md5('swap-that'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 317,
                    'languageCodes' => [
                        'ger-DE',
                        'nor-NO',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'dva',
                            ],
                        ],
                        [
                            'always-available' => false,
                            'translations' => [
                                'ger-DE' => 'swap-that',
                                'nor-NO' => 'swap-that',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => true,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('dva/swap-this');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '3-' . md5('swap-this'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 317,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'dva',
                            ],
                        ],
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'swap-this',
                                'eng-GB' => 'swap-en',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('dva/swap-en');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '3-' . md5('swap-en'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 317,
                    'languageCodes' => [
                        'eng-GB',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'dva',
                            ],
                        ],
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'swap-this',
                                'eng-GB' => 'swap-en',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );
    }

    /**
     * Test for the locationSwapped() method.
     *
     * @group swap
     */
    public function testLocationSwappedWithReusingExternalHistory()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_swap_reusing_external_history.php');

        $countBeforeReusing = $this->countRows();

        $handler->locationSwapped(318, 314, 319, 315);

        $this->assertEquals(
            $countBeforeReusing,
            $this->countRows()
        );

        $urlAlias = $handler->lookup('jedan/swap-that');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '2-' . md5('swap-that'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 318,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'jedan',
                            ],
                        ],
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'swap-that',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('dva/swap-this');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '3-' . md5('swap-this'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 319,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'dva',
                            ],
                        ],
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'swap-this',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('dva/swap-that');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '3-' . md5('swap-that'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 319,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'dva',
                            ],
                        ],
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'swap-that',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => true,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('jedan/swap-this');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '2-' . md5('swap-this'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 318,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'jedan',
                            ],
                        ],
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'swap-this',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => true,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );
    }

    /**
     * Test for the locationSwapped() method.
     *
     * @group swap
     */
    public function testLocationSwappedWithReusingNopEntry()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_swap_reusing_nop.php');

        $countBeforeReusing = $this->countRows();

        $handler->locationSwapped(316, 314, 317, 315);

        $this->assertEquals(
            $countBeforeReusing + 1,
            $this->countRows()
        );

        $urlAlias = $handler->lookup('jedan/swap-that');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '2-' . md5('swap-that'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 316,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'jedan',
                            ],
                        ],
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'swap-that',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('dva/swap-this');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '3-' . md5('swap-this'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 317,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'dva',
                            ],
                        ],
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'swap-this',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('dva/swap-that');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '3-' . md5('swap-that'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 317,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'dva',
                            ],
                        ],
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'swap-that',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => true,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );

        $urlAlias = $handler->lookup('jedan/swap-this');
        $this->assertEquals(
            new UrlAlias(
                [
                    'id' => '2-' . md5('swap-this'),
                    'type' => UrlAlias::LOCATION,
                    'destination' => 316,
                    'languageCodes' => [
                        'cro-HR',
                    ],
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'jedan',
                            ],
                        ],
                        [
                            'always-available' => false,
                            'translations' => [
                                'cro-HR' => 'swap-this',
                            ],
                        ],
                    ],
                    'alwaysAvailable' => false,
                    'isHistory' => true,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            $urlAlias
        );
    }

    /**
     * Test for the locationSwapped() method.
     *
     * @depends testLocationSwappedWithReusingNopEntry
     * @group swap
     */
    public function testLocationSwappedWithReusingNopEntryCustomAliasIsDestroyed()
    {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_swap_reusing_nop.php');

        $handler->lookup('jedan/swap-that/search');
        $handler->locationSwapped(316, 314, 317, 315);

        try {
            $handler->lookup('jedan/swap-that/search');
            $this->fail('Custom alias is not destroyed');
        } catch (NotFoundException $e) {
            // Custom alias is destroyed by reusing NOP entry with existing autogenerated alias
            // on the same level (that means link and ID are updated to the existing alias ID,
            // so custom alias children entries are no longer properly linked (parent-link))
        }
    }

    /**
     * Test for the locationSwapped() method.
     *
     * @group swap
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::locationSwapped
     */
    public function testLocationSwappedUpdatesLocationPathIdentificationString()
    {
        $handler = $this->getHandler();
        $locationGateway = $this->getLocationGateway();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_swap_path_identification_string.php');

        $countBeforeReusing = $this->countRows();

        $handler->locationSwapped(314, 2, 315, 2);

        $this->assertEquals(
            $countBeforeReusing,
            $this->countRows()
        );

        $locationData = $locationGateway->getBasicNodeData(314);
        self::assertEquals('dva', $locationData['path_identification_string']);

        $locationData = $locationGateway->getBasicNodeData(315);
        self::assertEquals('jedan', $locationData['path_identification_string']);
    }

    /**
     * Test for the locationSwapped() method.
     *
     * @group swap
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::locationSwapped
     */
    public function testLocationSwappedMultipleLanguagesUpdatesLocationPathIdentificationString()
    {
        $handler = $this->getHandler();
        $locationGateway = $this->getLocationGateway();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/urlaliases_swap_multilang_path_identification_string.php');

        $countBeforeReusing = $this->countRows();

        $handler->locationSwapped(314, 2, 315, 2);

        $this->assertEquals(
            $countBeforeReusing,
            $this->countRows()
        );

        $locationData = $locationGateway->getBasicNodeData(314);
        self::assertEquals('zwei', $locationData['path_identification_string']);

        $locationData = $locationGateway->getBasicNodeData(315);
        self::assertEquals('jedan', $locationData['path_identification_string']);
    }

    protected function countRows(): int
    {
        /** @var \eZ\Publish\Core\Persistence\Database\SelectQuery $query */
        $connection = $this->getDatabaseConnection();
        $query = $connection->createQueryBuilder();
        $query
            ->select($connection->getDatabasePlatform()->getCountExpression('*'))
            ->from(UrlAliasGateway::TABLE);

        $statement = $query->execute();

        return (int)$statement->fetchColumn();
    }

    /**
     * @var \eZ\Publish\Core\Persistence\Doctrine\ConnectionHandler
     * @deprecated Start to use DBAL $connection instead.
     */
    protected $dbHandler;

    /** @var \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway */
    protected $locationGateway;

    /** @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\Handler */
    protected $languageHandler;

    /** @var \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator */
    protected $languageMaskGenerator;

    /**
     * @param array $methods
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getPartlyMockedHandler(array $methods)
    {
        return $this->getMockBuilder(Handler::class)
            ->setConstructorArgs(
                [
                    $this->createMock(UrlAliasGateway::class),
                    $this->createMock(Mapper::class),
                    $this->createMock(LocationGateway::class),
                    $this->createMock(LanguageHandler::class),
                    $this->createMock(SlugConverter::class),
                    $this->createMock(Gateway::class),
                    $this->createMock(LanguageMaskGenerator::class),
                    $this->createMock(TransactionHandler::class),
                ]
            )
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function getHandler(): Handler
    {
        $languageHandler = $this->getLanguageHandler();
        $languageMaskGenerator = $this->getLanguageMaskGenerator();
        $gateway = new DoctrineDatabase(
            $this->getDatabaseConnection(),
            $languageMaskGenerator
        );
        $mapper = new Mapper($languageMaskGenerator);
        $slugConverter = new SlugConverter($this->getProcessor());
        $connection = $this->getDatabaseConnection();
        $contentGateway = new ContentGateway(
            $connection,
            $this->getSharedGateway(),
            new ContentGateway\QueryBuilder($connection),
            $languageHandler,
            $languageMaskGenerator
        );

        return new Handler(
            $gateway,
            $mapper,
            $this->getLocationGateway(),
            $languageHandler,
            $slugConverter,
            $contentGateway,
            $languageMaskGenerator,
            $this->createMock(TransactionHandler::class)
        );
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function getLanguageHandler(): LanguageHandler
    {
        if (!isset($this->languageHandler)) {
            $this->languageHandler = new LanguageHandler(
                new LanguageGateway(
                    $this->getDatabaseConnection()
                ),
                new LanguageMapper()
            );
        }

        return $this->languageHandler;
    }

    /**
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator
     */
    protected function getLanguageMaskGenerator()
    {
        if (!isset($this->languageMaskGenerator)) {
            $this->languageMaskGenerator = new LanguageMaskGenerator(
                $this->getLanguageHandler()
            );
        }

        return $this->languageMaskGenerator;
    }

    /**
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway
     */
    protected function getLocationGateway()
    {
        if (!isset($this->locationGateway)) {
            $this->locationGateway = new DoctrineDatabaseLocation(
                $this->getDatabaseConnection(),
                $this->getLanguageMaskGenerator()
            );
        }

        return $this->locationGateway;
    }

    /**
     * @return \eZ\Publish\Core\Persistence\TransformationProcessor
     */
    public function getProcessor()
    {
        return new DefinitionBased(
            new Parser(),
            new PcreCompiler(new Utf8Converter()),
            glob(__DIR__ . '/../../../../Tests/TransformationProcessor/_fixtures/transformations/*.tr')
        );
    }

    /**
     * Data provider for tests of archiveUrlAliasesForDeletedTranslations.
     *
     * @see testArchiveUrlAliasesForDeletedTranslations for the description of parameters
     *
     * @return array
     */
    public function providerForArchiveUrlAliasesForDeletedTranslations()
    {
        return [
            [2, ['eng-GB', 'pol-PL'], 'pol-PL'],
            [3, ['eng-GB', 'pol-PL', 'nor-NO'], 'pol-PL'],
        ];
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Handler::archiveUrlAliasesForDeletedTranslations()
     *
     * @dataProvider providerForArchiveUrlAliasesForDeletedTranslations
     *
     * @param int $locationId
     * @param string[] $expectedLanguages expected language codes before deleting
     * @param string $removeLanguage language code to be deleted
     */
    public function testArchiveUrlAliasesForDeletedTranslations(
        $locationId,
        array $expectedLanguages,
        $removeLanguage
    ) {
        $handler = $this->getHandler();
        $this->insertDatabaseFixture(__DIR__ . '/_fixtures/publish_multilingual.php');

        // collect data persisted from fixtures
        $urlAliases = $handler->listURLAliasesForLocation($locationId);
        $collectedLanguages = [];
        $collectedUrls = [];
        foreach ($urlAliases as $urlAlias) {
            // collect languages of all URL aliases
            $collectedLanguages = array_merge($collectedLanguages, $urlAlias->languageCodes);
            $isComposite = count($urlAlias->languageCodes) > 1;
            foreach ($urlAlias->pathData as $pathData) {
                // collect also actual unique URLs to be removed to check them after removal
                if (!empty($pathData['translations'][$removeLanguage])) {
                    $url = $pathData['translations'][$removeLanguage];
                    $collectedUrls[$url] = $isComposite;
                }
            }
        }
        // sanity check
        self::assertEquals($expectedLanguages, $collectedLanguages);

        // remove language
        $publishedLanguages = array_values(array_diff($collectedLanguages, [$removeLanguage]));
        $handler->archiveUrlAliasesForDeletedTranslations($locationId, 1, $publishedLanguages);

        // check reloaded structures
        $urlAliases = $handler->listURLAliasesForLocation($locationId);
        foreach ($urlAliases as $urlAlias) {
            self::assertNotContains($removeLanguage, $urlAlias->languageCodes);
            foreach ($urlAlias->pathData as $pathData) {
                self::assertNotContains($removeLanguage, $pathData['translations']);
                foreach ($pathData['translations'] as $url) {
                    $lookupUrlAlias = $handler->lookup($url);
                    self::assertNotContains($removeLanguage, $lookupUrlAlias->languageCodes);
                }
            }
        }

        // lookup removed URLs to check they're not found
        foreach ($collectedUrls as $url => $isComposite) {
            $urlAlias = $handler->lookup($url);
            if ($isComposite) {
                // check if alias no longer refers to removed Translation
                self::assertNotContains($removeLanguage, $urlAlias->languageCodes);
                foreach ($urlAlias->pathData as $pathData) {
                    self::assertNotContains($removeLanguage, $pathData['translations']);
                }
            } else {
                // check if non composite alias for removed translation is historized
                self::assertTrue($urlAlias->isHistory);
            }
        }
    }
}
