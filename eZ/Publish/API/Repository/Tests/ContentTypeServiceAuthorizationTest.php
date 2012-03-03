<?php
/**
 * File containing the ContentTypeServiceAuthorizationTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\StringLengthValidatorStub;

/**
 * Test case for operations in the ContentTypeServiceAuthorization using in memory storage.
 *
 * @see eZ\Publish\API\Repository\ContentTypeService
 * @d epends eZ\Publish\API\Repository\Tests\RepositoryTest::testSetCurrentUser
 * @depends eZ\Publish\API\Repository\Tests\UserServiceTest::testLoadAnonymousUser
 * @group integration
 */
class ContentTypeServiceAuthorizationTest extends BaseTest
{
    /**
     * Test for the createContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentTypeGroup
     */
    public function testCreateContentTypeGroupThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService        = $repository->getUserService();
        $contentTypeService = $repository->getContentTypeService();

        $groupCreate = $contentTypeService->newContentTypeGroupCreateStruct(
            'new-group'
        );
        $groupCreate->creatorId        = 23;
        $groupCreate->creationDate     = new \DateTime();
        $groupCreate->mainLanguageCode = 'de-DE';
        $groupCreate->names            = array( 'eng-US' => 'A name.' );
        $groupCreate->descriptions     = array( 'eng-US' => 'A description.' );

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with a "UnauthorizedException"
        $contentTypeService->createContentTypeGroup( $groupCreate );
        /* END: Use Case */
    }

    /**
     * Test for the updateContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::updateContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testUpdateContentTypeGroup
     */
    public function testUpdateContentTypeGroupThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService        = $repository->getUserService();
        $contentTypeService = $repository->getContentTypeService();

        $group = $contentTypeService->loadContentTypeGroupByIdentifier( 'Setup' );

        $groupUpdate = $contentTypeService->newContentTypeGroupUpdateStruct();

        $groupUpdate->identifier       = 'Teardown';
        $groupUpdate->modifierId       = 42;
        $groupUpdate->modificationDate = new \DateTime();
        $groupUpdate->mainLanguageCode = 'eng-US';

        $groupUpdate->names = array(
            'eng-US' => 'A name',
            'eng-GB' => 'A name',
        );
        $groupUpdate->descriptions = array(
            'eng-US' => 'A description',
            'eng-GB' => 'A description',
        );

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with a "UnauthorizedException"
        $contentTypeService->updateContentTypeGroup( $group, $groupUpdate );
        /* END: Use Case */
    }

    /**
     * Test for the deleteContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::deleteContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testDeleteContentTypeGroup
     */
    public function testDeleteContentTypeGroupThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService        = $repository->getUserService();
        $contentTypeService = $repository->getContentTypeService();

        $groupCreate = $contentTypeService->newContentTypeGroupCreateStruct(
            'new-group'
        );
        $contentTypeService->createContentTypeGroup( $groupCreate );

        // ...

        $group = $contentTypeService->loadContentTypeGroupByIdentifier( 'new-group' );

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with a "UnauthorizedException"
        $contentTypeService->deleteContentTypeGroup( $group );
        /* END: Use Case */
    }

    /**
     * Test for the updateContentTypeDraft() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::updateContentTypeDraft()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testUpdateContentTypeDraft
     */
    public function testUpdateContentTypeDraftThrowsUnauthorizedException()
    {
        $repository         = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        /* BEGIN: Use Case */
        $contentTypeDraft = $this->createContentTypeDraft();

        $typeUpdate = $contentTypeService->newContentTypeUpdateStruct();
        $typeUpdate->identifier             = 'news-article';
        $typeUpdate->remoteId               = '4cf35f5166fd31bf0cda859dc837e095daee9833';
        $typeUpdate->urlAliasSchema         = 'url@alias|scheme';
        $typeUpdate->nameSchema             = '@name@scheme@';
        $typeUpdate->isContainer            = true;
        $typeUpdate->mainLanguageCode       = 'de-DE';
        $typeUpdate->defaultAlwaysAvailable = false;
        $typeUpdate->modifierId             = 42;
        $typeUpdate->modificationDate       = new \DateTime();
        $typeUpdate->names                  = array(
            'eng-US' => 'News article',
            'de-DE'  => 'Nachrichten-Artikel',
        );
        $typeUpdate->descriptions = array(
            'eng-US' => 'A news article',
            'de-DE'  => 'Ein Nachrichten-Artikel',
        );

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with a "UnauthorizedException"
        $contentTypeService->updateContentTypeDraft( $contentTypeDraft, $typeUpdate );
        /* END: Use Case */
    }

    /**
     * Test for the addFieldDefinition() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::addFieldDefinition()
     * @depens eZ\Publish\API\Repository\Tests\ContentTypeServiceAuthorizationTest::testAddFieldDefinition
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testAddFieldDefinition
     */
    public function testAddFieldDefinitionThrowsUnauthorizedException()
    {
        $repository         = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        /* BEGIN: Use Case */
        $contentTypeDraft = $this->createContentTypeDraft();

        $fieldDefCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'tags', 'string'
        );
        $fieldDefCreate->names = array(
            'eng-US' => 'Tags',
            'de-DE' => 'Schlagworte',
        );
        $fieldDefCreate->descriptions = array(
            'eng-US' => 'Tags of the blog post',
            'de-DE' => 'Schlagworte des Blog-Eintrages',
        );
        $fieldDefCreate->fieldGroup      = 'blog-meta';
        $fieldDefCreate->position        = 1;
        $fieldDefCreate->isTranslatable  = true;
        $fieldDefCreate->isRequired      = true;
        $fieldDefCreate->isInfoCollector = false;
        $fieldDefCreate->validators      = array(
            new StringLengthValidatorStub(),
        );
        $fieldDefCreate->fieldSettings = array(
            'textblockheight' => 10
        );
        $fieldDefCreate->isSearchable = true;

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with a "UnauthorizedException"
        $contentTypeService->addFieldDefinition( $contentTypeDraft, $fieldDefCreate );
        /* END: Use Case */
    }

    /**
     * Test for the removeFieldDefinition() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::removeFieldDefinition()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testRemoveFieldDefinition
     */
    public function testRemoveFieldDefinitionThrowsUnauthorizedException()
    {
        $repository         = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        /* BEGIN: Use Case */
        $contentTypeDraft = $this->createContentTypeDraft();

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        $bodyField = $contentTypeDraft->getFieldDefinition( 'body' );

        // This call will fail with a "UnauthorizedException"
        $contentTypeService->removeFieldDefinition( $contentTypeDraft, $bodyField );
        /* END: Use Case */
    }

    /**
     * Test for the updateFieldDefinition() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::updateFieldDefinition()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testUpdateFieldDefinition
     */
    public function testUpdateFieldDefinitionThrowsUnauthorizedException()
    {
        $repository         = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        /* BEGIN: Use Case */
        $contentTypeDraft = $this->createContentTypeDraft();

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        $bodyField = $contentTypeDraft->getFieldDefinition( 'body' );

        $bodyUpdateStruct = $contentTypeService->newFieldDefinitionUpdateStruct();
        $bodyUpdateStruct->identifier = 'blog-body';
        $bodyUpdateStruct->names = array(
            'eng-US' => 'Blog post body',
            'de-DE' => 'Blog-Eintrags-Textkörper',
        );
        $bodyUpdateStruct->descriptions = array(
            'eng-US' => 'Blog post body of the blog post',
            'de-DE' => 'Blog-Eintrags-Textkörper des Blog-Eintrages',
        );
        $bodyUpdateStruct->fieldGroup      = 'updated-blog-content';
        $bodyUpdateStruct->position        = 3;
        $bodyUpdateStruct->isTranslatable  = false;
        $bodyUpdateStruct->isRequired      = false;
        $bodyUpdateStruct->isInfoCollector = true;
        $bodyUpdateStruct->validators      = array();
        $bodyUpdateStruct->fieldSettings = array(
            'textblockheight' => 60
        );
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
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::publishContentTypeDraft()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testPublishContentTypeDraft
     */
    public function testPublishContentTypeDraftThrowsUnauthorizedException()
    {
        $repository         = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        /* BEGIN: Use Case */
        $contentTypeDraft = $this->createContentTypeDraft();

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with a "UnauthorizedException"
        $contentTypeService->publishContentTypeDraft( $contentTypeDraft );
        /* END: Use Case */
    }

    /**
     * Test for the createContentTypeDraft() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentTypeDraft()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentTypeDraft
     */
    public function testCreateContentTypeDraftThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        $commentType = $contentTypeService->loadContentTypeByIdentifier( 'comment' );

        // This call will fail with a "UnauthorizedException"
        $contentTypeService->createContentTypeDraft( $commentType );
        /* END: Use Case */
    }

    /**
     * Test for the deleteContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::deleteContentType()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testDeleteContentType
     */
    public function testDeleteContentTypeThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        $commentType = $contentTypeService->loadContentTypeByIdentifier( 'comment' );

        // This call will fail with a "UnauthorizedException"
        $contentTypeService->deleteContentType( $commentType );
        /* END: Use Case */
    }

    /**
     * Test for the copyContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::copyContentType()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCopyContentType
     */
    public function testCopyContentTypeThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        $commentType = $contentTypeService->loadContentTypeByIdentifier( 'comment' );

        // This call will fail with a "UnauthorizedException"
        $contentTypeService->copyContentType( $commentType );
        /* END: Use Case */
    }

    /**
     * Test for the assignContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::assignContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testAssignContentTypeGroup
     */
    public function testAssignContentTypeGroupThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        $mediaGroup = $contentTypeService->loadContentTypeGroupByIdentifier( 'Media' );
        $folderType = $contentTypeService->loadContentTypeByIdentifier( 'folder' );

        // This call will fail with a "UnauthorizedException"
        $contentTypeService->assignContentTypeGroup( $folderType, $mediaGroup );
        /* END: Use Case */
    }

    /**
     * Test for the unassignContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::unassignContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testUnassignContentTypeGroup
     */
    public function testUnassignContentTypeGroupThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $folderType = $contentTypeService->loadContentTypeByIdentifier( 'folder' );

        $mediaGroup   = $contentTypeService->loadContentTypeGroupByIdentifier( 'Media' );
        $contentGroup = $contentTypeService->loadContentTypeGroupByIdentifier( 'Content' );

        // May not unassign last group
        $contentTypeService->assignContentTypeGroup( $folderType, $mediaGroup );

        // Load the user service
        $userService = $repository->getUserService();

        // Set anonymous user
        $repository->setCurrentUser( $userService->loadAnonymousUser() );

        // This call will fail with a "UnauthorizedException"
        $contentTypeService->unassignContentTypeGroup( $folderType, $contentGroup );
        /* END: Use Case */
    }

    /**
     * Creates a fully functional ContentTypeDraft and returns it.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     */
    protected function createContentTypeDraft()
    {
        $repository = $this->getRepository();

        /* BEGIN: Inline */
        $contentTypeService = $repository->getContentTypeService();

        $groups = array(
            $contentTypeService->loadContentTypeGroupByIdentifier( 'Content' ),
            $contentTypeService->loadContentTypeGroupByIdentifier( 'Setup' )
        );

        $typeCreate = $contentTypeService->newContentTypeCreateStruct( 'blog-post' );
        $typeCreate->mainLanguageCode = 'eng-US';
        $typeCreate->remoteId         = '384b94a1bd6bc06826410e284dd9684887bf56fc';
        $typeCreate->urlAliasSchema   = 'url|scheme';
        $typeCreate->nameSchema       = 'name|scheme';
        $typeCreate->names = array(
            'eng-US' => 'Blog post',
            'de-DE'  => 'Blog-Eintrag',
        );
        $typeCreate->descriptions = array(
            'eng-US' => 'A blog post',
            'de-DE'  => 'Ein Blog-Eintrag',
        );
        $typeCreate->creatorId    = 23;
        $typeCreate->creationDate = new \DateTime();

        $titleFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'title', 'string'
        );
        $titleFieldCreate->names = array(
            'eng-US' => 'Title',
            'de-DE'  => 'Titel',
        );
        $titleFieldCreate->descriptions = array(
            'eng-US' => 'Title of the blog post',
            'de-DE'  => 'Titel des Blog-Eintrages',
        );
        $titleFieldCreate->fieldGroup      = 'blog-content';
        $titleFieldCreate->position        = 1;
        $titleFieldCreate->isTranslatable  = true;
        $titleFieldCreate->isRequired      = true;
        $titleFieldCreate->isInfoCollector = false;
        $titleFieldCreate->validators      = array(
            new StringLengthValidatorStub(),
        );
        $titleFieldCreate->fieldSettings = array(
            'textblockheight' => 10
        );
        $titleFieldCreate->isSearchable = true;

        $typeCreate->addFieldDefinition( $titleFieldCreate );

        $bodyFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'body', 'text'
        );
        $bodyFieldCreate->names = array(
            'eng-US' => 'Body',
            'de-DE'  => 'Textkörper',
        );
        $bodyFieldCreate->descriptions = array(
            'eng-US' => 'Body of the blog post',
            'de-DE'  => 'Textkörper des Blog-Eintrages',
        );
        $bodyFieldCreate->fieldGroup      = 'blog-content';
        $bodyFieldCreate->position        = 2;
        $bodyFieldCreate->isTranslatable  = true;
        $bodyFieldCreate->isRequired      = true;
        $bodyFieldCreate->isInfoCollector = false;
        $bodyFieldCreate->validators      = array(
            new StringLengthValidatorStub(),
        );
        $bodyFieldCreate->fieldSettings = array(
            'textblockheight' => 80
        );
        $bodyFieldCreate->isSearchable = true;

        $typeCreate->addFieldDefinition( $bodyFieldCreate );

        $contentTypeDraft = $contentTypeService->createContentType(
            $typeCreate,
            $groups
        );
        /* END: Inline */

        return $contentTypeDraft;
    }
}
