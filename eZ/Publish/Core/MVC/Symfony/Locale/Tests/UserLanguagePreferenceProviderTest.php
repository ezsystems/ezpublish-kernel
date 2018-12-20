<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Locale\Tests;

use eZ\Publish\Core\MVC\Symfony\Locale\UserLanguagePreferenceProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Yaml\Yaml;

class UserLanguagePreferenceProviderTest extends TestCase
{
    /** @var \eZ\Publish\Core\MVC\Symfony\Locale\UserLanguagePreferenceProvider */
    private $userLanguagePreferenceProvider;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\HttpFoundation\RequestStack */
    private $requestStackMock;

    public function setUp()
    {
        $this->requestStackMock = $this->createMock(RequestStack::class);

        $this->userLanguagePreferenceProvider = new UserLanguagePreferenceProvider(
            $this->requestStackMock,
            $this->getLanguageCodesMap()
        );
    }

    /**
     * @dataProvider providerForTestGetPreferredLanguages
     *
     * @param array $userLanguages
     * @param array $expectedEzLanguageCodes
     */
    public function testGetPreferredLanguages(array $userLanguages, array $expectedEzLanguageCodes)
    {
        $request = new Request();
        $request->headers = new HeaderBag(
            [
                'Accept-Language' => implode(', ', $userLanguages),
            ]
        );
        $this
            ->requestStackMock
            ->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        self::assertEquals(
            $expectedEzLanguageCodes,
            $this->userLanguagePreferenceProvider->getPreferredLanguages()
        );
    }

    /**
     * @see testGetPreferredLanguages
     *
     * @return array
     */
    public function providerForTestGetPreferredLanguages(): array
    {
        return [
            [['pl'], ['pol-PL']],
            [['pol-PL'], ['pol-PL']],
            [['fr'], ['fre-FR']],
            [['en'], ['eng-GB', 'eng-US']],
        ];
    }

    private function getLanguageCodesMap(): array
    {
        $config = Yaml::parseFile(
            realpath(dirname(__DIR__, 6) . '/Bundle/EzPublishCoreBundle/Resources/config/locale.yml')
        );

        return $config['parameters']['ezpublish.locale.browser_map'];
    }
}
