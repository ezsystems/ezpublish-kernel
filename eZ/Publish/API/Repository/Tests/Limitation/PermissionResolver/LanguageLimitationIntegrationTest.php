<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests\Limitation\PermissionResolver;

use eZ\Publish\API\Repository\Values\User\Limitation\LanguageLimitation;
use eZ\Publish\SPI\Limitation\Target;

/**
 * Integration test for chosen use cases of calls to PermissionResolver::canUser.
 */
class LanguageLimitationIntegrationTest extends BaseLimitationIntegrationTest
{
    private const LANG_ENG_GB = 'eng-GB';
    private const LANG_ENG_US = 'eng-US';
    private const LANG_GER_DE = 'ger-DE';

    /**
     * Data provider for testCanUserCreateContent.
     *
     * @see testCanUserCreateContent
     *
     * @return array
     */
    public function providerForCanUserCreateContent(): array
    {
        $limitationForGerman = new LanguageLimitation();
        $limitationForGerman->limitationValues = [self::LANG_GER_DE];

        $limitationForBritishEnglish = new LanguageLimitation();
        $limitationForBritishEnglish->limitationValues = [self::LANG_ENG_GB];

        $multilingualLimitation = new LanguageLimitation();
        $multilingualLimitation->limitationValues = [self::LANG_ENG_US, self::LANG_GER_DE];

        return [
            // trying to create German Content, so for British it's false
            [[$limitationForBritishEnglish], false],
            [[$limitationForGerman], true],
            // at least one multilingual limitation must match
            [[$multilingualLimitation], true],
        ];
    }

    /**
     * @dataProvider providerForCanUserCreateContent
     *
     * @param array $limitations
     * @param bool $expectedResult
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCanUserCreateContent(array $limitations, bool $expectedResult): void
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();
        $contentService = $repository->getContentService();
        $locationService = $repository->getLocationService();

        $this->loginAsEditorUserWithLimitations('content', 'create', $limitations);

        $folderType = $contentTypeService->loadContentTypeByIdentifier(
            'folder'
        );
        $contentCreateStruct = $contentService->newContentCreateStruct(
            $folderType,
            self::LANG_GER_DE
        );
        $targets = [
            $locationService->newLocationCreateStruct(2),
        ];

        $this->assertCanUser(
            $expectedResult,
            'content',
            'create',
            $limitations,
            $contentCreateStruct,
            $targets
        );
    }

    /**
     * Data provider for testCanUserEditContent and testCanUserPublishContent.
     *
     * @see testCanUserEditContent
     * @see testCanUserPublishContent
     */
    public function providerForCanUserEditOrPublishContent(): array
    {
        $limitationForGerman = new LanguageLimitation();
        $limitationForGerman->limitationValues = [self::LANG_GER_DE];

        $limitationForBritishEnglish = new LanguageLimitation();
        $limitationForBritishEnglish->limitationValues = [self::LANG_ENG_GB];

        $multilingualLimitation = new LanguageLimitation();
        $multilingualLimitation->limitationValues = [self::LANG_ENG_US, self::LANG_GER_DE];

        return [
            // dealing with British content, so true only for British Language Limitation
            [[$limitationForBritishEnglish], true],
            [[$limitationForGerman], false],
            [[$multilingualLimitation], false],
        ];
    }

