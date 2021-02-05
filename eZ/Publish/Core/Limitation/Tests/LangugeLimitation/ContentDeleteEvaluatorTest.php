<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Limitation\Tests\LanguageLimitation;

use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\Core\Limitation\LanguageLimitation\ContentDeleteEvaluator;
use eZ\Publish\SPI\Limitation\Target;
use PHPUnit\Framework\TestCase;

final class ContentDeleteEvaluatorTest extends TestCase
{
    /**
     * @dataProvider dataProviderForAccept
     */
    public function testAccept(Target\Version $targetVersion, bool $expected): void
    {
        self::assertSame(
            $expected,
            (new ContentDeleteEvaluator())->accept($targetVersion)
        );
    }

    public function dataProviderForAccept(): iterable
    {
        yield [
            $this->getTergetVersion(['eng-GB', 'ger-DE']),
            true,
        ];

        yield [
            $this->getTergetVersion([]),
            false,
        ];

        yield [
            new Target\Version(),
            false,
        ];
    }

    /**
     * @dataProvider dataProviderForEvaluate
     */
    public function testEvaluate(Target\Version $targetVersion, Limitation $limitationValue, bool $expected): void
    {
        self::assertSame(
            $expected,
            (new ContentDeleteEvaluator())->evaluate($targetVersion, $limitationValue)
        );
    }

    public function dataProviderForEvaluate(): iterable
    {
        yield 'same_values' => [
            $this->getTergetVersion(['eng-GB', 'ger-DE']),
            $this->getLanguageLimitation(['eng-GB', 'ger-DE']),
            true,
        ];

        yield 'missing_fr_limitation' => [
            $this->getTergetVersion(['eng-GB', 'ger-DE', 'fre-FR']),
            $this->getLanguageLimitation(['eng-GB', 'ger-DE']),
            false,
        ];

        yield 'extra_fr_limitation' => [
            $this->getTergetVersion(['eng-GB', 'ger-DE']),
            $this->getLanguageLimitation(['eng-GB', 'ger-DE', 'fre-FR']),
            true,
        ];

        yield 'separable_values' => [
            $this->getTergetVersion(['eng-GB']),
            $this->getLanguageLimitation(['fre-FR']),
            false,
        ];
    }

    private function getTergetVersion(array $languageCodes): Target\Version
    {
        return (new Target\Version())->deleteTranslations($languageCodes);
    }

    private function getLanguageLimitation(array $languageCodes): Limitation\LanguageLimitation
    {
        return new Limitation\LanguageLimitation(['limitationValues' => $languageCodes]);
    }
}
