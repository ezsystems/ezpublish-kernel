<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\SiteAccessAware\Tests\Language;

use eZ\Publish\Core\Repository\SiteAccessAware\Language\LanguageResolver;
use PHPUnit\Framework\TestCase;

class LanguageResolverTest extends TestCase
{
    /**
     * @covers \eZ\Publish\Core\Repository\SiteAccessAware\Language\AbstractLanguageResolver::getPrioritizedLanguages
     *
     * @dataProvider getDataForTestGetPrioritizedLanguages
     *
     * @param array $expectedPrioritizedLanguagesList
     * @param array $configLanguages
     * @param bool $defaultShowAllTranslations
     * @param null|array $forcedLanguages
     * @param null|string $contextLanguage
     */
    public function testGetPrioritizedLanguages(
        array $expectedPrioritizedLanguagesList,
        array $configLanguages,
        bool $defaultShowAllTranslations,
        ?array $forcedLanguages,
        ?string $contextLanguage
    ) {
        // note: "use always available" does not affect this test
        $defaultUseAlwaysAvailable = true;

        $languageResolver = new LanguageResolver(
            $configLanguages,
            $defaultUseAlwaysAvailable,
            $defaultShowAllTranslations
        );

        $languageResolver->setContextLanguage($contextLanguage);

        self::assertEquals(
            $expectedPrioritizedLanguagesList,
            $languageResolver->getPrioritizedLanguages($forcedLanguages)
        );
    }

    /**
     * Data provider for testGetPrioritizedLanguages.
     *
     * @see testGetPrioritizedLanguages
     *
     * @return array
     */
    public function getDataForTestGetPrioritizedLanguages(): array
    {
        return [
            [
                ['eng-GB', 'pol-PL'], ['eng-GB', 'pol-PL'], false, null, null,
            ],
            [
                [], ['eng-GB', 'pol-PL'], false, [], null, ],
            [
                ['ger-DE'], ['eng-GB', 'pol-PL'], false, ['ger-DE'], null,
            ],
            [
                [], ['eng-GB', 'pol-PL'], true, null, null,
            ],
            [
                ['ger-DE', 'eng-GB', 'pol-PL'], ['eng-GB', 'pol-PL'], false, null, 'ger-DE',
            ],
        ];
    }
}
