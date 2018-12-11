<?php

/**
 * File containing the LanguageLimitationTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests\Values\User\Limitation;

use eZ\Publish\API\Repository\Values\User\Limitation\LanguageLimitation;
use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;

/**
 * Test case for the {@link \eZ\Publish\API\Repository\Values\User\Limitation\LanguageLimitation}
 * class.
 *
 * @see \eZ\Publish\API\Repository\Values\User\Limitation
 * @see \eZ\Publish\API\Repository\Values\User\Limitation\LanguageLimitation
 * @group integration
 * @group limitation
 */
class LanguageLimitationTest extends BaseLimitationTest
{
    /**
     * Test for the LanguageLimitation.
     *
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\LanguageLimitation
     *
     * @throws \ErrorException
     */
    public function testLanguageLimitationAllow()
    {
        $repository = $this->getRepository();

        $contentId = $this->generateId('content', 58);
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier('Editor');

        $editPolicy = null;
        foreach ($role->getPolicies() as $policy) {
            if ('content' != $policy->module || 'edit' != $policy->function) {
                continue;
            }
            $editPolicy = $policy;
            break;
        }

        if (null === $editPolicy) {
            throw new \ErrorException('No content:edit policy found.');
        }

        // Only allow eng-GB content
        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new LanguageLimitation(
                array('limitationValues' => array('eng-GB'))
            )
        );
        $roleService->updatePolicy($editPolicy, $policyUpdate);

        $roleService->assignRoleToUser($role, $user);

        $contentService = $repository->getContentService();

        $repository->setCurrentUser($user);

        $contentUpdate = $contentService->newContentUpdateStruct();
        $contentUpdate->setField('name', 'Contact Me');

        $draft = $contentService->createContentDraft(
            $contentService->loadContentInfo($contentId)
        );

        // Update content object
        $draft = $contentService->updateContent(
            $draft->versionInfo,
            $contentUpdate
        );

        $contentService->publishVersion($draft->versionInfo);
        /* END: Use Case */

