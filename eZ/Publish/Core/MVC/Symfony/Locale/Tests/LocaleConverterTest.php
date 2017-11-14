<?php

/**
 * File containing the LocaleConvertTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Locale\Tests;

use eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverter;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class LocaleConverterTest extends TestCase
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
            'eng-GB' => 'en_GB',
            'eng-US' => 'en_US',
            'fre-FR' => 'fr_FR',
            'ger-DE' => 'de_DE',
            'nor-NO' => 'no_NO',
            'cro-HR' => 'hr_HR',
        );

        $this->logger = $this->createMock(LoggerInterface::class);
        $this->localeConverter = new LocaleConverter($this->conversionMap, $this->logger);
    }

    /**
     * @dataProvider convertToPOSIXProvider
     *
     * @covers \eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverter::convertToPOSIX
     *
     * @param $ezpLocale
     * @param $expected
     */
    public function testConvertToPOSIX($ezpLocale, $expected)
    {
        if ($expected === null) {
            $this->logger
                ->expects($this->once())
                ->method('warning');
        }

        $this->assertSame($expected, $this->localeConverter->convertToPOSIX($ezpLocale));
    }

    public function convertToPOSIXProvider()
    {
        return array(
            array('eng-GB', 'en_GB'),
            array('eng-US', 'en_US'),
            array('fre-FR', 'fr_FR'),
            array('chi-CN', null),
            array('epo-EO', null),
            array('nor-NO', 'no_NO'),
        );
    }

    /**
     * @dataProvider convertToEzProvider
     *
     * @covers \eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverter::convertToEz
     *
     * @param $posixLocale
     * @param $expected
     */
    public function testConvertToEz($posixLocale, $expected)
    {
        if ($expected === null) {
            $this->logger
                ->expects($this->once())
                ->method('warning');
        }

        $this->assertSame($expected, $this->localeConverter->convertToEz($posixLocale));
    }

    public function convertToEzProvider()
    {
        return array(
            array('en_GB', 'eng-GB'),
            array('en_US', 'eng-US'),
            array('fr_FR', 'fre-FR'),
            array('zh-CN', null),
            array('eo', null),
            array('no_NO', 'nor-NO'),
        );
    }
}
