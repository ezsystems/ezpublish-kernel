<?php

/**
 * File containing the ContentTypeServiceAuthorizationTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Tests;

/**
 * Test case for operations in the ContentTypeServiceAuthorization using in memory storage.
 *
 * @see eZ\Publish\API\Repository\ContentTypeService
 * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadAnonymousUser
 * @group integration
 * @group authorization
 */
class ContentTypeServiceAuthorizationTest extends BaseContentTypeServiceTest
{
    /**
     * Test for the createContentTypeGroup() method.
     *
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentTypeGroup
     */
    public function testCreateContentTypeGroupThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $creatorId = $this->generateId('user', 14);
        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.
        $userService = $repository->getUserService();
        $contentTypeService = $repository->getContentTypeService();

        $groupCreate = $contentTypeService->newContentTypeGroupCreateStruct(
            'new-group'
        );
        // $creatorId is the ID of the administrator user
        $groupCreate->creatorId = $creatorId;
        $groupCreate->creationDate = $this->createDateTime();
        /* @todo uncomment when support for multilingual names and descriptions is added EZP-24776
        $groupCreate->mainLanguageCode = 'ger-DE';
        $groupCreate->names = array( 'eng-GB' => 'A name.' );
        $groupCreate->descriptions = array( 'eng-GB' => 'A description.' );
        */

        // Set anonymous user
        $repository->setCurrentUser($userService->loadUser($anonymousUserId));

