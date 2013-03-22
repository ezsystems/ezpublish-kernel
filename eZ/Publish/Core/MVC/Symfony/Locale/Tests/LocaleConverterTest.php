<?php
/**
 * File containing the LocaleConvertTest class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Locale\Tests;

use eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverter;

class LocaleConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverter
     */
    private $localeConverter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    private $conversionMap;

    protected function setUp()
    {
        parent::setUp();
        $this->conversionMap = array(
            'eng-GB'       => 'en_GB',
            'eng-US'       => 'en_US',
            'fre-FR'       => 'fr_FR',
            'ger-DE'       => 'de_DE',
            'nor-NO'       => 'no_NO',
            'cro-HR'       => 'hr_HR',
        );

        $this->logger = $this->getMock( 'Psr\\Log\\LoggerInterface' );
        $this->localeConverter = new LocaleConverter( $this->conversionMap, $this->logger );
    }

    /**
     * @dataProvider convertToPOSIXProvider
     *
     * @covers eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverter::convertToPOSIX
     *
     * @param $ezpLocale
     * @param $expected
     */
    public function testConvertToPOSIX( $ezpLocale, $expected )
    {
        if ( $expected === null )
        {
            $this->logger
                ->expects( $this->once() )
                ->method( 'warning' );
        }

        $this->assertSame( $expected, $this->localeConverter->convertToPOSIX( $ezpLocale ) );
    }

    public function convertToPOSIXProvider()
    {
        return array(
            array( 'eng-GB', 'en_GB' ),
            array( 'eng-US', 'en_US' ),
            array( 'fre-FR', 'fr_FR' ),
            array( 'chi-CN', null ),
            array( 'epo-EO', null ),
            array( 'nor-NO', 'no_NO' ),
        );
    }

    /**
     * @dataProvider convertToEzProvider
     *
     * @covers eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverter::convertToEz
     *
     * @param $posixLocale
     * @param $expected
     */
    public function testConvertToEz( $posixLocale, $expected )
    {
        if ( $expected === null )
        {
            $this->logger
                ->expects( $this->once() )
                ->method( 'warning' );
        }

        $this->assertSame( $expected, $this->localeConverter->convertToEz( $posixLocale ) );
    }

    public function convertToEzProvider()
    {
        return array(
            array( 'en_GB', 'eng-GB' ),
            array( 'en_US', 'eng-US' ),
            array( 'fr_FR', 'fre-FR' ),
            array( 'zh-CN', null ),
            array( 'eo', null ),
            array( 'no_NO', 'nor-NO' ),
        );
    }
}
