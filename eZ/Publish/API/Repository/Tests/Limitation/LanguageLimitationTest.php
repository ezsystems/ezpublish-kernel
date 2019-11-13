<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests\Limitation;

use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\API\Repository\Tests\BaseTest;
use eZ\Publish\API\Repository\Values\User\Limitation\LanguageLimitation;

class LanguageLimitationTest extends BaseTest
{
    /** @var string */
    private const ENG_US = 'eng-US';

    /** @var string */
    private const GER_DE = 'ger-DE';

    public function testPublishVersionTranslation(): void
    {
        $repository = $this->getRepository(false);
        $contentService = $repository->getContentService();
        $permissionResolver = $repository->getPermissionResolver();

        $publishedContent = $this->createFolder(
            [
                self::ENG_US => 'Published US',
                self::GER_DE => 'Published DE',
            ],
            $this->generateId('location', 2)
        );

        $draft = $contentService->createContentDraft($publishedContent->contentInfo);

        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $contentUpdateStruct->initialLanguageCode = self::GER_DE;

        $contentUpdateStruct->setField('name', 'Draft 1 DE', self::GER_DE);

        $contentService->updateContent($draft->versionInfo, $contentUpdateStruct);

        $user = $this->createUserWithPolicies(
            'user',
            [
                [
                    'module' => 'content',
                    'function' => 'publish',
                    'limitations' => [new LanguageLimitation(['limitationValues' => [self::GER_DE]])],
                ],
            ]
        );

        $admin = $permissionResolver->getCurrentUserReference();
        $permissionResolver->setCurrentUserReference($user);

        $contentService->publishVersion($draft->versionInfo, [self::GER_DE]);

        $permissionResolver->setCurrentUserReference($admin);
        $content = $contentService->loadContent($draft->contentInfo->id);
        $this->assertEquals(
            [
                self::ENG_US => 'Published US',
                self::GER_DE => 'Draft 1 DE',
            ],
            $content->fields['name']
        );
    }

    public function testthrowUnauthorizedExceptionWhilePublishVersionTranslation(): void
    {
        $this->expectException(UnauthorizedException::class);

        $repository = $this->getRepository(false);
        $contentService = $repository->getContentService();
        $permissionResolver = $repository->getPermissionResolver();

        $publishedContent = $this->createFolder(
            [
                self::ENG_US => 'Published US',
                self::GER_DE => 'Published DE',
            ],
            $this->generateId('location', 2)
        );

        $draft = $contentService->createContentDraft($publishedContent->contentInfo);

        $contentUpdateStruct = $contentService->newContentUpdateStruct();
        $contentUpdateStruct->initialLanguageCode = self::ENG_US;

        $contentUpdateStruct->setField('name', 'Draft 1 EN', self::ENG_US);

        $contentService->updateContent($draft->versionInfo, $contentUpdateStruct);

        $user = $this->createUserWithPolicies(
            'editor',
            [
                [
                    'module' => 'content',
                    'function' => 'publish',
                    'limitations' => [new LanguageLimitation(['limitationValues' => [self::GER_DE]])],
                ],
            ]
        );

        $admin = $permissionResolver->getCurrentUserReference();
        $permissionResolver->setCurrentUserReference($user);

        $contentService->publishVersion($draft->versionInfo, [self::ENG_US]);
    }
}
