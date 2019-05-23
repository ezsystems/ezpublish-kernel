<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Tests\Limitation\Target\Builder;

use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\SPI\Limitation\Target\Builder\VersionBuilder;
use eZ\Publish\SPI\Limitation\Target;
use PHPUnit\Framework\TestCase;

/**
 * @covers \eZ\Publish\SPI\Limitation\Target\Builder\VersionBuilder
 */
class VersionBuilderTest extends TestCase
{
    /**
     * Data provider for testBuild.
     *
     * @see testBuild
     *
     * @return array
     */
    public function providerForTestBuild(): array
    {
        $versionStatuses = [
            VersionInfo::STATUS_DRAFT,
            VersionInfo::STATUS_PUBLISHED,
            VersionInfo::STATUS_ARCHIVED,
        ];

        $data = [];
        foreach ($versionStatuses as $versionStatus) {
            $languagesList = ['ger-DE', 'eng-US', 'eng-GB'];
            $contentTypeIdsList = [1, 2];
            $initialLanguageCode = 'eng-US';
            $fields = [
                new Field(['languageCode' => 'ger-DE']),
                new Field(['languageCode' => 'ger-DE']),
                new Field(['languageCode' => 'eng-US']),
            ];
            $updateTranslationsLanguageCodes = ['ger-DE', 'eng-US'];

            $data[] = [
                new Target\Version(
                    [
                        'newStatus' => $versionStatus,
                        'allLanguageCodesList' => $languagesList,
                        'allContentTypeIdsList' => $contentTypeIdsList,
                        'forUpdateLanguageCodesList' => $updateTranslationsLanguageCodes,
                        'forUpdateInitialLanguageCode' => $initialLanguageCode,
                    ]
                ),
                $versionStatus,
                $initialLanguageCode,
                $fields,
                $languagesList,
                $contentTypeIdsList,
            ];

            // no published content
            $data[] = [
                new Target\Version(
                    [
                        'newStatus' => $versionStatus,
                        'allLanguageCodesList' => $languagesList,
                        'allContentTypeIdsList' => $contentTypeIdsList,
                        'forUpdateLanguageCodesList' => $updateTranslationsLanguageCodes,
                        'forUpdateInitialLanguageCode' => $initialLanguageCode,
                    ]
                ),
                $versionStatus,
                $initialLanguageCode,
                $fields,
                $languagesList,
                $contentTypeIdsList,
            ];
        }

        return $data;
    }

    /**
     * @covers       \eZ\Publish\SPI\Limitation\Target\Builder\VersionBuilder::build
     *
     * @dataProvider providerForTestBuild
     *
     * @param \eZ\Publish\SPI\Limitation\Target\Version $expectedTargetVersion
     * @param int $newStatus
     * @param string $initialLanguageCode
     * @param \eZ\Publish\API\Repository\Values\Content\Field[] $newFields
     * @param string[] $languagesList
     * @param int[] $contentTypeIdsList
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testBuild(
        Target\Version $expectedTargetVersion,
        int $newStatus,
        string $initialLanguageCode,
        array $newFields,
        array $languagesList,
        array $contentTypeIdsList
    ): void {
        $versionBuilder = new VersionBuilder();
        $versionBuilder
            ->changeStatusTo($newStatus)
            ->updateFieldsTo($initialLanguageCode, $newFields)
            ->translateToAnyLanguageOf($languagesList)
            ->createFromAnyContentTypeOf($contentTypeIdsList);

        self::assertInstanceOf(VersionBuilder::class, $versionBuilder);
        self::assertEquals($expectedTargetVersion, $versionBuilder->build());
    }
}
