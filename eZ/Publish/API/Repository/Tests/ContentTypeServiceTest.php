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
use eZ\Publish\API\Repository\Values\Content\Location;

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
        return $groupCreate;
    }

    /**
     * Test for the newContentTypeGroupCreateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::newContentTypeGroupCreateStruct()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testNewContentTypeGroupCreateStruct
     */
    public function testNewContentTypeGroupCreateStructValues( $createStruct )
    {
        $this->assertPropertiesCorrect(
            array(
                'identifier'       => 'new-group',
                'creatorId'        => null,
                'creationDate'     => null,
                'mainLanguageCode' => null,
            ),
            $createStruct
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
        $groupCreate->creatorId        = 23;
        $groupCreate->creationDate     = new \DateTime();
        $groupCreate->mainLanguageCode = 'de_DE';
        $groupCreate->setName( 'A name.' );
        $groupCreate->setDescription( 'A description.' );

        $group = $contentTypeService->createContentTypeGroup( $groupCreate );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup',
            $group
        );

        return array(
            'expected' => $groupCreate,
            'actual'   => $group,
        );
    }

    /**
     * Test for the createContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentTypeGroup()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentTypeGroup
     */
    public function testCreateContentTypeGroupStructValues( array $data )
    {
        $this->assertStructPropertiesCorrect(
            $data['expected'],
            $data['actual'],
            array( 'names', 'descriptions' )
        );
        $this->assertNotNull(
            $data['actual']->id
        );
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
        $contentTypeService = $repository->getContentTypeService();

        $groupCreate = $contentTypeService->newContentTypeGroupCreateStruct(
            'new-group'
        );
        $groupCreate->creatorId        = 23;
        $groupCreate->creationDate     = new \DateTime();
        $groupCreate->mainLanguageCode = 'de_DE';
        $groupCreate->setName( 'A name.' );
        $groupCreate->setDescription( 'A description.' );

        $storedGroup = $contentTypeService->createContentTypeGroup( $groupCreate );
        $groupId     = $storedGroup->id;

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $loadedGroup = $contentTypeService->loadContentTypeGroup(
            $groupId
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup',
            $loadedGroup
        );

        return array(
            'expected' => $storedGroup,
            'actual'   => $loadedGroup,
        );
    }

    /**
     * Test for the loadContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeGroup()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeGroup
     */
    public function testLoadContentTypeGroupStructValues( array $data )
    {
        $this->assertStructPropertiesCorrect(
            $data['expected'],
            $data['actual'],
            array( 'names', 'descriptions' )
        );
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
     * @dep_ends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     */
    public function testLoadContentTypeGroupByIdentifier()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $groupCreate = $contentTypeService->newContentTypeGroupCreateStruct(
            'new-group'
        );
        $storedGroup = $contentTypeService->createContentTypeGroup( $groupCreate );

        $loadedGroup = $contentTypeService->loadContentTypeGroupByIdentifier(
            $storedGroup->identifier
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup',
            $loadedGroup
        );

        return array(
            'expected' => $storedGroup,
            'actual'   => $loadedGroup,
        );
    }

    /**
     * Test for the loadContentTypeGroupByIdentifier() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeGroupByIdentifier()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeGroup
     */
    public function testLoadContentTypeGroupByIdentifierStructValues( array $data )
    {
        $this->assertStructPropertiesCorrect(
            $data['expected'],
            $data['actual'],
            array( 'names', 'descriptions' )
        );
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
        $repository = $this->getRepository();

        $contentTypeService = $repository->getContentTypeService();
        $loadedGroup = $contentTypeService->loadContentTypeGroupByIdentifier(
            'not-exists'
        );
    }

    /**
     * Test for the loadContentTypeGroups() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeGroups()
     * @dep_ends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentTypeGroup
     */
    public function testLoadContentTypeGroups()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $groupCreate = $contentTypeService->newContentTypeGroupCreateStruct(
            'new-group'
        );
        $storedGroup = $contentTypeService->createContentTypeGroup( $groupCreate );

        $secondGroupCreate = $contentTypeService->newContentTypeGroupCreateStruct(
            'second-group'
        );
        $secondStoredGroup = $contentTypeService->createContentTypeGroup( $secondGroupCreate );

        $loadedGroups = $contentTypeService->loadContentTypeGroups();
        /* END: Use Case */

        $this->assertInternalType(
            'array',
            $loadedGroups
        );

        return $loadedGroups;
    }

    /**
     * Test for the loadContentTypeGroups() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeGroups()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeGroups
     */
    public function testLoadContentTypeGroupsIdentifiers( $groups )
    {
        $this->assertEquals( 2, count( $groups ) );

        $expecteIdentifiers = array( 'new-group' => true, 'second-group' => true );
        $actualIdentifiers  = array( 'new-group' => false, 'second-group' => false );

        foreach ( $groups as $group )
        {
            $actualIdentifiers[$group->identifier] = true;
        }

        $this->assertEquals(
            $expecteIdentifiers,
            $actualIdentifiers,
            'Identifier missmatch in loeaded groups.'
        );
    }

    /**
     * Test for the newContentTypeGroupUpdateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::newContentTypeGroupUpdateStruct()
     * @dep_ends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     */
    public function testNewContentTypeGroupUpdateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $groupUpdate = $contentTypeService->newContentTypeGroupUpdateStruct();
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct',
            $groupUpdate
        );
    }

    /**
     * Test for the updateContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::updateContentTypeGroup()
     * @dep_ends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testNewContentTypeGroupUpdateStruct
     */
    public function testUpdateContentTypeGroup()
    {
        $this->createContentTypeGroup();
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $group = $contentTypeService->loadContentTypeGroupByIdentifier( 'new-group' );

        $groupUpdate = $contentTypeService->newContentTypeGroupUpdateStruct();

        $groupUpdate->identifier = 'updated-group';
        $groupUpdate->modifierId = 42;
        $groupUpdate->modificationDate = new \DateTime();
        $groupUpdate->mainLanguageCode = 'en_US';

        $groupUpdate->setName( 'A name', 'en_US' );
        $groupUpdate->setName( 'A name', 'en_GB' );
        $groupUpdate->setDescription( 'A description', 'en_US' );
        $groupUpdate->setDescription( 'A description', 'en_GB' );

        $contentTypeService->updateContentTypeGroup( $group, $groupUpdate );
        /* END: Use Case */

        $updatedGroup = $contentTypeService->loadContentTypeGroup( $group->id );

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct',
            $groupUpdate
        );

        return array(
            'originalGroup' => $group,
            'updateStruct'  => $groupUpdate,
            'updatedGroup'  => $updatedGroup,
        );
    }

    /**
     * Test for the updateContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::updateContentTypeGroup()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testUpdateContentTypeGroup
     */
    public function testUpdateContentTypeGroupStructValues( array $data )
    {
        $expectedValues = array(
            'identifier'       => $data['updateStruct']->identifier,
            'creationDate'     => $data['originalGroup']->creationDate,
            'modificationDate' => $data['updateStruct']->modificationDate,
            'creatorId'        => $data['originalGroup']->creatorId,
            'modifierId'       => $data['updateStruct']->modifierId,
            'mainLanguageCode' => $data['updateStruct']->mainLanguageCode,
            'names'            => $data['updateStruct']->names,
            'descriptions'     => $data['updateStruct']->descriptions,
        );

        $this->assertPropertiesCorrect(
            $expectedValues, $data['updatedGroup']
        );
    }

    /**
     * Creates a group with identifier "new-group"
     *
     * @return \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\ContentTypeGroupStub
     */
    protected function createContentTypeGroup()
    {
        $repository = $this->getRepository();

        $contentTypeService = $repository->getContentTypeService();

        $groupCreate = $contentTypeService->newContentTypeGroupCreateStruct(
            'new-group'
        );
        $groupCreate->creatorId        = 23;
        $groupCreate->creationDate     = new \DateTime();
        $groupCreate->mainLanguageCode = 'de_DE';
        $groupCreate->setName( 'Ein Name', 'de_DE' );
        $groupCreate->setDescription( 'Eine Beschreibung', 'de_DE' );

        return $contentTypeService->createContentTypeGroup( $groupCreate );
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
     * @dep_ends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testUpdateContentTypeGroup
     */
    public function testUpdateContentTypeGroupThrowsIllegalArgumentException()
    {
        $this->createContentTypeGroup();
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $groupCreate = $contentTypeService->newContentTypeGroupCreateStruct(
            'updated-group'
        );
        $groupToOverwrite = $contentTypeService->createContentTypeGroup( $groupCreate );

        $group = $contentTypeService->loadContentTypeGroupByIdentifier( 'new-group' );

        $groupUpdate = $contentTypeService->newContentTypeGroupUpdateStruct();
        $groupUpdate->identifier = 'updated-group';

        // Exception, because group with identifier "updated-group" exists
        $contentTypeService->updateContentTypeGroup( $group, $groupUpdate );
        /* END: Use Case */
    }

    /**
     * Test for the deleteContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::deleteContentTypeGroup()
     * @dep_ends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentTypeGroup
     */
    public function testDeleteContentTypeGroup()
    {
        $this->createContentTypeGroup();
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $group = $contentTypeService->loadContentTypeGroupByIdentifier( 'new-group' );

        $contentTypeService->deleteContentTypeGroup( $group );
        /* END: Use Case */

        try
        {
            $contentTypeService->loadContentTypeGroup( $group->id );
            $this->fail( 'Content type group not deleted.' );
        }
        catch ( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            // All fine
        }
    }

    /**
     * Test for the newContentTypeCreateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::newContentTypeCreateStruct()
     * @dep_ends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     */
    public function testNewContentTypeCreateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $typeCreate = $contentTypeService->newContentTypeCreateStruct(
            'new-type'
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct',
            $typeCreate
        );
        return $typeCreate;
    }

    /**
     * Test for the newContentTypeCreateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::newContentTypeCreateStruct()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testNewContentTypeCreateStruct
     */
    public function testNewContentTypeCreateStructValues( $createStruct )
    {
        $this->assertPropertiesCorrect(
            array(
                'identifier'             => 'new-type',
                'mainLanguageCode'       => null,
                'remoteId'               => null,
                'urlAliasSchema'         => null,
                'nameSchema'             => null,
                'isContainer'            => false,
                'defaultSortField'       => Location::SORT_FIELD_PUBLISHED,
                'defaultSortOrder'       => Location::SORT_ORDER_DESC,
                'defaultAlwaysAvailable' => true,
                'names'                  => new \ArrayObject(),
                'descriptions'           => new \ArrayObject(),
                'fieldDefinitions'       => new \ArrayObject(),
                'creatorId'              => null,
                'creationDate'           => null,
            ),
            $createStruct
        );
    }

    /**
     * Test for the newFieldDefinitionCreateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::newFieldDefinitionCreateStruct()
     * @dep_ends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     */
    public function testNewFieldDefinitionCreateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $fieldDefinitionCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'title', 'string'
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct',
            $fieldDefinitionCreate
        );
        return $fieldDefinitionCreate;
    }

    /**
     * Test for the newFieldDefinitionCreateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\FieldDefinitionService::newFieldDefinitionCreateStruct()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testNewFieldDefinitionCreateStruct
     */
    public function testNewFieldDefinitionCreateStructValues( $createStruct )
    {
        $this->assertPropertiesCorrect(
            array(
                'fieldTypeIdentifier' => 'string',
                'identifier'          => 'title',
                'names'               => new \ArrayObject(),
                'descriptions'        => new \ArrayObject(),
                'fieldGroup'          => null,
                'position'            => null,
                'isTranslatable'      => null,
                'isRequired'          => null,
                'isInfoCollector'     => null,
                'validator'           => null,
                'fieldSettings'       => null,
                'defaultValue'        => null,
                'isSearchable'        => null,
            ),
            $createStruct
        );
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
     * @see \eZ\Publish\API\Repository\ContentTypeService::deleteContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     */
    public function testDeleteContentTypeGroupThrowsIllegalArgumentException()
    {
        // TODO: Implement create content type
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
