<?php

/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\Language\MaskGeneratorTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\Language;

use eZ\Publish\Core\Persistence\Legacy\Tests\Content\LanguageAwareTestCase;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator;
use eZ\Publish\SPI\Persistence\Content\Language;

/**
 * Test case for Language MaskGenerator.
 */
class MaskGeneratorTest extends LanguageAwareTestCase
{
    /**
     * @param array $languages
     * @param int $expectedMask
     *
     * @covers       \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator::generateLanguageMask
     * @dataProvider getLanguageMaskData
     */
    public function testGenerateLanguageMask(array $languages, $expectedMask)
    {
        $generator = $this->getMaskGenerator();

        $this->assertSame(
            $expectedMask,
            $generator->generateLanguageMask($languages)
        );
    }

    /**
     * Returns test data for {@link testGenerateLanguageMask()}.
     *
     * @return array
     */
    public static function getLanguageMaskData()
    {
        return [
            'error' => [
                [],
                0,
            ],
            'single_lang' => [
                ['eng-GB' => true],
                4,
            ],
            'multi_lang' => [
                ['eng-US' => true, 'eng-GB' => true],
                6,
            ],
            'always_available' => [
                ['always-available' => 'eng-US', 'eng-US' => true],
                3,
            ],
            'full' => [
                ['always-available' => 'eng-US', 'eng-US' => true, 'eng-GB' => true],
                7,
            ],
        ];
    }

    /**
     * @param string $languageCode
     * @param bool $alwaysAvailable
     * @param int $expectedIndicator
     *
     * @covers       \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator::generateLanguageIndicator
     * @dataProvider getLanguageIndicatorData
     */
    public function testGenerateLanguageIndicator(
        $languageCode,
        $alwaysAvailable,
        $expectedIndicator
    ) {
        $generator = $this->getMaskGenerator();

        $this->assertSame(
            $expectedIndicator,
            $generator->generateLanguageIndicator($languageCode, $alwaysAvailable)
        );
    }

    /**
     * Returns test data for {@link testGenerateLanguageIndicator()}.
     *
     * @return array
     */
    public static function getLanguageIndicatorData()
    {
        return [
            'not_available' => [
                'eng-GB',
                false,
                4,
            ],
            'always_available' => [
                'eng-US',
                true,
                3,
            ],
        ];
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator::isLanguageAlwaysAvailable
     */
    public function testIsLanguageAlwaysAvailable()
    {
        $generator = $this->getMaskGenerator();

        $this->assertTrue(
            $generator->isLanguageAlwaysAvailable(
                'eng-GB',
                [
                    'always-available' => 'eng-GB',
                    'eng-GB' => 'lala',
                ]
            )
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator::isLanguageAlwaysAvailable
     */
    public function testIsLanguageAlwaysAvailableOtherLanguage()
    {
        $generator = $this->getMaskGenerator();

        $this->assertFalse(
            $generator->isLanguageAlwaysAvailable(
                'eng-GB',
                [
                    'always-available' => 'eng-US',
                    'eng-GB' => 'lala',
                ]
            )
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator::isLanguageAlwaysAvailable
     */
    public function testIsLanguageAlwaysAvailableNoDefault()
    {
        $generator = $this->getMaskGenerator();

        $this->assertFalse(
            $generator->isLanguageAlwaysAvailable(
                'eng-GB',
                [
                    'eng-GB' => 'lala',
                ]
            )
        );
    }

    /**
     * @param int $languageMask
     * @param bool $expectedResult
     *
     * @covers       \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator::isAlwaysAvailable
     * @dataProvider isAlwaysAvailableProvider
     */
    public function testIsAlwaysAvailable($langMask, $expectedResult)
    {
        $generator = $this->getMaskGenerator();
        self::assertSame($expectedResult, $generator->isAlwaysAvailable($langMask));
    }

    /**
     * Returns test data for {@link testIsAlwaysAvailable()}.
     *
     * @return array
     */
    public function isAlwaysAvailableProvider()
    {
        return [
            [2, false],
            [3, true],
            [62, false],
            [14, false],
            [15, true],
        ];
    }

    /**
     * @covers       \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator::removeAlwaysAvailableFlag
     * @dataProvider removeAlwaysAvailableFlagProvider
     */
    public function testRemoveAlwaysAvailableFlag($langMask, $expectedResult)
    {
        $generator = $this->getMaskGenerator();
        self::assertSame($expectedResult, $generator->removeAlwaysAvailableFlag($langMask));
    }

    /**
     * Returns test data for {@link testRemoveAlwaysAvailableFlag}.
     *
     * @return array
     */
    public function removeAlwaysAvailableFlagProvider()
    {
        return [
            [3, 2],
            [7, 6],
            [14, 14],
            [62, 62],
        ];
    }

    /**
     * @param int $langMask
     * @param array $expectedResult
     *
     * @covers       \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator::extractLanguageIdsFromMask
     * @dataProvider languageIdsFromMaskProvider
     */
    public function testExtractLanguageIdsFromMask($langMask, array $expectedResult)
    {
        $generator = $this->getMaskGenerator();
        self::assertSame($expectedResult, $generator->extractLanguageIdsFromMask($langMask));
    }

    /**
     * Returns test data for {@link testExtractLanguageIdsFromMask}.
     *
     * @return array
     */
    public function languageIdsFromMaskProvider()
    {
        return [
            [
                2,
                [2],
            ],
            [
                15,
                [2, 4, 8],
            ],
            [
                62,
                [2, 4, 8, 16, 32],
            ],
        ];
    }

    /**
     * Returns the mask generator to test.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator
     */
    protected function getMaskGenerator()
    {
        return new MaskGenerator($this->getLanguageHandler());
    }

    /**
     * Returns a language handler mock.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Language\Handler
     */
    protected function getLanguageHandler()
    {
        if (!isset($this->languageHandler)) {
            $this->languageHandler = $this->getMock('eZ\\Publish\\SPI\\Persistence\\Content\\Language\\Handler');
            $this->languageHandler->expects($this->any())
                                  ->method('loadByLanguageCode')
                                  ->will(
                                      $this->returnCallback(
                                          function ($languageCode) {
                                              switch ($languageCode) {
                                                  case 'eng-US':
                                                      return new Language(
                                                          [
                                                              'id' => 2,
                                                              'languageCode' => 'eng-US',
                                                              'name' => 'US english',
                                                          ]
                                                      );
                                                  case 'eng-GB':
                                                      return new Language(
                                                          [
                                                              'id' => 4,
                                                              'languageCode' => 'eng-GB',
                                                              'name' => 'British english',
                                                          ]
                                                      );
                                              }
                                          }
                                      )
                                  );
        }

        return $this->languageHandler;
    }
}
