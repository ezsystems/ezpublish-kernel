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

use \eZ\Publish\API\Repository\Tests\Stubs\Values\ContentType\StringLengthValidatorStub;

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
        $groupCreate->names            = array( 'en_US' => 'A name.' );
        $groupCreate->descriptions     = array( 'en_US' => 'A description.' );

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
        $groupCreate->names            = array( 'en_US' => 'A name.' );
        $groupCreate->descriptions     = array( 'en_US' => 'A description.' );

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

        $groupUpdate->names = array(
            'en_US' => 'A name',
            'en_GB' => 'A name',
        );
        $groupUpdate->descriptions = array(
            'en_US' => 'A description',
            'en_GB' => 'A description',
        );

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
        $groupCreate->names            = array( 'de_DE' => 'Ein Name' );
        $groupCreate->descriptions     = array( 'de_DE' => 'Eine Beschreibung' );

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
                'names'                  => null,
                'descriptions'           => null,
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
                'names'               => null,
                'descriptions'        => null,
                'fieldGroup'          => null,
                'position'            => null,
                'isTranslatable'      => null,
                'isRequired'          => null,
                'isInfoCollector'     => null,
                'validators'          => null,
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
        $groups = $this->createGroups();

        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        // $groups is an array of ContentTypeGroup objects

        $contentTypeService = $repository->getContentTypeService();

        $typeCreate = $contentTypeService->newContentTypeCreateStruct( 'blog-post' );
        $typeCreate->mainLanguageCode = 'en_US';
        $typeCreate->remoteId = '384b94a1bd6bc06826410e284dd9684887bf56fc';
        $typeCreate->urlAliasSchema = 'url|scheme';
        $typeCreate->nameSchema = 'name|scheme';
        $typeCreate->names = array(
            'en_US' => 'Blog post',
            'de_DE' => 'Blog-Eintrag',
        );
        $typeCreate->descriptions = array(
            'en_US' => 'A blog post',
            'de_DE' => 'Ein Blog-Eintrag',
        );
        $typeCreate->creatorId = 23;
        $typeCreate->creationDate = new \DateTime();

        $titleFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'title', 'string'
        );
        $titleFieldCreate->names = array(
            'en_US' => 'Title',
            'de_DE' => 'Titel',
        );
        $titleFieldCreate->descriptions = array(
            'en_US' => 'Title of the blog post',
            'de_DE' => 'Titel des Blog-Eintrages',
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
            'en_US' => 'Body',
            'de_DE' => 'Textkörper',
        );
        $bodyFieldCreate->descriptions = array(
            'en_US' => 'Body of the blog post',
            'de_DE' => 'Textkörper des Blog-Eintrages',
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

        $contentType = $contentTypeService->createContentType(
            $typeCreate,
            $groups
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentType',
            $contentType
        );

        return array(
            'typeCreate'  => $typeCreate,
            'contentType' => $contentType,
            'groups'      => $groups,
        );
    }

    /**
     * Test for the createContentType() method struct values.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentType()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentType
     */
    public function testCreateContentTypeStructValues( array $data )
    {
        $typeCreate  = $data['typeCreate'];
        $contentType = $data['contentType'];
        $groups      = $data['groups'];

        foreach ( $typeCreate as $propertyName => $propertyValue )
        {
            switch ( $propertyName )
            {
                case 'fieldDefinitions':
                    $this->assertFieldDefinitionsCorrect(
                        $typeCreate->fieldDefinitions,
                        $contentType->fieldDefinitions
                    );
                    break;

                case 'contentTypeGroups':
                    $this->assertContentTypeGroupsCorrect(
                        $groups,
                        $contentType->contentTypeGroups
                    );
                    break;
                default:
                    $this->assertEquals(
                        $typeCreate->$propertyName,
                        $contentType->$propertyName
                    );
                    break;
            }
        }
    }

    /**
     * Asserts field definition creation
     *
     * Asserts that all field definitions defined through created structs in
     * $expectedDefinitionCreates have been correctly created in
     * $actualDefinitions.
     *
     * @param \eZ\Publish\API\Repository\Values\FieldDefinitionCreateStruct[] $expectedDefinitionCreates
     * @param \eZ\Publish\API\Repository\Values\FieldDefinition[] $actualDefinitions
     * @return void
     */
    protected function assertFieldDefinitionsCorrect( array $expectedDefinitionCreates, array $actualDefinitions )
    {
        $this->assertEquals(
            count( $expectedDefinitionCreates ),
            count( $actualDefinitions ),
            'Count of field definition creates did not match count of field definitions.'
        );

        $sorter = function( $a, $b )
        {
            return strcmp( $a->identifier, $b->identifier );
        };

        usort( $expectedDefinitionCreates, $sorter );
        usort( $actualDefinitions, $sorter );

        foreach ( $expectedDefinitionCreates as $key => $expectedCreate )
        {
            $this->assertFieldDefinitionsEqual(
                $expectedCreate,
                $actualDefinitions[$key]
            );
        }
    }

    /**
     * Asserts that a field definition has been correctly created.
     *
     * Asserts that the given $actualDefinition is correctly created from the
     * create struct in $expectedCreate.
     *
     * @param \eZ\Publish\API\Repository\Values\FieldDefinitionCreateStruct $expectedDefinitionCreate
     * @param \eZ\Publish\API\Repository\Values\FieldDefinition $actualDefinition
     * @return void
     */
    protected function assertFieldDefinitionsEqual( $expectedCreate, $actualDefinition )
    {
        foreach ( $expectedCreate as $propertyName => $propertyValue )
        {
            $this->assertEquals(
                $expectedCreate->$propertyName,
                $actualDefinition->$propertyName
            );
        }
    }

    /**
     * Asserts that two sets of ContentTypeGroups are equal.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup[] $expectedGroups
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup[] $actualGroups
     * @return void
     */
    protected function assertContentTypeGroupsCorrect( $expectedGroups, $actualGroups )
    {
        $sorter = function ( $a, $b )
        {
            if ( $a->id == $b->id )
            {
                return 0;
            }
            return ( $a->id < $b->id ) ? -1 : 1;
        };

        usort( $expectedGroups, $sorter );
        usort( $actualGroups, $sorter );

        foreach ( $expectedGroups as $key => $expectedGroup )
        {
            $this->assertPropertiesCorrect(
                $expectedGroup,
                $actualGroups[$key],
                $this->groupProperties
            );
        }
    }

    /**
     * Creates a number of ContentTypeGroup objects and returns them
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup[]
     */
    protected function createGroups()
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        $groups = array();

        $groupCreate = $contentTypeService->newContentTypeGroupCreateStruct(
            'first-group'
        );
        $groups[] = $contentTypeService->createContentTypeGroup( $groupCreate );

        $groupCreate->identifier = 'second-group';
        $groups[] = $contentTypeService->createContentTypeGroup( $groupCreate );

        return $groups;
    }

    /**
     * Test for the createContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentType()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentType
     */
    public function testCreateContentTypeThrowsIllegalArgumentExceptionDuplicateIdentifier()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $typeCreate = $contentTypeService->newContentTypeCreateStruct( 'blog-post' );

        $firstType = $contentTypeService->createContentType( $typeCreate, array() );

        $typeCreate = $contentTypeService->newContentTypeCreateStruct( 'blog-post' );

        // Throws exception
        $secondType = $contentTypeService->createContentType( $typeCreate, array() );
        /* END: Use Case */
    }

    /**
     * Test for the createContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentType()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentType
     */
    public function testCreateContentTypeThrowsIllegalArgumentExceptionDuplicateRemoteId()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $typeCreate = $contentTypeService->newContentTypeCreateStruct( 'blog-post' );
        $typeCreate->remoteId = 'duplicate-id';

        $firstType = $contentTypeService->createContentType( $typeCreate, array() );

        $typeCreate = $contentTypeService->newContentTypeCreateStruct( 'news-article' );
        $typeCreate->remoteId = 'duplicate-id';

        // Throws exception
        $secondType = $contentTypeService->createContentType( $typeCreate, array() );
        /* END: Use Case */
    }

    /**
     * Test for the createContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentType()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentType
     */
    public function testCreateContentTypeThrowsIllegalArgumentExceptionDuplicateFieldIdentifier()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $typeCreate = $contentTypeService->newContentTypeCreateStruct( 'blog-post' );

        $firstFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'title', 'string'
        );
        $typeCreate->addFieldDefinition( $firstFieldCreate );

        $secondFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'title', 'text'
        );
        $typeCreate->addFieldDefinition( $secondFieldCreate );

        // Throws exception
        $secondType = $contentTypeService->createContentType( $typeCreate, array() );
        /* END: Use Case */
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
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $typeUpdate = $contentTypeService->newContentTypeUpdateStruct();
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentTypeUpdateStruct',
            $typeUpdate
        );
        return $typeUpdate;
    }

    /**
     * Test for the newContentTypeUpdateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::newContentTypeUpdateStruct()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testNewContentTypeUpdateStruct
     */
    public function testNewContentTypeUpdateStructValues( $typeUpdate )
    {
        foreach ( $typeUpdate as $propertyName => $propertyValue )
        {
            $this->assertNull(
                $propertyValue,
                "Property '$propertyName' is not null."
            );
        }
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
        $createdDraft = $this->createContentTypeDraft();
        $draftId = $createdDraft->id;

        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        // $draftId contains the ID of the draft to load
        $contentTypeService = $repository->getContentTypeService();

        $contentTypeDraft = $contentTypeService->loadContentTypeDraft( $draftId );
        /* END: Use Case */

        $this->assertEquals(
            $createdDraft,
            $contentTypeDraft
        );
    }

    /**
     * Creates a fully functional ContentTypeDraft and returns it.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     */
    protected function createContentTypeDraft()
    {
        // Actually equals @see testCreateContentType()
        $repository = $this->getRepository();

        $groups = $this->createGroups();

        $contentTypeService = $repository->getContentTypeService();

        $typeCreate = $contentTypeService->newContentTypeCreateStruct( 'blog-post' );
        $typeCreate->mainLanguageCode = 'en_US';
        $typeCreate->remoteId = '384b94a1bd6bc06826410e284dd9684887bf56fc';
        $typeCreate->urlAliasSchema = 'url|scheme';
        $typeCreate->nameSchema = 'name|scheme';
        $typeCreate->names = array(
            'en_US' => 'Blog post',
            'de_DE' => 'Blog-Eintrag',
        );
        $typeCreate->descriptions = array(
            'en_US' => 'A blog post',
            'de_DE' => 'Ein Blog-Eintrag',
        );
        $typeCreate->creatorId = 23;
        $typeCreate->creationDate = new \DateTime();

        $titleFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'title', 'string'
        );
        $titleFieldCreate->names = array(
            'en_US' => 'Title',
            'de_DE' => 'Titel',
        );
        $titleFieldCreate->descriptions = array(
            'en_US' => 'Title of the blog post',
            'de_DE' => 'Titel des Blog-Eintrages',
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
            'en_US' => 'Body',
            'de_DE' => 'Textkörper',
        );
        $bodyFieldCreate->descriptions = array(
            'en_US' => 'Body of the blog post',
            'de_DE' => 'Textkörper des Blog-Eintrages',
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

        return $contentTypeService->createContentType(
            $typeCreate,
            $groups
        );
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
        $contentTypeDraft = $this->createContentTypeDraft();

        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        // $contentTypeDraft contains a ContentTypeDraft

        $contentTypeService = $repository->getContentTypeService();

        $typeUpdate = $contentTypeService->newContentTypeUpdateStruct();
        $typeUpdate->identifier = 'news-article';
        $typeUpdate->remoteId = '4cf35f5166fd31bf0cda859dc837e095daee9833';
        $typeUpdate->urlAliasSchema = 'url@alias|scheme';
        $typeUpdate->nameSchema = '@name@scheme@';
        $typeUpdate->isContainer = true;
        $typeUpdate->mainLanguageCode = 'de_DE';
        $typeUpdate->defaultAlwaysAvailable = false;
        $typeUpdate->modifierId = 42;
        $typeUpdate->modificationDate = new \DateTime();
        $typeUpdate->names = array(
            'en_US' => 'News article',
            'de_DE' => 'Nachrichten-Artikel',
        );
        $typeUpdate->descriptions = array(
            'en_US' => 'A news article',
            'de_DE' => 'Ein Nachrichten-Artikel',
        );

        $contentTypeService->updateContentTypeDraft( $contentTypeDraft, $typeUpdate );
        /* END: Use Case */

        $updatedType = $contentTypeService->loadContentTypeDraft(
            $contentTypeDraft->id
        );

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentTypeDraft',
            $updatedType
        );

        return array(
            'originalType' => $contentTypeDraft,
            'updateStruct' => $typeUpdate,
            'updatedType'  => $updatedType,
        );
    }

    /**
     * Test for the updateContentTypeDraft() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::updateContentTypeDraft()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testUpdateContentTypeDraft
     */
    public function testUpdateContentTypeDraftStructValues( $data )
    {
        $originalType = $data['originalType'];
        $updateStruct = $data['updateStruct'];
        $updatedType  = $data['updatedType'];

        $expectedValues = array(
            'id'                => $originalType->id,
            'names'             => $updateStruct->names,
            'descriptions'      => $updateStruct->descriptions,
            'identifier'        => $updateStruct->identifier,
            'creationDate'      => $originalType->creationDate,
            'modificationDate'  => $updateStruct->modificationDate,
            'creatorId'         => $originalType->creatorId,
            'modifierId'        => $updateStruct->modifierId,
            'urlAliasSchema'    => $updateStruct->urlAliasSchema,
            'nameSchema'        => $updateStruct->nameSchema,
            'isContainer'       => $updateStruct->isContainer,
            'mainLanguageCode'  => $updateStruct->mainLanguageCode,
            'contentTypeGroups' => $originalType->contentTypeGroups,
            'fieldDefinitions'  => $originalType->fieldDefinitions,
        );

        $this->assertPropertiesCorrect(
            $expectedValues,
            $updatedType
        );
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
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testUpdateContentTypeDraft
     */
    public function testUpdateContentTypeDraftThrowsIllegalArgumentExceptionDuplicateIdentifier()
    {
        $contentTypeDraft = $this->createContentTypeDraft();

        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        // $contentTypeDraft contains a ContentTypeDraft with identifier 'blog-post'

        $contentTypeService = $repository->getContentTypeService();

        $typeCreate = $contentTypeService->newContentTypeCreateStruct(
            'news-article'
        );
        $contentTypeService->createContentType( $typeCreate, array() );

        $typeUpdate = $contentTypeService->newContentTypeUpdateStruct();
        $typeUpdate->identifier = 'news-article';

        // Throws exception
        $contentTypeService->updateContentTypeDraft( $contentTypeDraft, $typeUpdate );
        /* END: Use Case */
    }

    /**
     * Test for the updateContentTypeDraft() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::updateContentTypeDraft()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testUpdateContentTypeDraft
     */
    public function testUpdateContentTypeDraftThrowsIllegalArgumentExceptionDuplicateRemoteId()
    {
        $contentTypeDraft = $this->createContentTypeDraft();

        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        // $contentTypeDraft contains a ContentTypeDraft with identifier 'blog-post'

        $contentTypeService = $repository->getContentTypeService();

        $typeCreate = $contentTypeService->newContentTypeCreateStruct(
            'some-content-type'
        );
        $typeCreate->remoteId = 'will-be-duplicated';
        $contentTypeService->createContentType( $typeCreate, array() );

        $typeUpdate = $contentTypeService->newContentTypeUpdateStruct();
        $typeUpdate->remoteId = 'will-be-duplicated';

        // Throws exception
        $contentTypeService->updateContentTypeDraft( $contentTypeDraft, $typeUpdate );
        /* END: Use Case */
    }

    /**
     * Test for the updateContentTypeDraft() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::updateContentTypeDraft()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testUpdateContentTypeDraft
     */
    public function testUpdateContentTypeDraftThrowsIllegalArgumentExceptionIncorrectUser()
    {
        $this->markTestIncomplete( "Is not implemented." );
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
        $contentTypeDraft = $this->createContentTypeDraft();

        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        // $contentTypeDraft contains a ContentTypeDraft

        $contentTypeService = $repository->getContentTypeService();

        $fieldDefCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'tags', 'string'
        );
        $fieldDefCreate->names = array(
            'en_US' => 'Tags',
            'de_DE' => 'Schlagworte',
        );
        $fieldDefCreate->descriptions = array(
            'en_US' => 'Tags of the blog post',
            'de_DE' => 'Schlagworte des Blog-Eintrages',
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

        $contentTypeService->addFieldDefinition( $contentTypeDraft, $fieldDefCreate );
        /* END: Use Case */

        $loadedType = $contentTypeService->loadContentTypeDraft( $contentTypeDraft->id );

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentTypeDraft',
            $loadedType
        );
        return array(
            'loadedType'     => $loadedType,
            'fieldDefCreate' => $fieldDefCreate,
        );
    }

    /**
     * Test for the addFieldDefinition() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::addFieldDefinition()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testAddFieldDefinition
     */
    public function testAddFieldDefinitionStructValues( array $data )
    {
        $loadedType     = $data['loadedType'];
        $fieldDefCreate = $data['fieldDefCreate'];

        foreach ( $loadedType->fieldDefinitions as $fieldDefinition )
        {
            if ( $fieldDefinition->identifier == $fieldDefCreate->identifier )
            {
                $this->assertFieldDefinitionsEqual( $fieldDefCreate, $fieldDefinition );
                return;
            }
        }

        $this->fail(
            sprintf(
                'Field definition with identifier "%s" not create.',
                $fieldDefCreate->identifier
            )
        );
    }

    /**
     * Test for the addFieldDefinition() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::addFieldDefinition()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     */
    public function testAddFieldDefinitionThrowsIllegalArgumentExceptionDuplicateFieldIdentifier()
    {
        $contentTypeDraft = $this->createContentTypeDraft();

        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        // $contentTypeDraft contains a ContentTypeDraft
        // $contentTypeDraft has a field "title"

        $contentTypeService = $repository->getContentTypeService();

        $fieldDefCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'title', 'string'
        );

        // Throws an exception
        $contentTypeService->addFieldDefinition( $contentTypeDraft, $fieldDefCreate );
        /* END: Use Case */
    }

    /**
     * Test for the addFieldDefinition() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::addFieldDefinition()
     * @depens eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testAddFieldDefinition
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
     * @depens eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentType
     */
    public function testRemoveFieldDefinition()
    {
        $contentTypeDraft = $this->createContentTypeDraft();
        $draftId = $contentTypeDraft->id;

        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        // $draftId contains the ID of a content type draft
        $contentTypeService = $repository->getContentTypeService();

        $contentTypeDraft = $contentTypeService->loadContentTypeDraft( $draftId );

        $bodyField = $contentTypeDraft->getFieldDefinition( 'body' );

        $contentTypeService->removeFieldDefinition( $contentTypeDraft, $bodyField );
        /* END: Use Case */

        $loadedType = $contentTypeService->loadContentTypeDraft( $contentTypeDraft->id );

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentTypeDraft',
            $loadedType
        );

        return array(
            'removedFieldDefinition' => $bodyField,
            'loadedType'             => $loadedType,
        );
    }

    /**
     * Test for the removeFieldDefinition() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::removeFieldDefinition()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testRemoveFieldDefinition
     */
    public function testRemoveFieldDefinitionRemoved( array $data )
    {
        $removedFieldDefinition = $data['removedFieldDefinition'];
        $loadedType = $data['loadedType'];

        foreach ( $loadedType->fieldDefinitions as $fieldDefinition )
        {
            if ( $fieldDefinition->identifier == $removedFieldDefinition->identifier )
            {
                $this->fail(
                    sprintf(
                        'Field definition with identifier "%s" not removed.',
                        $removedFieldDefinition->identifier
                    )
                );
            }
        }
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