    /**
     * @dataProvider providerForCanUserEditOrPublishContent
     *
     * @param array $limitations
     * @param bool $expectedResult
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCanUserEditContent(array $limitations, bool $expectedResult): void
    {
        $repository = $this->getRepository();
        $locationService = $repository->getLocationService();

        $content = $this->createFolder([self::LANG_ENG_GB => 'British Folder'], 2);
        $contentInfo = $content->contentInfo;
        $location = $locationService->loadLocation($contentInfo->mainLocationId);

        $this->loginAsEditorUserWithLimitations('content', 'edit', $limitations);

        $this->assertCanUser(
            $expectedResult,
            'content',
            'edit',
            $limitations,
            $contentInfo,
            [$location]
        );
    }

    /**
     * @dataProvider providerForCanUserEditOrPublishContent
     *
     * @param array $limitations
     * @param bool $expectedResult
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCanUserPublishContent(array $limitations, bool $expectedResult): void
    {
        $content = $this->createFolder([self::LANG_ENG_GB => 'British Folder'], 2);

        $this->loginAsEditorUserWithLimitations('content', 'publish', $limitations);

        $this->assertCanUser($expectedResult, 'content', 'publish', $limitations, $content);
    }

    /**
     * Data provider for testCanUserDeleteContent.
     *
     * @see testCanUserDeleteContent
     */
    public function providerForCanUserDeleteContent(): array
    {
        $limitationForGerman = new LanguageLimitation();
        $limitationForGerman->limitationValues = [self::LANG_GER_DE];

        $limitationForBritishEnglish = new LanguageLimitation();
        $limitationForBritishEnglish->limitationValues = [self::LANG_ENG_GB];

        $multilingualLimitation = new LanguageLimitation();
        $multilingualLimitation->limitationValues = [self::LANG_ENG_GB, self::LANG_GER_DE];

        return [
            [[$limitationForBritishEnglish], false],
            [[$limitationForGerman], false],
            // dealing with British and German content, so true only for multilingual Language Limitation
            [[$multilingualLimitation], true],
        ];
    }

    /**
     * @dataProvider providerForCanUserDeleteContent
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation[] $limitations
     * @param bool $expectedResult
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCanUserDeleteContent(array $limitations, bool $expectedResult): void
    {
        $content = $this->createFolder(
            [
                self::LANG_ENG_GB => 'British Folder',
                self::LANG_GER_DE => 'German Folder',
            ],
            2
        );

        $this->loginAsEditorUserWithLimitations('content', 'remove', $limitations);

        $target = (new Target\Version())->deleteTranslations($content->getVersionInfo()->languageCodes);
        $this->assertCanUser($expectedResult, 'content', 'remove', $limitations, $content, [$target]);
    }

    /**
     * Data provider for testCanUserDeleteContentTranslation.
     *
     * @see testCanUserDeleteContentTranslation
     */
    public function providerForCanUserDeleteContentTranslation(): iterable
    {
        $limitationForGerman = new LanguageLimitation();
        $limitationForGerman->limitationValues = [self::LANG_GER_DE];

        $limitationForBritishEnglish = new LanguageLimitation();
        $limitationForBritishEnglish->limitationValues = [self::LANG_ENG_GB];

        $multilingualLimitation = new LanguageLimitation();
        $multilingualLimitation->limitationValues = [self::LANG_ENG_US, self::LANG_GER_DE];

        yield 'Limitation with eng-GB should return true for eng-GB translation' => [
            [$limitationForBritishEnglish],
            self::LANG_ENG_GB,
            true,
        ];

        yield 'Limitation with ger-de should return false for eng-GB translation' => [
            [$limitationForGerman],
            self::LANG_ENG_GB,
            false,
        ];

        yield 'Limitation with neg-US and ger-de should return true for eng-US translation' => [
            [$multilingualLimitation],
            self::LANG_ENG_US,
            true,
        ];
    }

    /**
     * @dataProvider providerForCanUserDeleteContentTranslation
     *
     * @param \eZ\Publish\API\Repository\Values\User\Limitation[] $limitations
     * @param string $translation
     * @param bool $expectedResult
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCanUserDeleteContentTranslation(array $limitations, string $translation, bool $expectedResult): void
    {
        $content = $this->createFolder(
            [
                self::LANG_ENG_GB => 'British Folder',
                self::LANG_GER_DE => 'German Folder',
                self::LANG_ENG_US => 'US Folder',
            ],
            2
        );

        $this->loginAsEditorUserWithLimitations('content', 'remove', $limitations);

        $target = (new Target\Builder\VersionBuilder())->translateToAnyLanguageOf([$translation])->build();
        $this->assertCanUser($expectedResult, 'content', 'remove', $limitations, $content, [$target]);
    }
}
