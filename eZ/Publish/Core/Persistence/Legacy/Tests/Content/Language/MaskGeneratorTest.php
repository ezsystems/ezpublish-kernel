<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\Language\MaskGeneratorTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content\Language;

use eZ\Publish\Core\Persistence\Legacy\Tests\Content\LanguageAwareTestCase;
use eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator;
use eZ\Publish\SPI\Persistence\Content\Language;

/**
 * Test case for Language MaskGenerator
 */
class MaskGeneratorTest extends LanguageAwareTestCase
{

    /**
     * @param array $languages
     * @param int $expectedMask
     *
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator::generateLanguageMask
     * @dataProvider getLanguageMaskData
     */
    public function testGenerateLanguageMask( array $languages, $expectedMask )
    {
        $generator = $this->getMaskGenerator();

        $this->assertSame(
            $expectedMask,
            $generator->generateLanguageMask( $languages )
        );
    }

    /**
     * Returns test data for {@link testGenerateLanguageMask()}
     *
     * @return array
     */
    public static function getLanguageMaskData()
    {
        return array(
            'error' => array(
                array(),
                0,
            ),
            'single_lang' => array(
                array( 'eng-GB' => true ),
                4,
            ),
            'multi_lang' => array(
                array( 'eng-US' => true, 'eng-GB' => true ),
                6,
            ),
            'always_available' => array(
                array( 'always-available' => 'eng-US', 'eng-US' => true ),
                3,
            ),
            'full' => array(
                array( 'always-available' => 'eng-US', 'eng-US' => true, 'eng-GB' => true ),
                7,
            ),
        );
    }

    /**
     * @param string $languageCode
     * @param boolean $alwaysAvailable
     * @param int $expectedIndicator
     *
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator::generateLanguageIndicator
     * @dataProvider getLanguageIndicatorData
     */
    public function testGenerateLanguageIndicator(
        $languageCode, $alwaysAvailable, $expectedIndicator )
    {
        $generator = $this->getMaskGenerator();

        $this->assertSame(
            $expectedIndicator,
            $generator->generateLanguageIndicator( $languageCode, $alwaysAvailable )
        );
    }

    /**
     * Returns test data for {@link testGenerateLanguageIndicator()}
     *
     * @return array
     */
    public static function getLanguageIndicatorData()
    {
        return array(
            'not_available' => array(
                'eng-GB',
                false,
                4,
            ),
            'always_available' => array(
                'eng-US',
                true,
                3,
            ),
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator::isLanguageAlwaysAvailable
     *
     * @return void
     */
    public function testIsLanguageAlwaysAvailable()
    {
        $generator = $this->getMaskGenerator();

        $this->assertTrue(
            $generator->isLanguageAlwaysAvailable(
                'eng-GB',
                array(
                    'always-available' => 'eng-GB',
                    'eng-GB' => 'lala'
                )
            )
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator::isLanguageAlwaysAvailable
     *
     * @return void
     */
    public function testIsLanguageAlwaysAvailableOtherLanguage()
    {
        $generator = $this->getMaskGenerator();

        $this->assertFalse(
            $generator->isLanguageAlwaysAvailable(
                'eng-GB',
                array(
                    'always-available' => 'eng-US',
                    'eng-GB' => 'lala'
                )
            )
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator::isLanguageAlwaysAvailable
     *
     * @return void
     */
    public function testIsLanguageAlwaysAvailableNoDefault()
    {
        $generator = $this->getMaskGenerator();

        $this->assertFalse(
            $generator->isLanguageAlwaysAvailable(
                'eng-GB',
                array(
                    'eng-GB' => 'lala'
                )
            )
        );
    }

    /**
     * @param int $languageMask
     * @param boolean $expectedResult
     *
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator::isAlwaysAvailable
     * @dataProvider isAlwaysAvailableProvider
     */
    public function testIsAlwaysAvailable( $langMask, $expectedResult )
    {
        $generator = $this->getMaskGenerator();
        self::assertSame( $expectedResult, $generator->isAlwaysAvailable( $langMask ) );
    }

    /**
     * Returns test data for {@link testIsAlwaysAvailable()}
     *
     * @return array
     */
    public function isAlwaysAvailableProvider()
    {
        return array(
            array( 2, false ),
            array( 3, true ),
            array( 62, false ),
            array( 14, false ),
            array( 15, true )
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator::removeAlwaysAvailableFlag
     * @dataProvider removeAlwaysAvailableFlagProvider
     */
    public function testRemoveAlwaysAvailableFlag( $langMask, $expectedResult )
    {
        $generator = $this->getMaskGenerator();
        self::assertSame( $expectedResult, $generator->removeAlwaysAvailableFlag( $langMask ) );
    }

    /**
     * Returns test data for {@link testRemoveAlwaysAvailableFlag}
     *
     * @return array
     */
    public function removeAlwaysAvailableFlagProvider()
    {
        return array(
            array( 3, 2 ),
            array( 7, 6 ),
            array( 14, 14 ),
            array( 62, 62 )
        );
    }

    /**
     * @param int $langMask
     * @param array $expectedResult
     *
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator::extractLanguageIdsFromMask
     * @dataProvider languageIdsFromMaskProvider
     */
    public function testExtractLanguageIdsFromMask( $langMask, array $expectedResult )
    {
        $generator = $this->getMaskGenerator();
        self::assertSame( $expectedResult, $generator->extractLanguageIdsFromMask( $langMask ) );
    }

    /**
     * Returns test data for {@link testExtractLanguageIdsFromMask}
     *
     * @return array
     */
    public function languageIdsFromMaskProvider()
    {
        return array(
            array(
                2,
                array( 2 )
            ),
            array(
                15,
                array( 2, 4, 8 )
            ),
            array(
                62,
                array( 2, 4, 8, 16, 32 )
            ),
        );
    }

    /**
     * Returns the mask generator to test
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Language\MaskGenerator
     */
    protected function getMaskGenerator()
    {
        return new MaskGenerator( $this->getLanguageHandler() );
    }

    /**
     * Returns a language handler mock
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Language\Handler
     */
    protected function getLanguageHandler()
    {
        if ( !isset( $this->languageHandler ) )
        {
            $this->languageHandler = $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\Language\\Handler' );
            $this->languageHandler->expects( $this->any() )
                ->method( 'loadByLanguageCode' )
                ->will(
                    $this->returnCallback(
                        function ( $languageCode )
                        {
                            switch ( $languageCode )
                            {
                                case 'eng-US':
                                    return new Language(
                                        array(
                                            'id' => 2,
                                            'languageCode' => 'eng-US',
                                            'name' => 'US english'
                                        )
                                    );
                                case 'eng-GB':
                                    return new Language(
                                        array(
                                            'id' => 4,
                                            'languageCode' => 'eng-GB',
                                            'name' => 'British english'
                                        )
                                    );
                            }
                        }
                    )
                );
        }
        return $this->languageHandler;
    }
}
