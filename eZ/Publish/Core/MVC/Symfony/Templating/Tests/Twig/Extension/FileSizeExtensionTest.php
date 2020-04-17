<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Templating\Tests\Twig\Extension;

use eZ\Publish\Core\MVC\Symfony\Templating\Twig\Extension\FileSizeExtension;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Test\IntegrationTestCase;

/**
 * Class FileSizeExtensionTest.
 */
class FileSizeExtensionTest extends IntegrationTestCase
{
    /**
     * @param string $locale
     */
    protected $locale;

    /**
     * @param array $suffixes
     */
    protected $suffixes = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB'];

    /**
     * @param TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $translatorMock;

    /**
     * @param ConfigResolverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $configResolverInterfaceMock;

    /**
     * @param LocaleConverterInterface|\PHPUnit\Framework\MockObject\MockObject
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

    protected function setUp(): void
    {
        $this->markTestSkipped('Skipped since NumberFormatter is behaving differently on PHP 7.3. Needs investigation.');

        parent::setUp();
    }

    /**
     * @return string $locale
     */
    public function getLocale()
    {
        return [$this->locale];
    }

    /**
     * @return array
     */
    protected function getExtensions()
    {
        return [
            new FileSizeExtension($this->getTranslatorInterfaceMock(), $this->suffixes, $this->getConfigResolverInterfaceMock(), $this->getLocaleConverterInterfaceMock()),
        ];
    }

    /**
     * @return string
     */
    protected function getFixturesDir()
    {
        return __DIR__ . '/_fixtures/functions/ez_file_size';
    }

    /**
     * @return ConfigResolverInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getConfigResolverInterfaceMock()
    {
        $configResolverInterfaceMock = $this->createMock(ConfigResolverInterface::class);
        $configResolverInterfaceMock->expects($this->any())
            ->method('getParameter')
            ->with('languages')
            ->will($this->returnValue($this->getLocale()));

        return $configResolverInterfaceMock;
    }

    /**
     * @return LocaleConverterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getLocaleConverterInterfaceMock()
    {
        $this->localeConverterInterfaceMock = $this->createMock(LocaleConverterInterface::class);
        $this->localeConverterInterfaceMock->expects($this->any())
        ->method('convertToPOSIX')
        ->will(
            $this->returnValueMap(
                [
                    ['fre-FR', 'fr-FR'],
                    ['eng-GB', 'en-GB'],
                ]
            )
        );

        return $this->localeConverterInterfaceMock;
    }

    /**
     * @return TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getTranslatorInterfaceMock()
    {
        $that = $this;
        $this->translatorMock = $this->createMock(TranslatorInterface::class);
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