        $this->assertEquals(
            'Contact Me',
            $contentService->loadContent($contentId)
                ->getFieldValue('name')->text
        );
    }

    /**
     * Test for the LanguageLimitation.
     *
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\LanguageLimitation
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     *
     * @throws \ErrorException
     */
    public function testLanguageLimitationForbid()
    {
        $repository = $this->getRepository();

        $contentId = $this->generateId('content', 58);
        /* BEGIN: Use Case */
        $user = $this->createUserVersion1();

        $roleService = $repository->getRoleService();

        $role = $roleService->loadRoleByIdentifier('Editor');

        $editPolicy = null;
        foreach ($role->getPolicies() as $policy) {
            if ('content' != $policy->module || 'edit' != $policy->function) {
                continue;
            }
            $editPolicy = $policy;
            break;
        }

        if (null === $editPolicy) {
            throw new \ErrorException('No content:edit policy found.');
        }

        // Only allow eng-US content
        $policyUpdate = $roleService->newPolicyUpdateStruct();
        $policyUpdate->addLimitation(
            new LanguageLimitation(
                array('limitationValues' => array('eng-US'))
            )
        );
        $roleService->updatePolicy($editPolicy, $policyUpdate);

        $roleService->assignRoleToUser($role, $user);

        $contentService = $repository->getContentService();

        $repository->setCurrentUser($user);

        $contentUpdate = $contentService->newContentUpdateStruct();
        $contentUpdate->setField('name', 'Contact Me');

        // This call will fail with an UnauthorizedException
        $contentService->createContentDraft(
            $contentService->loadContentInfo($contentId)
        );
        /* END: Use Case */
    }

    /**
     * Test for the LanguageLimitation.
     *
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\LanguageLimitation
     */
    public function testUserIsAllowedToTranslateContent()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();

        $this->createRoleWithPolicies('Publisher', [
            ['module' => 'content', 'function' => 'read'],
            ['module' => 'content', 'function' => 'create'],
            ['module' => 'content', 'function' => 'publish'],
        ]);

        $publisherUser = $this->createCustomUserWithLogin(
            'publisher',
            'publisher@example.com',
            'Publishers',
            'Publisher'
        );

        $this->createRoleWithPolicies('Translator', [
            ['module' => 'content', 'function' => 'read'],
            ['module' => 'content', 'function' => 'edit'],
            ['module' => 'content', 'function' => 'versionread'],
            [
                'module' => 'content',
                'function' => 'translate',
                'limitations' => [new LanguageLimitation(['limitationValues' => ['eng-GB']])],
            ],
        ]);

        $translatorUser = $this->createCustomUserWithLogin(
            'translator',
            'translator@example.com',
            'Translators',
            'Translator'
        );

        $repository->getPermissionResolver()->setCurrentUserReference($publisherUser);
        // this will create eng-US Content
        $content = $this->createWikiPage();

        $contentService->loadContent($content->id);

        $repository->getPermissionResolver()->setCurrentUserReference($translatorUser);

        $translationCreateStruct = $contentService->newTranslationCreateStruct($content->contentInfo);
        $translationCreateStruct->initialLanguageCode = 'eng-GB';
        $translationCreateStruct->setField('title', 'An awesome wiki page (GB)');

        // translate content object
        $translatedContentDraft = $contentService->translateVersion(
            $content->versionInfo,
            $translationCreateStruct
        );

        $repository->getPermissionResolver()->setCurrentUserReference($publisherUser);
        $contentService->publishVersion($translatedContentDraft->versionInfo);

        $this->assertEquals(
            'An awesome wiki page (GB)',
            $contentService->loadContent($content->id, ['eng-GB'])
                ->getFieldValue('title')->text
        );

        // Update translated content
        $repository->getPermissionResolver()->setCurrentUserReference($translatorUser);

        $contentUpdate = $contentService->newContentUpdateStruct();
        $contentUpdate->initialLanguageCode = 'eng-GB';
        $contentUpdate->setField('title', 'An awesome wiki page GB - second update');

        $draft = $contentService->createContentDraft(
            $contentService->loadContentInfo($content->id)
        );

        // Update content object
        $contentService->updateContent(
            $draft->versionInfo,
            $contentUpdate
        );

        $repository->getPermissionResolver()->setCurrentUserReference($publisherUser);
        $contentService->publishVersion($draft->versionInfo);

        $this->assertEquals(
            'An awesome wiki page GB - second update',
            $contentService->loadContent($content->id, ['eng-GB'])
                ->getFieldValue('title')->text
        );
    }

    /**
     * Test for the LanguageLimitation.
     *
     * @see \eZ\Publish\API\Repository\Values\User\Limitation\LanguageLimitation
     */
    public function testUserIsNotAllowedToTranslateContent()
    {
        $repository = $this->getRepository();
        $contentService = $repository->getContentService();

        $this->createRoleWithPolicies('Publisher', [
            ['module' => 'content', 'function' => 'read'],
            ['module' => 'content', 'function' => 'create'],
            ['module' => 'content', 'function' => 'publish'],
        ]);

        $publisherUser = $this->createCustomUserWithLogin(
            'publisher',
            'publisher@example.com',
            'Publishers',
            'Publisher'
        );

        $this->createRoleWithPolicies('Translator', [
            ['module' => 'content', 'function' => 'read'],
            ['module' => 'content', 'function' => 'edit'],
            ['module' => 'content', 'function' => 'versionread'],
            [
                'module' => 'content',
                'function' => 'translate',
                'limitations' => [new LanguageLimitation(['limitationValues' => ['eng-US']])],
            ],
        ]);

        $translatorUser = $this->createCustomUserWithLogin(
            'translator',
            'translator@example.com',
            'Translators',
            'Translator'
        );

        /* BEGIN: Use Case */
        $repository->getPermissionResolver()->setCurrentUserReference($publisherUser);
        $content = $this->createWikiPage();

        $contentService->loadContent($content->id);

        $repository->getPermissionResolver()->setCurrentUserReference($translatorUser);

        $translationCreateStruct = $contentService->newTranslationCreateStruct($content->contentInfo);
        $translationCreateStruct->initialLanguageCode = 'eng-GB';
        $translationCreateStruct->setField('title', 'An awesome wiki page GB');

        /* END: Use Case */

        // This call will fail with an UnauthorizedException
        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('User does not have access to \'translate\' \'content\' with: contentId \'' . $content->id . '\'');

        $contentService->translateVersion(
            $content->versionInfo,
            $translationCreateStruct
        );
    }
}
