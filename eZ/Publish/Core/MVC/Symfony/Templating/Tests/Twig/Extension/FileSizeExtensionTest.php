<?php

/**
 * File containing the FileSizeExtensionTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\Templating\Tests\Twig\Extension;

use Twig_Test_IntegrationTestCase;
use eZ\Publish\Core\MVC\Symfony\Templating\Twig\Extension\FileSizeExtension;
use Symfony\Component\Translation\TranslatorInterface;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface;

/**
 * Class FileSizeExtensionTest.
 */
class FileSizeExtensionTest extends Twig_Test_IntegrationTestCase
{
    /**
     * @param string $locale
     */
    protected $locale;

    /**
     * @param array $suffixes
     */
    protected $suffixes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB');

    /**
     * @param TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $translatorMock;

    /**
     * @param ConfigResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configResolverInterfaceMock;

    /**
     * @param LocaleConverterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeConverterInterfaceMock;

    /**
     * @param string $locale
     * @param string $defaultLocale
     */
    protected function setConfigurationLocale($locale, $defaultLocale)
    {
        locale_set_default($defaultLocale);
        $this->locale = $locale;
    }

    /**
     * @return string $locale
     */
    public function getLocale()
    {
        return array($this->locale);
    }

    /**
     * @return array
     */
    protected function getExtensions()
    {
        return array(
            new FileSizeExtension($this->getTranslatorInterfaceMock(), $this->suffixes, $this->getConfigResolverInterfaceMock(), $this->getLocaleConverterInterfaceMock()),
        );
    }

    /**
     * @return string
     */
    protected function getFixturesDir()
    {
        return __DIR__ . '/_fixtures/functions/ez_file_size';
    }

    /**
     * @return ConfigResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getConfigResolverInterfaceMock()
    {
        $configResolverInterfaceMock = $this->getMock('eZ\Publish\Core\MVC\ConfigResolverInterface');
        $configResolverInterfaceMock->expects($this->any())
            ->method('getParameter')
            ->with('languages')
            ->will($this->returnValue($this->getLocale()));

        return $configResolverInterfaceMock;
    }

    /**
     * @return LocaleConverterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getLocaleConverterInterfaceMock()
    {
        $this->localeConverterInterfaceMock = $this->getMock('eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface');
        $this->localeConverterInterfaceMock->expects($this->any())
        ->method('convertToPOSIX')
        ->will(
            $this->returnValueMap(
                array(
                    array('fre-FR', 'fr-FR'),
                    array('eng-GB', 'en-GB'),
                )
            )
        );

        return $this->localeConverterInterfaceMock;
    }

    /**
     * @return TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTranslatorInterfaceMock()
    {
        $that = $this;
        $this->translatorMock = $this->getMock('Symfony\Component\Translation\TranslatorInterface');
        $this->translatorMock
            ->expects($this->any())->method('trans')->will(
                $this->returnCallback(
                    function ($suffixes) use ($that) {
                        foreach ($that->getLocale() as $value) {
                            if ($value === 'fre-FR') {
                                return $suffixes . ' French version';
                            } elseif ($value === 'eng-GB') {
                                return $suffixes . ' English version';
                            } else {
                                return $suffixes . ' wrong local so we take the default one which is en-GB here';
                            }
                        }
                    }
                )
            );

        return $this->translatorMock;
    }
}
