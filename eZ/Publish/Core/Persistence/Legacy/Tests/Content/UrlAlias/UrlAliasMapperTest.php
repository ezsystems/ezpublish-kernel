<?php

/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\UrlAliasMapperTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\UrlAlias;

use eZ\Publish\Core\Persistence\Legacy\Tests\Content\LanguageAwareTestCase;
use eZ\Publish\SPI\Persistence\Content\UrlAlias;
use eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Mapper;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator as LanguageMaskGenerator;

/**
 * Test case for UrlAliasMapper.
 */
class UrlAliasMapperTest extends LanguageAwareTestCase
{
    protected $fixture = [
        0 => [
            'action' => 'eznode:314',
            'parent' => '1',
            'text_md5' => 'f97c5d29941bfb1b2fdab0874906ab82',
            'raw_path_data' => [
                0 => [
                    [
                        'lang_mask' => 2,
                        'text' => 'root_us',
                    ],
                    [
                        'lang_mask' => 4,
                        'text' => 'root_gb',
                    ],
                ],
                1 => [
                    [
                        'lang_mask' => 4,
                        'text' => 'one',
                    ],
                ],
            ],
            'lang_mask' => 5,
            'is_original' => '1',
            'is_alias' => '1',
            'alias_redirects' => '0',
        ],
        1 => [
            'action' => 'eznode:314',
            'parent' => '2',
            'text_md5' => 'b8a9f715dbb64fd5c56e7783c6820a61',
            'raw_path_data' => [
                0 => [
                    [
                        'lang_mask' => 3,
                        'text' => 'two',
                    ],
                ],
            ],
            'lang_mask' => 3,
            'is_original' => '0',
            'is_alias' => '0',
            'alias_redirects' => '1',
        ],
        2 => [
            'action' => 'module:content/search',
            'parent' => '0',
            'text_md5' => '35d6d33467aae9a2e3dccb4b6b027878',
            'raw_path_data' => [
                0 => [
                    [
                        'lang_mask' => 6,
                        'text' => 'three',
                    ],
                ],
            ],
            'lang_mask' => 6,
            'is_original' => '1',
            'is_alias' => '1',
            'alias_redirects' => '1',
        ],
        3 => [
            'action' => 'nop:',
            'parent' => '3',
            'text_md5' => '8cbad96aced40b3838dd9f07f6ef5772',
            'raw_path_data' => [
                0 => [
                    [
                        'lang_mask' => 1,
                        'text' => 'four',
                    ],
                ],
            ],
            'lang_mask' => 1,
            'is_original' => '0',
            'is_alias' => '0',
            'alias_redirects' => '1',
        ],
        4 => [
            'action' => 'nop:',
            'parent' => '3',
            'text_md5' => '1d8d2fd0a99802b89eb356a86e029d25',
            'raw_path_data' => [
                0 => [
                    [
                        'lang_mask' => 8,
                        'text' => 'drei',
                    ],
                ],
            ],
            'lang_mask' => 8,
            'is_original' => '0',
            'is_alias' => '0',
            'alias_redirects' => '1',
        ],
    ];

    protected function getExpectation()
    {
        return [
            0 => new UrlAlias(
                [
                    'id' => '1-f97c5d29941bfb1b2fdab0874906ab82',
                    'type' => UrlAlias::LOCATION,
                    'destination' => 314,
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'eng-US' => 'root_us',
                                'eng-GB' => 'root_gb',
                            ],
                        ],
                        [
                            'always-available' => false,
                            'translations' => [
                                'eng-GB' => 'one',
                            ],
                        ],
                    ],
                    'languageCodes' => ['eng-GB'],
                    'alwaysAvailable' => true,
                    'isHistory' => false,
                    'isCustom' => true,
                    'forward' => false,
                ]
            ),
            1 => new UrlAlias(
                [
                    'id' => '2-b8a9f715dbb64fd5c56e7783c6820a61',
                    'type' => UrlAlias::LOCATION,
                    'destination' => 314,
                    'pathData' => [
                        [
                            'always-available' => true,
                            'translations' => [
                                'eng-US' => 'two',
                            ],
                        ],
                    ],
                    'languageCodes' => ['eng-US'],
                    'alwaysAvailable' => true,
                    'isHistory' => true,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            2 => new UrlAlias(
                [
                    'id' => '0-35d6d33467aae9a2e3dccb4b6b027878',
                    'type' => UrlAlias::RESOURCE,
                    'destination' => 'content/search',
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'eng-US' => 'three',
                                'eng-GB' => 'three',
                            ],
                        ],
                    ],
                    'languageCodes' => ['eng-US', 'eng-GB'],
                    'alwaysAvailable' => false,
                    'isHistory' => false,
                    'isCustom' => true,
                    'forward' => true,
                ]
            ),
            3 => new UrlAlias(
                [
                    'id' => '3-8cbad96aced40b3838dd9f07f6ef5772',
                    'type' => UrlAlias::VIRTUAL,
                    'destination' => null,
                    'pathData' => [
                        [
                            'always-available' => true,
                            'translations' => [
                                'always-available' => 'four',
                            ],
                        ],
                    ],
                    'languageCodes' => [],
                    'alwaysAvailable' => true,
                    'isHistory' => true,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
            4 => new UrlAlias(
                [
                    'id' => '3-1d8d2fd0a99802b89eb356a86e029d25',
                    'type' => UrlAlias::VIRTUAL,
                    'destination' => null,
                    'pathData' => [
                        [
                            'always-available' => false,
                            'translations' => [
                                'ger-DE' => 'drei',
                            ],
                        ],
                    ],
                    'languageCodes' => ['ger-DE'],
                    'alwaysAvailable' => false,
                    'isHistory' => true,
                    'isCustom' => false,
                    'forward' => false,
                ]
            ),
        ];
    }

    public function providerForTestExtractUrlAliasFromData()
    {
        return [[0], [1], [2], [3]];
    }

    /**
     * Test for the extractUrlAliasFromData() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Mapper::extractUrlAliasFromData
     * @dataProvider providerForTestExtractUrlAliasFromData
     */
    public function testExtractUrlAliasFromData($index)
    {
        $mapper = $this->getMapper();

        $urlAlias = $mapper->extractUrlAliasFromData($this->fixture[$index]);
        $expectation = $this->getExpectation();

        self::assertEquals(
            $expectation[$index],
            $urlAlias
        );
    }

    /**
     * Test for the extractUrlAliasListFromData() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Mapper::extractUrlAliasListFromData
     * @depends testExtractUrlAliasFromData
     */
    public function testExtractUrlAliasListFromData()
    {
        $mapper = $this->getMapper();

        self::assertEquals(
            $this->getExpectation(),
            $mapper->extractUrlAliasListFromData($this->fixture)
        );
    }

    /**
     * Test for the extractLanguageCodesFromData method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Mapper::extractLanguageCodesFromData
     */
    public function testExtractLanguageCodesFromData()
    {
        $mapper = $this->getMapper();

        self::assertEquals(
            ['eng-US', 'eng-GB', 'ger-DE'],
            $mapper->extractLanguageCodesFromData($this->fixture)
        );
    }

    /**
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Mapper
     */
    protected function getMapper()
    {
        $languageHandler = $this->getLanguageHandler();
        $languageMaskGenerator = new LanguageMaskGenerator($languageHandler);

        return new Mapper($languageMaskGenerator);
    }
}
