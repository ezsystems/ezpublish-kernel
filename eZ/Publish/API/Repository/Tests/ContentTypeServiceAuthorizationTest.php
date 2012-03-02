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
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService        = $repository->getUserService();
        $contentTypeService = $repository->getContentTypeService();

        $this->markTestIncomplete( "@TODO: Test for ContentTypeService::updateContentTypeDraft() is not implemented." );
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
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService        = $repository->getUserService();
        $contentTypeService = $repository->getContentTypeService();

        $this->markTestIncomplete( "@TODO: Test for ContentTypeService::addFieldDefinition() is not implemented." );
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
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService        = $repository->getUserService();
        $contentTypeService = $repository->getContentTypeService();

        $this->markTestIncomplete( "@TODO: Test for ContentTypeService::removeFieldDefinition() is not implemented." );
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
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService        = $repository->getUserService();
        $contentTypeService = $repository->getContentTypeService();

        $this->markTestIncomplete( "@TODO: Test for ContentTypeService::updateFieldDefinition() is not implemented." );
    }

    /**
     * Test for the publishContentTypeDraft() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::publishContentTypeDraft()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testPublishContentTypeDraftThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService        = $repository->getUserService();
        $contentTypeService = $repository->getContentTypeService();

        $this->markTestIncomplete( "@TODO: Test for ContentTypeService::publishContentTypeDraft() is not implemented." );
    }

    /**
     * Test for the createContentTypeDraft() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentTypeDraft()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCreateContentTypeDraftThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService        = $repository->getUserService();
        $contentTypeService = $repository->getContentTypeService();

        $this->markTestIncomplete( "@TODO: Test for ContentTypeService::createContentTypeDraft() is not implemented." );
    }

    /**
     * Test for the deleteContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::deleteContentType()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testDeleteContentTypeThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService        = $repository->getUserService();
        $contentTypeService = $repository->getContentTypeService();

        $this->markTestIncomplete( "@TODO: Test for ContentTypeService::deleteContentType() is not implemented." );
    }

    /**
     * Test for the copyContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::copyContentType()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCopyContentTypeThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService        = $repository->getUserService();
        $contentTypeService = $repository->getContentTypeService();

        $this->markTestIncomplete( "@TODO: Test for ContentTypeService::copyContentType() is not implemented." );
    }

    /**
     * Test for the copyContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::copyContentType($contentType, $user)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCopyContentTypeThrowsUnauthorizedExceptionWithSecondParameter()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService        = $repository->getUserService();
        $contentTypeService = $repository->getContentTypeService();

        $this->markTestIncomplete( "@TODO: Test for ContentTypeService::copyContentType() is not implemented." );
    }

    /**
     * Test for the assignContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::assignContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testAssignContentTypeGroupThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService        = $repository->getUserService();
        $contentTypeService = $repository->getContentTypeService();

        $this->markTestIncomplete( "@TODO: Test for ContentTypeService::assignContentTypeGroup() is not implemented." );
    }

    /**
     * Test for the unassignContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::unassignContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testUnassignContentTypeGroupThrowsUnauthorizedException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $userService        = $repository->getUserService();
        $contentTypeService = $repository->getContentTypeService();

        $this->markTestIncomplete( "@TODO: Test for ContentTypeService::unassignContentTypeGroup() is not implemented." );
    }
}
