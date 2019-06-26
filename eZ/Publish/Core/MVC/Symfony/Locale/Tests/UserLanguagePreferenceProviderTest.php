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
use eZ\Publish\API\Repository\UserPreferenceService;
use eZ\Publish\API\Repository\Values\UserPreference\UserPreference;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;

class UserLanguagePreferenceProviderTest extends TestCase
{
    private const LOCALE_FALLBACK = 'en';
    private const LANGUAGE_PREFERENCE_NAME = 'language';
    private const LANGUAGE_PREFERENCE_VALUE = 'no';

    /** @var \eZ\Publish\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface */
    private $userLanguagePreferenceProvider;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\HttpFoundation\RequestStack */
    private $requestStackMock;

    /** @var \eZ\Publish\API\Repository\UserPreferenceService */
    private $userPreferenceServiceMock;

    public function setUp()
    {
        $this->requestStackMock = $this->createMock(RequestStack::class);

        $userLanguagePreference = new UserPreference([
            'name' => self::LANGUAGE_PREFERENCE_NAME,
            'value' => self::LANGUAGE_PREFERENCE_VALUE,
        ]);

        $this->userPreferenceServiceMock = $this->createMock(UserPreferenceService::class);
        $this->userPreferenceServiceMock
            ->method('getUserPreference')
            ->with(self::LANGUAGE_PREFERENCE_NAME)
            ->willReturn($userLanguagePreference);

        $this->userLanguagePreferenceProvider = new UserLanguagePreferenceProvider(
            $this->requestStackMock,
            $this->userPreferenceServiceMock,
            $this->getLanguageCodesMap(),
            self::LOCALE_FALLBACK
        );
    }

    /**
     * @dataProvider providerForTestGetPreferredLanguages
     *
     * @param array $userLanguages
     * @param array $expectedEzLanguageCodes
     */
    public function testGetPreferredLanguagesWithoutUserLanguage(array $userLanguages, array $expectedEzLanguageCodes): void
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

        $userPreferenceServiceMock = $this->createMock(UserPreferenceService::class);
        $userPreferenceServiceMock
            ->method('getUserPreference')
            ->with(self::LANGUAGE_PREFERENCE_NAME)
            ->will($this->throwException(new NotFoundException('User Preference', self::LANGUAGE_PREFERENCE_NAME)));

        $userLanguagePreferenceProvider = new UserLanguagePreferenceProvider(
            $this->requestStackMock,
            $userPreferenceServiceMock,
            $this->getLanguageCodesMap(),
            self::LOCALE_FALLBACK
        );

        self::assertEquals(
            $expectedEzLanguageCodes,
            $userLanguagePreferenceProvider->getPreferredLanguages()
        );
    }

    /**
     * @dataProvider providerForTestGetPreferredLanguagesWithUserPreferredLanguage
     *
     * @param array $userLanguages
     * @param array $expectedEzLanguageCodes
     */
    public function testGetPreferredLanguagesWithUserPreferredLanguage(array $userLanguages, array $expectedEzLanguageCodes): void
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

        $userLanguagePreferenceProvider = new UserLanguagePreferenceProvider(
            $this->requestStackMock,
            $this->userPreferenceServiceMock,
            $this->getLanguageCodesMap(),
            self::LOCALE_FALLBACK
        );

        self::assertEquals(
            $expectedEzLanguageCodes,
            $userLanguagePreferenceProvider->getPreferredLanguages()
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
            [[], ['eng-GB', 'eng-US']],
            [['pl'], ['pol-PL']],
            [['fr'], ['fre-FR']],
            [['en'], ['eng-GB', 'eng-US']],
            [['en_us'], ['eng-US']],
        ];
    }

    /**
     * @see testGetPreferredLanguages
     *
     * @return array
     */
    public function providerForTestGetPreferredLanguagesWithUserPreferredLanguage(): array
    {
        return [
            [[], ['nor-NO', 'eng-GB', 'eng-US']],
            [['pl'], ['nor-NO', 'pol-PL']],
            [['fr'], ['nor-NO', 'fre-FR']],
            [['en'], ['nor-NO', 'eng-GB', 'eng-US']],
            [['en_us'], ['nor-NO', 'eng-US']],
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
