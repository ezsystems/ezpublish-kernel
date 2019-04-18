<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests\Limitation;

use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\API\Repository\Tests\BaseTest;
use eZ\Publish\API\Repository\Values\User\Limitation\LanguageLimitation;
use eZ\Publish\API\Repository\Values\User\User;

/**
 * Test cases for ContentService APIs calls made by user with LanguageLimitation on chosen policies.
 *
 * @uses \eZ\Publish\API\Repository\Values\User\Limitation\LanguageLimitation
 *
 * @group integration
 * @group authorization
 * @group language-limited-content-mgm
 */
class LanguageLimitationTest extends BaseTest
{
    /**
     * Create editor who is allowed to modify only specific translations of a Content item.
     *
     * @param array $allowedTranslationsList list of translations (language codes) which editor can modify.
     *
     * @return \eZ\Publish\API\Repository\Values\User\User
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    private function createEditorUserWithLanguageLimitation(array $allowedTranslationsList): User
    {
        $limitations = [
            // limitation for specific translations
            new LanguageLimitation(['limitationValues' => $allowedTranslationsList]),
        ];

        return $this->createUserWithPolicies(
            'editor',
            [
                ['module' => 'content', 'function' => 'read'],
                ['module' => 'content', 'function' => 'versionread'],
                ['module' => 'content', 'function' => 'view_embed'],
                ['module' => 'content', 'function' => 'create', 'limitations' => $limitations],
                ['module' => 'content', 'function' => 'edit', 'limitations' => $limitations],
                ['module' => 'content', 'function' => 'publish', 'limitations' => $limitations],
            ]
        );
    }

    /**
     * @return array
     * @see testCreateAndPublishContent
     */
    public function providerForCreateAndPublishContent(): array
    {
        // $names (as admin), $allowedTranslationsList (editor limitations)
        return [
            [
                ['ger-DE' => 'German Folder'],
                ['ger-DE'],
            ],
            [
                ['ger-DE' => 'German Folder', 'eng-GB' => 'British Folder'],
                ['ger-DE', 'eng-GB'],
            ],
        ];
    }

    /**
     * Test creating and publishing a fresh Content item in a language restricted by LanguageLimitation.
     *
     * @param array $names
     * @param array $allowedTranslationsList
     *
     * @dataProvider providerForCreateAndPublishContent
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCreateAndPublishContent(array $names, array $allowedTranslationsList): void
    {
        $repository = $this->getRepository();
        $repository->getPermissionResolver()->setCurrentUserReference(
            $this->createEditorUserWithLanguageLimitation($allowedTranslationsList)
        );

        $folder = $this->createFolder($names, 2);

        foreach ($names as $languageCode => $translatedName) {
            self::assertEquals(
                $translatedName,
                $folder->getField('name', $languageCode)->value->text
            );
        }
    }

    /**
     * Data provider for testPublishVersionWithLanguageLimitation.
     *
     * @return array
     * @see testPublishVersionIsNotAllowedIfModifiedOtherTranslations
     *
     * @see testPublishVersion
     */
    public function providerForPublishVersionWithLanguageLimitation(): array
    {
        // $names (as admin), $namesToUpdate (as editor), $allowedTranslationsList (editor limitations)
        return [
            [
                ['eng-US' => 'American Folder'],
                ['ger-DE' => 'Updated German Folder'],
                ['ger-DE'],
            ],
            [
                ['eng-US' => 'American Folder', 'ger-DE' => 'German Folder'],
                ['ger-DE' => 'Updated German Folder'],
                ['ger-DE'],
            ],
            [
                [
                    'eng-US' => 'American Folder',
                    'eng-GB' => 'British Folder',
                    'ger-DE' => 'German Folder',
                ],
                ['ger-DE' => 'Updated German Folder', 'eng-GB' => 'British Folder'],
                ['ger-DE', 'eng-GB'],
            ],
            [
                ['eng-US' => 'American Folder', 'ger-DE' => 'German Folder'],
                ['ger-DE' => 'Updated German Folder', 'eng-GB' => 'British Folder'],
                ['ger-DE', 'eng-GB'],
            ],
        ];
    }

    /**
     * Test publishing Version with translations restricted by LanguageLimitation.
     *
     * @param array $names
     * @param array $namesToUpdate
     * @param array $allowedTranslationsList
     *
     * @dataProvider providerForPublishVersionWithLanguageLimitation
     *
     * @covers \eZ\Publish\API\Repository\ContentService::createContentDraft
     * @covers \eZ\Publish\API\Repository\ContentService::updateContent
     * @covers \eZ\Publish\API\Repository\ContentService::publishVersion
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @throws \Exception
     */
    public function testPublishVersion(
        array $names,
        array $namesToUpdate,
        array $allowedTranslationsList
    ): void {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();

        $folder = $this->createFolder($names, 2);

        $repository->getPermissionResolver()->setCurrentUserReference(
            $this->createEditorUserWithLanguageLimitation($allowedTranslationsList)
        );

        $folderDraft = $contentService->createContentDraft($folder->contentInfo);
        $folderUpdateStruct = $contentService->newContentUpdateStruct();
        // set modified translation of Version to the first modified as multiple are not supported yet
        $folderUpdateStruct->initialLanguageCode = array_keys($namesToUpdate)[0];
        foreach ($namesToUpdate as $languageCode => $translatedName) {
            $folderUpdateStruct->setField('name', $translatedName, $languageCode);
        }
        $folderDraft = $contentService->updateContent(
            $folderDraft->getVersionInfo(),
            $folderUpdateStruct
        );
        $contentService->publishVersion($folderDraft->getVersionInfo());

        $folder = $contentService->loadContent($folder->id);
        $updatedNames = array_merge($names, $namesToUpdate);
        foreach ($updatedNames as $languageCode => $expectedValue) {
            self::assertEquals(
                $expectedValue,
                $folder->getField('name', $languageCode)->value->text,
                "Unexpected Field value for {$languageCode}"
            );
        }
    }

    /**
     * Test that publishing version with changes to translations outside limitation values throws unauthorized exception.
     *
     * @param array $names
     *
     * @dataProvider providerForPublishVersionWithLanguageLimitation
     *
     * @covers \eZ\Publish\API\Repository\ContentService::createContentDraft
     * @covers \eZ\Publish\API\Repository\ContentService::updateContent
     * @covers \eZ\Publish\API\Repository\ContentService::publishVersion
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ForbiddenException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testPublishVersionIsNotAllowedIfModifiedOtherTranslations(array $names): void
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();

        $folder = $this->createFolder($names, 2);
        $folderDraft = $contentService->createContentDraft($folder->contentInfo);
        $folderUpdateStruct = $contentService->newContentUpdateStruct();
        $folderUpdateStruct->setField('name', 'Updated American Folder', 'eng-US');
        $folderDraft = $contentService->updateContent(
            $folderDraft->getVersionInfo(),
            $folderUpdateStruct
        );

        // switch context to the user not allowed to publish eng-US
        $repository->getPermissionResolver()->setCurrentUserReference(
            $this->createEditorUserWithLanguageLimitation(['ger-DE'])
        );

        $this->expectException(UnauthorizedException::class);
        $contentService->publishVersion($folderDraft->getVersionInfo());
    }
}