        // This call will fail with a "UnauthorizedException"
        $contentTypeService->createContentTypeGroup($groupCreate);
        /* END: Use Case */
    }

    /**
     * Test for the updateContentTypeGroup() method.
     *
     * @see \eZ\Publish\API\Repository\ContentTypeService::updateContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testUpdateContentTypeGroup
     */
    public function testUpdateContentTypeGroupThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $modifierId = $this->generateId('user', 42);
        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.
        $userService = $repository->getUserService();
        $contentTypeService = $repository->getContentTypeService();

        $group = $contentTypeService->loadContentTypeGroupByIdentifier('Setup');

        $groupUpdate = $contentTypeService->newContentTypeGroupUpdateStruct();

        $groupUpdate->identifier = 'Teardown';
        // $modifierId is the ID of a random user
        $groupUpdate->modifierId = $modifierId;
        $groupUpdate->modificationDate = $this->createDateTime();
        /* @todo uncomment when support for multilingual names and descriptions is added EZP-24776
        $groupUpdate->mainLanguageCode = 'eng-GB';

        $groupUpdate->names = array(
            'eng-GB' => 'A name',
            'eng-US' => 'A name',
        );
        $groupUpdate->descriptions = array(
            'eng-GB' => 'A description',
            'eng-US' => 'A description',
        );
        */

        // Set anonymous user
        $repository->setCurrentUser($userService->loadUser($anonymousUserId));

        // This call will fail with a "UnauthorizedException"
        $contentTypeService->updateContentTypeGroup($group, $groupUpdate);
        /* END: Use Case */
    }

    /**
     * Test for the deleteContentTypeGroup() method.
     *
     * @see \eZ\Publish\API\Repository\ContentTypeService::deleteContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testDeleteContentTypeGroup
     */
    public function testDeleteContentTypeGroupThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.
        $userService = $repository->getUserService();
        $contentTypeService = $repository->getContentTypeService();

        $groupCreate = $contentTypeService->newContentTypeGroupCreateStruct(
            'new-group'
        );
        $contentTypeService->createContentTypeGroup($groupCreate);

        // ...

        $group = $contentTypeService->loadContentTypeGroupByIdentifier('new-group');

        // Set anonymous user
        $repository->setCurrentUser($userService->loadUser($anonymousUserId));

        // This call will fail with a "UnauthorizedException"
        $contentTypeService->deleteContentTypeGroup($group);
        /* END: Use Case */
    }

    /**
     * Test for the createContentType() method.
     *
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentType()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentType
     */
    public function testCreateContentTypeThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $creatorId = $this->generateId('user', 14);
        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.
        $userService = $repository->getUserService();
        $contentTypeService = $repository->getContentTypeService();

        $groupCreate = $contentTypeService->newContentTypeGroupCreateStruct(
            'new-group'
        );
        // $creatorId is the ID of the administrator user
        $groupCreate->creatorId = $creatorId;
        $groupCreate->creationDate = $this->createDateTime();
        $contentTypeGroup = $contentTypeService->createContentTypeGroup($groupCreate);

        $typeCreate = $contentTypeService->newContentTypeCreateStruct(
            'new-type'
        );
        // $creatorId is the ID of the administrator user
        $typeCreate->creatorId = $creatorId;
        $typeCreate->creationDate = $this->createDateTime();
        $typeCreate->mainLanguageCode = 'eng-GB';
        $typeCreate->names = ['eng-GB' => 'A name.'];
        $typeCreate->descriptions = ['eng-GB' => 'A description.'];

        $titleFieldCreateStruct = $contentTypeService->newFieldDefinitionCreateStruct('title', 'ezstring');
        $titleFieldCreateStruct->names = ['eng-GB' => 'Title'];
        $titleFieldCreateStruct->descriptions = ['eng-GB' => 'The Title'];
        $titleFieldCreateStruct->fieldGroup = 'content';
        $titleFieldCreateStruct->position = 10;
        $titleFieldCreateStruct->isTranslatable = true;
        $titleFieldCreateStruct->isRequired = true;
        $titleFieldCreateStruct->isSearchable = true;
        $typeCreate->addFieldDefinition($titleFieldCreateStruct);

        // Set anonymous user
        $repository->setCurrentUser($userService->loadUser($anonymousUserId));

        // This call will fail with a "UnauthorizedException"
        $contentTypeService->createContentType($typeCreate, [$contentTypeGroup]);
        /* END: Use Case */
    }

    /**
     * Test for the updateContentTypeDraft() method.
     *
     * @see \eZ\Publish\API\Repository\ContentTypeService::updateContentTypeDraft()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testUpdateContentTypeDraft
     */
    public function testUpdateContentTypeDraftThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        $modifierId = $this->generateId('user', 42);
        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.
        $contentTypeDraft = $this->createContentTypeDraft();

        $typeUpdate = $contentTypeService->newContentTypeUpdateStruct();
        $typeUpdate->identifier = 'news-article';
        $typeUpdate->remoteId = '4cf35f5166fd31bf0cda859dc837e095daee9833';
        $typeUpdate->urlAliasSchema = 'url@alias|scheme';
        $typeUpdate->nameSchema = '@name@scheme@';
        $typeUpdate->isContainer = true;
        $typeUpdate->mainLanguageCode = 'ger-DE';
        $typeUpdate->defaultAlwaysAvailable = false;
        // $modifierId is the ID of a random user
        $typeUpdate->modifierId = $modifierId;
        $typeUpdate->modificationDate = $this->createDateTime();
        $typeUpdate->names = [
            'eng-GB' => 'News article',
            'ger-DE' => 'Nachrichten-Artikel',
        ];
        $typeUpdate->descriptions = [
            'eng-GB' => 'A news article',
            'ger-DE' => 'Ein Nachrichten-Artikel',
        ];

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser($userService->loadUser($anonymousUserId));

        // This call will fail with a "UnauthorizedException"
        $contentTypeService->updateContentTypeDraft($contentTypeDraft, $typeUpdate);
        /* END: Use Case */
    }

    /**
     * Test for the addFieldDefinition() method.
     *
     * @see \eZ\Publish\API\Repository\ContentTypeService::addFieldDefinition()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testAddFieldDefinition
     */
    public function testAddFieldDefinitionThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.
        $contentTypeDraft = $this->createContentTypeDraft();

        $fieldDefCreate = $contentTypeService->newFieldDefinitionCreateStruct('tags', 'string');
        $fieldDefCreate->names = [
            'eng-GB' => 'Tags',
            'ger-DE' => 'Schlagworte',
        ];
        $fieldDefCreate->descriptions = [
            'eng-GB' => 'Tags of the blog post',
            'ger-DE' => 'Schlagworte des Blog-Eintrages',
        ];
        $fieldDefCreate->fieldGroup = 'blog-meta';
        $fieldDefCreate->position = 1;
        $fieldDefCreate->isTranslatable = true;
        $fieldDefCreate->isRequired = true;
        $fieldDefCreate->isInfoCollector = false;
        $fieldDefCreate->validatorConfiguration = [
            'StringLengthValidator' => [
                'minStringLength' => 0,
                'maxStringLength' => 0,
            ],
        ];
        $fieldDefCreate->fieldSettings = [
            'textRows' => 10,
        ];
        $fieldDefCreate->isSearchable = true;

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser($userService->loadUser($anonymousUserId));

        // This call will fail with a "UnauthorizedException"
        $contentTypeService->addFieldDefinition($contentTypeDraft, $fieldDefCreate);
        /* END: Use Case */
    }

    /**
     * Test for the removeFieldDefinition() method.
     *
     * @see \eZ\Publish\API\Repository\ContentTypeService::removeFieldDefinition()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testRemoveFieldDefinition
     */
    public function testRemoveFieldDefinitionThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.
        $contentTypeDraft = $this->createContentTypeDraft();

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser($userService->loadUser($anonymousUserId));

        $bodyField = $contentTypeDraft->getFieldDefinition('body');

        // This call will fail with a "UnauthorizedException"
        $contentTypeService->removeFieldDefinition($contentTypeDraft, $bodyField);
        /* END: Use Case */
    }

    /**
     * Test for the updateFieldDefinition() method.
     *
     * @see \eZ\Publish\API\Repository\ContentTypeService::updateFieldDefinition()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testUpdateFieldDefinition
     */
    public function testUpdateFieldDefinitionThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.
        $contentTypeDraft = $this->createContentTypeDraft();

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser($userService->loadUser($anonymousUserId));

        $bodyField = $contentTypeDraft->getFieldDefinition('body');

        $bodyUpdateStruct = $contentTypeService->newFieldDefinitionUpdateStruct();
        $bodyUpdateStruct->identifier = 'blog-body';
        $bodyUpdateStruct->names = [
            'eng-GB' => 'Blog post body',
            'ger-DE' => 'Blog-Eintrags-Textkörper',
        ];
        $bodyUpdateStruct->descriptions = [
            'eng-GB' => 'Blog post body of the blog post',
            'ger-DE' => 'Blog-Eintrags-Textkörper des Blog-Eintrages',
        ];
        $bodyUpdateStruct->fieldGroup = 'updated-blog-content';
        $bodyUpdateStruct->position = 3;
        $bodyUpdateStruct->isTranslatable = false;
        $bodyUpdateStruct->isRequired = false;
        $bodyUpdateStruct->isInfoCollector = true;
        $bodyUpdateStruct->validatorConfiguration = [];
        $bodyUpdateStruct->fieldSettings = [
            'textRows' => 60,
        ];
        $bodyUpdateStruct->isSearchable = false;

        // This call will fail with a "UnauthorizedException"
        $contentTypeService->updateFieldDefinition(
            $contentTypeDraft,
            $bodyField,
            $bodyUpdateStruct
        );
        /* END: Use Case */
    }

    /**
     * Test for the publishContentTypeDraft() method.
     *
     * @see \eZ\Publish\API\Repository\ContentTypeService::publishContentTypeDraft()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testPublishContentTypeDraft
     */
    public function testPublishContentTypeDraftThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.
        $contentTypeDraft = $this->createContentTypeDraft();

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser($userService->loadUser($anonymousUserId));

        // This call will fail with a "UnauthorizedException"
        $contentTypeService->publishContentTypeDraft($contentTypeDraft);
        /* END: Use Case */
    }

    /**
     * Test for the createContentTypeDraft() method.
     *
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentTypeDraft()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentTypeDraft
     */
    public function testCreateContentTypeDraftThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.
        $contentTypeService = $repository->getContentTypeService();

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser($userService->loadUser($anonymousUserId));

        $commentType = $contentTypeService->loadContentTypeByIdentifier('comment');

        // This call will fail with a "UnauthorizedException"
        $contentTypeService->createContentTypeDraft($commentType);
        /* END: Use Case */
    }

    /**
     * Test for the deleteContentType() method.
     *
     * @see \eZ\Publish\API\Repository\ContentTypeService::deleteContentType()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testDeleteContentType
     */
    public function testDeleteContentTypeThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.
        $contentTypeService = $repository->getContentTypeService();

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser($userService->loadUser($anonymousUserId));

        $commentType = $contentTypeService->loadContentTypeByIdentifier('comment');

        // This call will fail with a "UnauthorizedException"
        $contentTypeService->deleteContentType($commentType);
        /* END: Use Case */
    }

    /**
     * Test for the copyContentType() method.
     *
     * @see \eZ\Publish\API\Repository\ContentTypeService::copyContentType()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCopyContentType
     */
    public function testCopyContentTypeThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.
        $contentTypeService = $repository->getContentTypeService();

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser($userService->loadUser($anonymousUserId));

        $commentType = $contentTypeService->loadContentTypeByIdentifier('comment');

        // This call will fail with a "UnauthorizedException"
        $contentTypeService->copyContentType($commentType);
        /* END: Use Case */
    }

    /**
     * Test for the assignContentTypeGroup() method.
     *
     * @see \eZ\Publish\API\Repository\ContentTypeService::assignContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testAssignContentTypeGroup
     */
    public function testAssignContentTypeGroupThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.
        $contentTypeService = $repository->getContentTypeService();

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser($userService->loadUser($anonymousUserId));

        $mediaGroup = $contentTypeService->loadContentTypeGroupByIdentifier('Media');
        $folderType = $contentTypeService->loadContentTypeByIdentifier('folder');

        // This call will fail with a "UnauthorizedException"
        $contentTypeService->assignContentTypeGroup($folderType, $mediaGroup);
        /* END: Use Case */
    }

    /**
     * Test for the unassignContentTypeGroup() method.
     *
     * @see \eZ\Publish\API\Repository\ContentTypeService::unassignContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testUnassignContentTypeGroup
     */
    public function testUnassignContentTypeGroupThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        $anonymousUserId = $this->generateId('user', 10);
        /* BEGIN: Use Case */
        // $anonymousUserId is the ID of the "Anonymous" user in a eZ
        // Publish demo installation.
        $contentTypeService = $repository->getContentTypeService();

        $folderType = $contentTypeService->loadContentTypeByIdentifier('folder');

        $mediaGroup = $contentTypeService->loadContentTypeGroupByIdentifier('Media');
        $contentGroup = $contentTypeService->loadContentTypeGroupByIdentifier('Content');

        // May not unassign last group
        $contentTypeService->assignContentTypeGroup($folderType, $mediaGroup);

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser($userService->loadUser($anonymousUserId));

        // This call will fail with a "UnauthorizedException"
        $contentTypeService->unassignContentTypeGroup($folderType, $contentGroup);
        /* END: Use Case */
    }
}
