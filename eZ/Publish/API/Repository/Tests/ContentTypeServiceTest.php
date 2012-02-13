<?php
/**
 * File containing the ContentTypeServiceTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use \eZ\Publish\API\Repository\Tests\BaseTest;

/**
 * Test case for operations in the ContentTypeService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\ContentTypeService
 */
class ContentTypeServiceTest extends BaseTest
{
    /**
     * Test for the newContentTypeGroupCreateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::newContentTypeGroupCreateStruct()
     * @dep_ends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     */
    public function testNewContentTypeGroupCreateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $groupCreate = $contentTypeService->newContentTypeGroupCreateStruct(
            'new-group'
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct',
            $groupCreate
        );
    }

    /**
     * Test for the createContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentTypeGroup()
     * @dep_ends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testNewContentTypeGroupCreateStruct
     */
    public function testCreateContentTypeGroup()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $groupCreate = $contentTypeService->newContentTypeGroupCreateStruct(
            'new-group'
        );

        $group = $contentTypeService->createContentTypeGroup( $groupCreate );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup',
            $group
        );
        // TODO: Further equality tests?
    }

    /**
     * Test for the createContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testCreateContentTypeGroupThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::createContentTypeGroup() is not implemented." );
    }

    /**
     * Test for the createContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     * @dep_ends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentTypeGroup
     */
    public function testCreateContentTypeGroupThrowsIllegalArgumentException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $groupCreate = $contentTypeService->newContentTypeGroupCreateStruct(
            'new-group'
        );
        $group = $contentTypeService->createContentTypeGroup( $groupCreate );

        $seciondGroupCreate = $contentTypeService->newContentTypeGroupCreateStruct(
            'new-group'
        );

        // Throws an Exception
        $group = $contentTypeService->createContentTypeGroup( $seciondGroupCreate );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeGroup()
     * @dep_ends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentTypeGroup
     */
    public function testLoadContentTypeGroup()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $groupCreate = $contentTypeService->newContentTypeGroupCreateStruct(
            'new-group'
        );
        $storedGroup = $contentTypeService->createContentTypeGroup( $groupCreate );

        $loadedGroup = $contentTypeService->loadContentTypeGroup(
            $storedGroup->id
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup',
            $loadedGroup
        );
        // TODO: Further equality tests?
    }

    /**
     * Test for the loadContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @dep_ends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     */
    public function testLoadContentTypeGroupThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        $contentTypeService = $repository->getContentTypeService();
        $loadedGroup = $contentTypeService->loadContentTypeGroup( 23 );
    }

    /**
     * Test for the loadContentTypeGroupByIdentifier() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeGroupByIdentifier()
     * 
     */
    public function testLoadContentTypeGroupByIdentifier()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::loadContentTypeGroupByIdentifier() is not implemented." );
    }

    /**
     * Test for the loadContentTypeGroupByIdentifier() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeGroupByIdentifier()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadContentTypeGroupByIdentifierThrowsNotFoundException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::loadContentTypeGroupByIdentifier() is not implemented." );
    }

    /**
     * Test for the loadContentTypeGroups() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeGroups()
     * 
     */
    public function testLoadContentTypeGroups()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::loadContentTypeGroups() is not implemented." );
    }

    /**
     * Test for the updateContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::updateContentTypeGroup()
     * 
     */
    public function testUpdateContentTypeGroup()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::updateContentTypeGroup() is not implemented." );
    }

    /**
     * Test for the updateContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::updateContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testUpdateContentTypeGroupThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::updateContentTypeGroup() is not implemented." );
    }

    /**
     * Test for the updateContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::updateContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     */
    public function testUpdateContentTypeGroupThrowsIllegalArgumentException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::updateContentTypeGroup() is not implemented." );
    }

    /**
     * Test for the deleteContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::deleteContentTypeGroup()
     * 
     */
    public function testDeleteContentTypeGroup()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::deleteContentTypeGroup() is not implemented." );
    }

    /**
     * Test for the deleteContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::deleteContentTypeGroup($contentTypeGroup, $deleteObjects)
     * 
     */
    public function testDeleteContentTypeGroupWithSecondParameter()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::deleteContentTypeGroup() is not implemented." );
    }

    /**
     * Test for the deleteContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::deleteContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testDeleteContentTypeGroupThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::deleteContentTypeGroup() is not implemented." );
    }

    /**
     * Test for the deleteContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::deleteContentTypeGroup($contentTypeGroup, $deleteObjects)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testDeleteContentTypeGroupThrowsUnauthorizedExceptionWithSecondParameter()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::deleteContentTypeGroup() is not implemented." );
    }

    /**
     * Test for the deleteContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::deleteContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     */
    public function testDeleteContentTypeGroupThrowsIllegalArgumentException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::deleteContentTypeGroup() is not implemented." );
    }

    /**
     * Test for the deleteContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::deleteContentTypeGroup($contentTypeGroup, $deleteObjects)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     */
    public function testDeleteContentTypeGroupThrowsIllegalArgumentExceptionWithSecondParameter()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::deleteContentTypeGroup() is not implemented." );
    }

    /**
     * Test for the createContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentType()
     * 
     */
    public function testCreateContentType()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::createContentType() is not implemented." );
    }

    /**
     * Test for the createContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentType()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     */
    public function testCreateContentTypeThrowsIllegalArgumentException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::createContentType() is not implemented." );
    }

    /**
     * Test for the loadContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentType()
     * 
     */
    public function testLoadContentType()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::loadContentType() is not implemented." );
    }

    /**
     * Test for the loadContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentType()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadContentTypeThrowsNotFoundException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::loadContentType() is not implemented." );
    }

    /**
     * Test for the loadContentTypeByIdentifier() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeByIdentifier()
     * 
     */
    public function testLoadContentTypeByIdentifier()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::loadContentTypeByIdentifier() is not implemented." );
    }

    /**
     * Test for the loadContentTypeByIdentifier() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeByIdentifier()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadContentTypeByIdentifierThrowsNotFoundException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::loadContentTypeByIdentifier() is not implemented." );
    }

    /**
     * Test for the loadContentTypeByRemoteId() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeByRemoteId()
     * 
     */
    public function testLoadContentTypeByRemoteId()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::loadContentTypeByRemoteId() is not implemented." );
    }

    /**
     * Test for the loadContentTypeByRemoteId() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeByRemoteId()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadContentTypeByRemoteIdThrowsNotFoundException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::loadContentTypeByRemoteId() is not implemented." );
    }

    /**
     * Test for the loadContentTypeDraft() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeDraft()
     * 
     */
    public function testLoadContentTypeDraft()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::loadContentTypeDraft() is not implemented." );
    }

    /**
     * Test for the loadContentTypeDraft() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeDraft()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadContentTypeDraftThrowsNotFoundException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::loadContentTypeDraft() is not implemented." );
    }

    /**
     * Test for the loadContentTypes() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypes()
     * 
     */
    public function testLoadContentTypes()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::loadContentTypes() is not implemented." );
    }

    /**
     * Test for the createContentTypeDraft() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentTypeDraft()
     * 
     */
    public function testCreateContentTypeDraft()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::createContentTypeDraft() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentTypeService::createContentTypeDraft() is not implemented." );
    }

    /**
     * Test for the createContentTypeDraft() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentTypeDraft()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testCreateContentTypeDraftThrowsBadStateException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::createContentTypeDraft() is not implemented." );
    }

    /**
     * Test for the updateContentTypeDraft() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::updateContentTypeDraft()
     * 
     */
    public function testUpdateContentTypeDraft()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::updateContentTypeDraft() is not implemented." );
    }

    /**
     * Test for the updateContentTypeDraft() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::updateContentTypeDraft()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testUpdateContentTypeDraftThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::updateContentTypeDraft() is not implemented." );
    }

    /**
     * Test for the updateContentTypeDraft() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::updateContentTypeDraft()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     */
    public function testUpdateContentTypeDraftThrowsIllegalArgumentException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::updateContentTypeDraft() is not implemented." );
    }

    /**
     * Test for the deleteContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::deleteContentType()
     * 
     */
    public function testDeleteContentType()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::deleteContentType() is not implemented." );
    }

    /**
     * Test for the deleteContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::deleteContentType($contentType, $deleteObjects)
     * 
     */
    public function testDeleteContentTypeWithSecondParameter()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::deleteContentType() is not implemented." );
    }

    /**
     * Test for the deleteContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::deleteContentType()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testDeleteContentTypeThrowsBadStateException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::deleteContentType() is not implemented." );
    }

    /**
     * Test for the deleteContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::deleteContentType($contentType, $deleteObjects)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testDeleteContentTypeThrowsBadStateExceptionWithSecondParameter()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::deleteContentType() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentTypeService::deleteContentType() is not implemented." );
    }

    /**
     * Test for the deleteContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::deleteContentType($contentType, $deleteObjects)
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testDeleteContentTypeThrowsUnauthorizedExceptionWithSecondParameter()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::deleteContentType() is not implemented." );
    }

    /**
     * Test for the copyContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::copyContentType()
     * 
     */
    public function testCopyContentType()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::copyContentType() is not implemented." );
    }

    /**
     * Test for the copyContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::copyContentType($contentType, $user)
     * 
     */
    public function testCopyContentTypeWithSecondParameter()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::copyContentType() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentTypeService::copyContentType() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentTypeService::copyContentType() is not implemented." );
    }

    /**
     * Test for the assignContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::assignContentTypeGroup()
     * 
     */
    public function testAssignContentTypeGroup()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::assignContentTypeGroup() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentTypeService::assignContentTypeGroup() is not implemented." );
    }

    /**
     * Test for the assignContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::assignContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     */
    public function testAssignContentTypeGroupThrowsIllegalArgumentException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::assignContentTypeGroup() is not implemented." );
    }

    /**
     * Test for the unassignContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::unassignContentTypeGroup()
     * 
     */
    public function testUnassignContentTypeGroup()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::unassignContentTypeGroup() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentTypeService::unassignContentTypeGroup() is not implemented." );
    }

    /**
     * Test for the unassignContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::unassignContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     */
    public function testUnassignContentTypeGroupThrowsIllegalArgumentException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::unassignContentTypeGroup() is not implemented." );
    }

    /**
     * Test for the unassignContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::unassignContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testUnassignContentTypeGroupThrowsBadStateException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::unassignContentTypeGroup() is not implemented." );
    }

    /**
     * Test for the addFieldDefinition() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::addFieldDefinition()
     * 
     */
    public function testAddFieldDefinition()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::addFieldDefinition() is not implemented." );
    }

    /**
     * Test for the addFieldDefinition() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::addFieldDefinition()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     */
    public function testAddFieldDefinitionThrowsIllegalArgumentException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::addFieldDefinition() is not implemented." );
    }

    /**
     * Test for the addFieldDefinition() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::addFieldDefinition()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testAddFieldDefinitionThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::addFieldDefinition() is not implemented." );
    }

    /**
     * Test for the removeFieldDefinition() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::removeFieldDefinition()
     * 
     */
    public function testRemoveFieldDefinition()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::removeFieldDefinition() is not implemented." );
    }

    /**
     * Test for the removeFieldDefinition() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::removeFieldDefinition()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     */
    public function testRemoveFieldDefinitionThrowsIllegalArgumentException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::removeFieldDefinition() is not implemented." );
    }

    /**
     * Test for the removeFieldDefinition() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::removeFieldDefinition()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testRemoveFieldDefinitionThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::removeFieldDefinition() is not implemented." );
    }

    /**
     * Test for the updateFieldDefinition() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::updateFieldDefinition()
     * 
     */
    public function testUpdateFieldDefinition()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::updateFieldDefinition() is not implemented." );
    }

    /**
     * Test for the updateFieldDefinition() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::updateFieldDefinition()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testUpdateFieldDefinitionThrowsInvalidArgumentException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::updateFieldDefinition() is not implemented." );
    }

    /**
     * Test for the updateFieldDefinition() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::updateFieldDefinition()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function testUpdateFieldDefinitionThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::updateFieldDefinition() is not implemented." );
    }

    /**
     * Test for the updateFieldDefinition() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::updateFieldDefinition()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     */
    public function testUpdateFieldDefinitionThrowsIllegalArgumentException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::updateFieldDefinition() is not implemented." );
    }

    /**
     * Test for the publishContentTypeDraft() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::publishContentTypeDraft()
     * 
     */
    public function testPublishContentTypeDraft()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::publishContentTypeDraft() is not implemented." );
    }

    /**
     * Test for the publishContentTypeDraft() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::publishContentTypeDraft()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testPublishContentTypeDraftThrowsBadStateException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::publishContentTypeDraft() is not implemented." );
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
        $this->markTestIncomplete( "Test for ContentTypeService::publishContentTypeDraft() is not implemented." );
    }

    /**
     * Test for the newContentTypeCreateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::newContentTypeCreateStruct()
     * 
     */
    public function testNewContentTypeCreateStruct()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::newContentTypeCreateStruct() is not implemented." );
    }

    /**
     * Test for the newContentTypeUpdateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::newContentTypeUpdateStruct()
     * 
     */
    public function testNewContentTypeUpdateStruct()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::newContentTypeUpdateStruct() is not implemented." );
    }

    /**
     * Test for the newContentTypeGroupUpdateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::newContentTypeGroupUpdateStruct()
     * 
     */
    public function testNewContentTypeGroupUpdateStruct()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::newContentTypeGroupUpdateStruct() is not implemented." );
    }

    /**
     * Test for the newFieldDefinitionCreateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::newFieldDefinitionCreateStruct()
     * 
     */
    public function testNewFieldDefinitionCreateStruct()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::newFieldDefinitionCreateStruct() is not implemented." );
    }

    /**
     * Test for the newFieldDefinitionUpdateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::newFieldDefinitionUpdateStruct()
     * 
     */
    public function testNewFieldDefinitionUpdateStruct()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::newFieldDefinitionUpdateStruct() is not implemented." );
    }

}
