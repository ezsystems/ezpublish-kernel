<?php
/**
 * File containing the ContentTypeServiceTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;
use eZ\Publish\API\Repository\Exceptions;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;

/**
 * Test case for operations in the ContentTypeService using in memory storage.
 *
 * @see eZ\Publish\API\Repository\ContentTypeService
 * @group integration
 * @group content-type
 */
class ContentTypeServiceTest extends BaseContentTypeServiceTest
{
    /**
     * Test for the newContentTypeGroupCreateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::newContentTypeGroupCreateStruct()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     * @group user
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
            '\\eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentTypeGroupCreateStruct',
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
                'identifier' => 'new-group',
                'creatorId' => null,
                'creationDate' => null,
                /* @todo uncomment when support for multilingual names and descriptions is added
                'mainLanguageCode' => null,
                */
            ),
            $createStruct
        );
    }

    /**
     * Test for the createContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentTypeGroup()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testNewContentTypeGroupCreateStruct
     * @group user
     */
    public function testCreateContentTypeGroup()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $groupCreate = $contentTypeService->newContentTypeGroupCreateStruct(
            'new-group'
        );
        $groupCreate->creatorId = $this->generateId( "user", $repository->getCurrentUser()->id );
        $groupCreate->creationDate = $this->createDateTime();
        /* @todo uncomment when support for multilingual names and descriptions is added
        $groupCreate->mainLanguageCode = 'ger-DE';
        $groupCreate->names = array( 'eng-GB' => 'A name.' );
        $groupCreate->descriptions = array( 'eng-GB' => 'A description.' );
        */

        $group = $contentTypeService->createContentTypeGroup( $groupCreate );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentTypeGroup',
            $group
        );

        return array(
            'createStruct' => $groupCreate,
            'group' => $group,
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
        $createStruct = $data['createStruct'];
        $group = $data['group'];

        $this->assertEquals(
            array(
                'identifier' => $group->identifier,
                'creatorId' => $group->creatorId,
                'creationDate' => $group->creationDate,
            ),
            array(
                'identifier' => $createStruct->identifier,
                'creatorId' => $createStruct->creatorId,
                'creationDate' => $createStruct->creationDate,
            )
        );
        $this->assertNotNull(
            $group->id
        );
        return $data;
    }

    /**
     * Test for the createContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentTypeGroup()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentTypeGroupStructValues
     */
    public function testCreateContentTypeGroupStructLanguageDependentValues( array $data )
    {
        $createStruct = $data['createStruct'];
        $group = $data['group'];

        $this->assertStructPropertiesCorrect(
            $createStruct,
            $group
            /* @todo uncomment when support for multilingual names and descriptions is added
            array( 'names', 'descriptions', 'mainLanguageCode' )
            */
        );
    }

    /**
     * Test for the createContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentTypeGroup
     */
    public function testCreateContentTypeGroupThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $groupCreate = $contentTypeService->newContentTypeGroupCreateStruct(
            'Content'
        );

        // Throws an Exception, since group "Content" already exists
        $contentTypeService->createContentTypeGroup( $groupCreate );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeGroup()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentTypeGroup
     * @group user
     */
    public function testLoadContentTypeGroup()
    {
        $repository = $this->getRepository();

        $contentTypeGroupId = $this->generateId( 'typegroup', 2 );
        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        // Loads the "Users" group
        // $contentTypeGroupId is the ID of an existing content type group
        $loadedGroup = $contentTypeService->loadContentTypeGroup( $contentTypeGroupId );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentTypeGroup',
            $loadedGroup
        );

        return $loadedGroup;
    }

    /**
     * Test for the loadContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeGroup()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeGroup
     */
    public function testLoadContentTypeGroupStructValues( ContentTypeGroup $group )
    {
        $this->assertPropertiesCorrect(
            array(
                'id' => $this->generateId( 'typegroup', 2 ),
                'identifier' => 'Users',
                'creationDate' => $this->createDateTime( 1031216941 ),
                'modificationDate' => $this->createDateTime( 1033922113 ),
                'creatorId' => $this->generateId( 'user', 14 ),
                'modifierId' => $this->generateId( 'user', 14 ),
            ),
            $group
        );
    }

    /**
     * Test for the loadContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     */
    public function testLoadContentTypeGroupThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        $contentTypeService = $repository->getContentTypeService();
        $loadedGroup = $contentTypeService->loadContentTypeGroup( $this->generateId( 'typegroup', 2342 ) );
    }

    /**
     * Test for the loadContentTypeGroupByIdentifier() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeGroupByIdentifier()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     * @group user
     * @group field-type
     */
    public function testLoadContentTypeGroupByIdentifier()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $loadedGroup = $contentTypeService->loadContentTypeGroupByIdentifier(
            "Media"
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentTypeGroup',
            $loadedGroup
        );

        return $loadedGroup;
    }

    /**
     * Test for the loadContentTypeGroupByIdentifier() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeGroupByIdentifier()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeGroupByIdentifier
     */
    public function testLoadContentTypeGroupByIdentifierStructValues( ContentTypeGroup $group )
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        $this->assertEquals(
            $contentTypeService->loadContentTypeGroup( $this->generateId( 'typegroup', 3 ) ),
            $group
        );
    }

    /**
     * Test for the loadContentTypeGroupByIdentifier() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeGroupByIdentifier()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeGroupByIdentifier
     */
    public function testLoadContentTypeGroupByIdentifierThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        // Throws exception
        $loadedGroup = $contentTypeService->loadContentTypeGroupByIdentifier(
            'not-exists'
        );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentTypeGroups() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeGroups()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentTypeGroup
     */
    public function testLoadContentTypeGroups()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        // Loads an array with all content type groups
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
        $this->assertEquals( 4, count( $groups ) );

        $expectedIdentifiers = array(
            'Content' => true,
            'Users' => true,
            'Media' => true,
            'Setup' => true,
        );

        $actualIdentifiers = array();
        foreach ( $groups as $group )
        {
            $actualIdentifiers[$group->identifier] = true;
        }

        ksort( $expectedIdentifiers );
        ksort( $actualIdentifiers );

        $this->assertEquals(
            $expectedIdentifiers,
            $actualIdentifiers,
            'Identifier missmatch in loaded groups.'
        );
    }

    /**
     * Test for the newContentTypeGroupUpdateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::newContentTypeGroupUpdateStruct()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     */
    public function testNewContentTypeGroupUpdateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $groupUpdate = $contentTypeService->newContentTypeGroupUpdateStruct();
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentTypeGroupUpdateStruct',
            $groupUpdate
        );
    }

    /**
     * Test for the updateContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::updateContentTypeGroup()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentTypeGroup
     */
    public function testUpdateContentTypeGroup()
    {
        $repository = $this->getRepository();

        $modifierId = $this->generateId( 'user', 42 );
        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $group = $contentTypeService->loadContentTypeGroupByIdentifier( 'Setup' );

        $groupUpdate = $contentTypeService->newContentTypeGroupUpdateStruct();

        $groupUpdate->identifier = 'Teardown';
        $groupUpdate->modifierId = $modifierId;
        $groupUpdate->modificationDate = $this->createDateTime();
        /* @todo uncomment when support for multilingual names and descriptions is added
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

        $contentTypeService->updateContentTypeGroup( $group, $groupUpdate );
        /* END: Use Case */

        $updatedGroup = $contentTypeService->loadContentTypeGroup( $group->id );

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentTypeGroupUpdateStruct',
            $groupUpdate
        );

        return array(
            'originalGroup' => $group,
            'updateStruct' => $groupUpdate,
            'updatedGroup' => $updatedGroup,
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
            'identifier' => $data['updateStruct']->identifier,
            'creationDate' => $data['originalGroup']->creationDate,
            'modificationDate' => $data['updateStruct']->modificationDate,
            'creatorId' => $data['originalGroup']->creatorId,
            'modifierId' => $data['updateStruct']->modifierId,
        );

        $this->assertPropertiesCorrect(
            $expectedValues, $data['updatedGroup']
        );

        return $data;
    }

    /**
     * Test for the updateContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::updateContentTypeGroup()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testUpdateContentTypeGroupStructValues
     */
    public function testUpdateContentTypeGroupStructLanguageDependentValues( array $data )
    {
        $expectedValues = array(
            'identifier' => $data['updateStruct']->identifier,
            'creationDate' => $data['originalGroup']->creationDate,
            'modificationDate' => $data['updateStruct']->modificationDate,
            'creatorId' => $data['originalGroup']->creatorId,
            'modifierId' => $data['updateStruct']->modifierId,
            /* @todo uncomment when support for multilingual names and descriptions is added
            'mainLanguageCode' => $data['updateStruct']->mainLanguageCode,
            'names' => $data['updateStruct']->names,
            'descriptions' => $data['updateStruct']->descriptions,
            */
        );

        $this->assertPropertiesCorrect(
            $expectedValues, $data['updatedGroup']
        );
    }

    /**
     * Test for the updateContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::updateContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testUpdateContentTypeGroup
     */
    public function testUpdateContentTypeGroupThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $group = $contentTypeService->loadContentTypeGroupByIdentifier(
            'Media'
        );

        $groupUpdate = $contentTypeService->newContentTypeGroupUpdateStruct();
        $groupUpdate->identifier = 'Users';

        // Exception, because group with identifier "Users" exists
        $contentTypeService->updateContentTypeGroup( $group, $groupUpdate );
        /* END: Use Case */
    }

    /**
     * Test for the deleteContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::deleteContentTypeGroup()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeGroup
     */
    public function testDeleteContentTypeGroup()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $groupCreate = $contentTypeService->newContentTypeGroupCreateStruct(
            'new-group'
        );
        $contentTypeService->createContentTypeGroup( $groupCreate );

        // ...

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
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     * @group user
     * @group field-type
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
            '\\eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentTypeCreateStruct',
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
                'identifier' => 'new-type',
                'mainLanguageCode' => null,
                'remoteId' => null,
                'urlAliasSchema' => null,
                'nameSchema' => null,
                'isContainer' => false,
                'defaultSortField' => Location::SORT_FIELD_PUBLISHED,
                'defaultSortOrder' => Location::SORT_ORDER_DESC,
                'defaultAlwaysAvailable' => true,
                'names' => null,
                'descriptions' => null,
                'creatorId' => null,
                'creationDate' => null,
            ),
            $createStruct
        );
    }

    /**
     * Test for the newFieldDefinitionCreateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::newFieldDefinitionCreateStruct()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     * @group user
     * @group field-type
     */
    public function testNewFieldDefinitionCreateStruct()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $fieldDefinitionCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'title', 'ezstring'
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ContentType\\FieldDefinitionCreateStruct',
            $fieldDefinitionCreate
        );
        return $fieldDefinitionCreate;
    }

    /**
     * Test for the newFieldDefinitionCreateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::newFieldDefinitionCreateStruct()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testNewFieldDefinitionCreateStruct
     */
    public function testNewFieldDefinitionCreateStructValues( $createStruct )
    {
        $this->assertPropertiesCorrect(
            array(
                'fieldTypeIdentifier' => 'ezstring',
                'identifier' => 'title',
                'names' => null,
                'descriptions' => null,
                'fieldGroup' => null,
                'position' => null,
                'isTranslatable' => null,
                'isRequired' => null,
                'isInfoCollector' => null,
                'validatorConfiguration' => null,
                'fieldSettings' => null,
                'defaultValue' => null,
                'isSearchable' => null,
            ),
            $createStruct
        );
    }

    /**
     * Test for the deleteContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::deleteContentTypeGroup()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testDeleteContentTypeGroup
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testDeleteContentTypeGroupThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $contentGroup = $contentTypeService->loadContentTypeGroupByIdentifier( 'Content' );

        // Throws exception, since group contains types
        $contentTypeService->deleteContentTypeGroup( $contentGroup );
        /* END: Use Case */
    }

    /**
     * Test for the createContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentType()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testNewContentTypeCreateStruct
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testNewFieldDefinitionCreateStruct
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeGroupByIdentifier
     * @group user
     * @group field-type
     */
    public function testCreateContentType()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $typeCreate = $contentTypeService->newContentTypeCreateStruct( 'blog-post' );
        $typeCreate->mainLanguageCode = 'eng-GB';
        $typeCreate->remoteId = '384b94a1bd6bc06826410e284dd9684887bf56fc';
        $typeCreate->urlAliasSchema = 'url|scheme';
        $typeCreate->nameSchema = 'name|scheme';
        $typeCreate->names = array(
            'eng-GB' => 'Blog post',
            'ger-DE' => 'Blog-Eintrag',
        );
        $typeCreate->descriptions = array(
            'eng-GB' => 'A blog post',
            'ger-DE' => 'Ein Blog-Eintrag',
        );
        $typeCreate->creatorId = $repository->getCurrentUser()->id;
        $typeCreate->creationDate = $this->createDateTime();

        $titleFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'title', 'ezstring'
        );
        $titleFieldCreate->names = array(
            'eng-GB' => 'Title',
            'ger-DE' => 'Titel',
        );
        $titleFieldCreate->descriptions = array(
            'eng-GB' => 'Title of the blog post',
            'ger-DE' => 'Titel des Blog-Eintrages',
        );
        $titleFieldCreate->fieldGroup = 'blog-content';
        $titleFieldCreate->position = 1;
        $titleFieldCreate->isTranslatable = true;
        $titleFieldCreate->isRequired = true;
        $titleFieldCreate->isInfoCollector = false;
        $titleFieldCreate->validatorConfiguration = array(
            'StringLengthValidator' => array(
                'minStringLength' => 0,
                'maxStringLength' => 0,
            ),
        );
        $titleFieldCreate->fieldSettings = array();
        $titleFieldCreate->isSearchable = true;

        $typeCreate->addFieldDefinition( $titleFieldCreate );

        $bodyFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'body', 'ezstring'
        );
        $bodyFieldCreate->names = array(
            'eng-GB' => 'Body',
            'ger-DE' => 'Textkörper',
        );
        $bodyFieldCreate->descriptions = array(
            'eng-GB' => 'Body of the blog post',
            'ger-DE' => 'Textkörper des Blog-Eintrages',
        );
        $bodyFieldCreate->fieldGroup = 'blog-content';
        $bodyFieldCreate->position = 2;
        $bodyFieldCreate->isTranslatable = true;
        $bodyFieldCreate->isRequired = true;
        $bodyFieldCreate->isInfoCollector = false;
        $bodyFieldCreate->validatorConfiguration = array(
            'StringLengthValidator' => array(
                'minStringLength' => 0,
                'maxStringLength' => 0,
            ),
        );
        $bodyFieldCreate->fieldSettings = array();
        $bodyFieldCreate->isSearchable = true;

        $typeCreate->addFieldDefinition( $bodyFieldCreate );

        $groups = array(
            $contentTypeService->loadContentTypeGroupByIdentifier( 'Media' ),
            $contentTypeService->loadContentTypeGroupByIdentifier( 'Setup' )
        );

        $contentTypeDraft = $contentTypeService->createContentType(
            $typeCreate,
            $groups
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentType',
            $contentTypeDraft
        );

        return array(
            'typeCreate' => $typeCreate,
            'contentType' => $contentTypeDraft,
            'groups' => $groups,
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
        $typeCreate = $data['typeCreate'];
        $contentType = $data['contentType'];
        $groups = $data['groups'];

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
     *
     * @return void
     */
    protected function assertFieldDefinitionsCorrect( array $expectedDefinitionCreates, array $actualDefinitions )
    {
        $this->assertEquals(
            count( $expectedDefinitionCreates ),
            count( $actualDefinitions ),
            'Count of field definition creates did not match count of field definitions.'
        );

        $sorter = function ( $a, $b )
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
     *
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
     *
     * @return void
     */
    protected function assertContentTypeGroupsCorrect( $expectedGroups, $actualGroups )
    {
        $sorter = function ( $a, $b )
        {
            return strcmp( $a->id, $b->id );
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
     * Test for the createContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentType()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentType
     */
    public function testCreateContentTypeThrowsInvalidArgumentExceptionDuplicateIdentifier()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $typeCreate = $contentTypeService->newContentTypeCreateStruct( 'folder' );

        // Throws exception, since type "folder" exists
        $secondType = $contentTypeService->createContentType( $typeCreate, array() );
        /* END: Use Case */
    }

    /**
     * Test for the createContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentType()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentType
     */
    public function testCreateContentTypeThrowsInvalidArgumentExceptionDuplicateRemoteId()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $typeCreate = $contentTypeService->newContentTypeCreateStruct( 'news-article' );
        $typeCreate->remoteId = 'a3d405b81be900468eb153d774f4f0d2';

        // Throws exception, since "folder" type has this remote ID
        $secondType = $contentTypeService->createContentType( $typeCreate, array() );
        /* END: Use Case */
    }

    /**
     * Test for the createContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentType()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentType
     */
    public function testCreateContentTypeThrowsInvalidArgumentExceptionDuplicateFieldIdentifier()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $typeCreate = $contentTypeService->newContentTypeCreateStruct( 'blog-post' );

        $firstFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'title', 'ezstring'
        );
        $typeCreate->addFieldDefinition( $firstFieldCreate );

        $secondFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'title', 'ezstring'
        );
        $typeCreate->addFieldDefinition( $secondFieldCreate );

        // Throws exception, due to duplicate "title" field
        $secondType = $contentTypeService->createContentType( $typeCreate, array() );
        /* END: Use Case */
    }

    /**
     * Test for the newContentTypeUpdateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::newContentTypeUpdateStruct()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
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
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentType
     */
    public function testLoadContentTypeDraft()
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        /* BEGIN: Use Case */
        $contentTypeDraft = $this->createContentTypeDraft();

        $contentTypeDraftReloaded = $contentTypeService->loadContentTypeDraft(
            $contentTypeDraft->id
        );
        /* END: Use Case */

        $this->assertEquals(
            $contentTypeDraft,
            $contentTypeDraftReloaded
        );
    }

    /**
     * Test for the loadContentTypeDraft() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeDraft()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeDraft
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadContentTypeDraftThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        $nonExistingContentTypeId = $this->generateId( 'type', 2342 );
        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        // Throws exception, since 2342 does not exist
        $contentTypeDraft = $contentTypeService->loadContentTypeDraft( $nonExistingContentTypeId );
        /* END: Use Case */
    }

    /**
     * Test for the updateContentTypeDraft() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::updateContentTypeDraft()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeDraft
     */
    public function testUpdateContentTypeDraft()
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        $modifierId = $this->generateId( 'user', 14 );
        /* BEGIN: Use Case */
        $contentTypeDraft = $this->createContentTypeDraft();

        $typeUpdate = $contentTypeService->newContentTypeUpdateStruct();
        $typeUpdate->identifier = 'news-article';
        $typeUpdate->remoteId = '4cf35f5166fd31bf0cda859dc837e095daee9833';
        $typeUpdate->urlAliasSchema = 'url@alias|scheme';
        $typeUpdate->nameSchema = '@name@scheme@';
        $typeUpdate->isContainer = true;
        $typeUpdate->mainLanguageCode = 'ger-DE';
        $typeUpdate->defaultAlwaysAvailable = false;
        $typeUpdate->modifierId = $modifierId;
        $typeUpdate->modificationDate = $this->createDateTime();
        $typeUpdate->names = array(
            'eng-GB' => 'News article',
            'ger-DE' => 'Nachrichten-Artikel',
        );
        $typeUpdate->descriptions = array(
            'eng-GB' => 'A news article',
            'ger-DE' => 'Ein Nachrichten-Artikel',
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
            'updatedType' => $updatedType,
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
        $updatedType = $data['updatedType'];

        $expectedValues = array(
            'id' => $originalType->id,
            'names' => $updateStruct->names,
            'descriptions' => $updateStruct->descriptions,
            'identifier' => $updateStruct->identifier,
            'creationDate' => $originalType->creationDate,
            'modificationDate' => $updateStruct->modificationDate,
            'creatorId' => $originalType->creatorId,
            'modifierId' => $updateStruct->modifierId,
            'urlAliasSchema' => $updateStruct->urlAliasSchema,
            'nameSchema' => $updateStruct->nameSchema,
            'isContainer' => $updateStruct->isContainer,
            'mainLanguageCode' => $updateStruct->mainLanguageCode,
            'contentTypeGroups' => $originalType->contentTypeGroups,
            'fieldDefinitions' => $originalType->fieldDefinitions,
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
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testUpdateContentTypeDraft
     */
    public function testUpdateContentTypeDraftThrowsInvalidArgumentExceptionDuplicateIdentifier()
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        /* BEGIN: Use Case */
        $contentTypeDraft = $this->createContentTypeDraft();

        $typeUpdate = $contentTypeService->newContentTypeUpdateStruct();
        $typeUpdate->identifier = 'folder';

        // Throws exception, since type "folder" already exists
        $contentTypeService->updateContentTypeDraft( $contentTypeDraft, $typeUpdate );
        /* END: Use Case */
    }

    /**
     * Test for the updateContentTypeDraft() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::updateContentTypeDraft()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testUpdateContentTypeDraft
     */
    public function testUpdateContentTypeDraftThrowsInvalidArgumentExceptionDuplicateRemoteId()
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        /* BEGIN: Use Case */
        $contentTypeDraft = $this->createContentTypeDraft();

        $typeUpdate = $contentTypeService->newContentTypeUpdateStruct();
        $typeUpdate->remoteId = 'a3d405b81be900468eb153d774f4f0d2';

        // Throws exception, since remote ID of type "folder" is used
        $contentTypeService->updateContentTypeDraft( $contentTypeDraft, $typeUpdate );
        /* END: Use Case */
    }

    /**
     * Test for the addFieldDefinition() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::addFieldDefinition()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentType
     */
    public function testAddFieldDefinition()
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        /* BEGIN: Use Case */
        $contentTypeDraft = $this->createContentTypeDraft();

        $fieldDefCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'tags', 'ezstring'
        );
        $fieldDefCreate->names = array(
            'eng-GB' => 'Tags',
            'ger-DE' => 'Schlagworte',
        );
        $fieldDefCreate->descriptions = array(
            'eng-GB' => 'Tags of the blog post',
            'ger-DE' => 'Schlagworte des Blog-Eintrages',
        );
        $fieldDefCreate->fieldGroup = 'blog-meta';
        $fieldDefCreate->position = 1;
        $fieldDefCreate->isTranslatable = true;
        $fieldDefCreate->isRequired = true;
        $fieldDefCreate->isInfoCollector = false;
        $fieldDefCreate->validatorConfiguration = array(
            'StringLengthValidator' => array(
                'minStringLength' => 0,
                'maxStringLength' => 0,
            ),
        );
        $fieldDefCreate->fieldSettings = array();
        $fieldDefCreate->isSearchable = true;

        $contentTypeService->addFieldDefinition( $contentTypeDraft, $fieldDefCreate );
        /* END: Use Case */

        $loadedType = $contentTypeService->loadContentTypeDraft( $contentTypeDraft->id );

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentTypeDraft',
            $loadedType
        );
        return array(
            'loadedType' => $loadedType,
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
        $loadedType = $data['loadedType'];
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
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testAddFieldDefinition
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testAddFieldDefinitionThrowsInvalidArgumentExceptionDuplicateFieldIdentifier()
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        /* BEGIN: Use Case */
        $contentTypeDraft = $this->createContentTypeDraft();

        $fieldDefCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'title', 'ezstring'
        );

        // Throws an exception
        $contentTypeService->addFieldDefinition( $contentTypeDraft, $fieldDefCreate );
        /* END: Use Case */
    }

    /**
     * Test for the removeFieldDefinition() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::removeFieldDefinition()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentType
     */
    public function testRemoveFieldDefinition()
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        /* BEGIN: Use Case */
        $contentTypeDraft = $this->createContentTypeDraft();

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
            'loadedType' => $loadedType,
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
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testRemoveFieldDefinition
     */
    public function testRemoveFieldDefinitionThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        /* BEGIN: Use Case */
        $contentTypeDraft = $this->createContentTypeDraft();

        $bodyField = $contentTypeDraft->getFieldDefinition( 'body' );
        $contentTypeService->removeFieldDefinition( $contentTypeDraft, $bodyField );

        $loadedDraft = $contentTypeService->loadContentTypeDraft( $contentTypeDraft->id );

        // Throws exception, sine "body" has already been removed
        $contentTypeService->removeFieldDefinition( $loadedDraft, $bodyField );
        /* END: Use Case */
    }

    /**
     * Test for the newFieldDefinitionUpdateStruct() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::newFieldDefinitionUpdateStruct()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     */
    public function testNewFieldDefinitionUpdateStruct()
    {
        $repository = $this->getRepository();
        /* BEGIN: Use Case */
        // $draftId contains the ID of a content type draft
        $contentTypeService = $repository->getContentTypeService();

        $updateStruct = $contentTypeService->newFieldDefinitionUpdateStruct();
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ContentType\\FieldDefinitionUpdateStruct',
            $updateStruct
        );
    }

    /**
     * Test for the updateFieldDefinition() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::updateFieldDefinition()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeDraft
     */
    public function testUpdateFieldDefinition()
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        /* BEGIN: Use Case */
        $contentTypeDraft = $this->createContentTypeDraft();

        $bodyField = $contentTypeDraft->getFieldDefinition( 'body' );

        $bodyUpdateStruct = $contentTypeService->newFieldDefinitionUpdateStruct();
        $bodyUpdateStruct->identifier = 'blog-body';
        $bodyUpdateStruct->names = array(
            'eng-GB' => 'Blog post body',
            'ger-DE' => 'Blog-Eintrags-Textkörper',
        );
        $bodyUpdateStruct->descriptions = array(
            'eng-GB' => 'Blog post body of the blog post',
            'ger-DE' => 'Blog-Eintrags-Textkörper des Blog-Eintrages',
        );
        $bodyUpdateStruct->fieldGroup = 'updated-blog-content';
        $bodyUpdateStruct->position = 3;
        $bodyUpdateStruct->isTranslatable = false;
        $bodyUpdateStruct->isRequired = false;
        $bodyUpdateStruct->isInfoCollector = true;
        $bodyUpdateStruct->validatorConfiguration = array();
        $bodyUpdateStruct->fieldSettings = array(
            'textRows' => 60
        );
        $bodyUpdateStruct->isSearchable = false;

        $contentTypeService->updateFieldDefinition(
            $contentTypeDraft,
            $bodyField,
            $bodyUpdateStruct
        );
        /* END: Use Case */

        $loadedDraft = $contentTypeService->loadContentTypeDraft( $contentTypeDraft->id );
        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\ContentType\\FieldDefinition',
            ( $loadedField = $loadedDraft->getFieldDefinition( 'blog-body' ) )
        );

        return array(
            'originalField' => $bodyField,
            'updatedField' => $loadedField,
            'updateStruct' => $bodyUpdateStruct,
        );
    }

    /**
     * Test for the updateFieldDefinition() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::updateFieldDefinition()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testUpdateFieldDefinition
     */
    public function testUpdateFieldDefinitionStructValues( array $data )
    {
        $originalField = $data['originalField'];
        $updatedField = $data['updatedField'];
        $updateStruct = $data['updateStruct'];

        $this->assertPropertiesCorrect(
            array(
                'id' => $originalField->id,
                'identifier' => $updateStruct->identifier,
                'names' => $updateStruct->names,
                'descriptions' => $updateStruct->descriptions,
                'fieldGroup' => $updateStruct->fieldGroup,
                'position' => $updateStruct->position,
                'fieldTypeIdentifier' => $originalField->fieldTypeIdentifier,
                'isTranslatable' => $updateStruct->isTranslatable,
                'isRequired' => $updateStruct->isRequired,
                'isInfoCollector' => $updateStruct->isInfoCollector,
                'validatorConfiguration' => $updateStruct->validatorConfiguration,
                'defaultValue' => $originalField->defaultValue,
                'isSearchable' => $updateStruct->isSearchable,
            ),
            $updatedField
        );
    }

    /**
     * Test for the updateFieldDefinition() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::updateFieldDefinition()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeDraft
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testUpdateFieldDefinitionThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        /* BEGIN: Use Case */
        $contentTypeDraft = $this->createContentTypeDraft();

        $bodyField = $contentTypeDraft->getFieldDefinition( 'body' );
        $titleField = $contentTypeDraft->getFieldDefinition( 'title' );

        $bodyUpdateStruct = $contentTypeService->newFieldDefinitionUpdateStruct();
        $bodyUpdateStruct->identifier = 'title';

        // Throws exception, since "title" field already exists
        $contentTypeService->updateFieldDefinition(
            $contentTypeDraft,
            $bodyField,
            $bodyUpdateStruct
        );
        /* END: Use Case */
    }

    /**
     * Test for the updateFieldDefinition() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::updateFieldDefinition()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeDraft
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testUpdateFieldDefinitionThrowsInvalidArgumentExceptionForUndefinedField()
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        /* BEGIN: Use Case */
        $contentTypeDraft = $this->createContentTypeDraft();

        $bodyField = $contentTypeDraft->getFieldDefinition( 'body' );
        $contentTypeService->removeFieldDefinition( $contentTypeDraft, $bodyField );

        $loadedDraft = $contentTypeService->loadContentTypeDraft( $contentTypeDraft->id );

        $bodyUpdateStruct = $contentTypeService->newFieldDefinitionUpdateStruct();

        // Throws exception, since field "body" is already deleted
        $contentTypeService->updateFieldDefinition(
            $loadedDraft,
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
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeDraft
     */
    public function testPublishContentTypeDraft()
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        /* BEGIN: Use Case */
        $contentTypeDraft = $this->createContentTypeDraft();

        $contentTypeService->publishContentTypeDraft( $contentTypeDraft );
        /* END: Use Case */

        $publishedType = $contentTypeService->loadContentType( $contentTypeDraft->id );

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentType',
            $publishedType
        );
        $this->assertNotInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentTypeDraft',
            $publishedType
        );
    }

    /**
     * Test for the publishContentTypeDraft() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::publishContentTypeDraft()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testPublishContentTypeDraft
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testPublishContentTypeDraftThrowsBadStateException()
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        /* BEGIN: Use Case */
        $contentTypeDraft = $this->createContentTypeDraft();

        $contentTypeService->publishContentTypeDraft( $contentTypeDraft );

        // Throws exception, since no draft exists anymore
        $contentTypeService->publishContentTypeDraft( $contentTypeDraft );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentType()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentType
     * @group user
     * @group field-type
     */
    public function testLoadContentType()
    {
        $repository = $this->getRepository();

        $userGroupId = $this->generateId( 'type', 3 );
        /* BEGIN: Use Case */
        // $userGroupId is the ID of the "user_group" type
        $contentTypeService = $repository->getContentTypeService();
        // Loads the standard "user_group" type
        $userGroupType = $contentTypeService->loadContentType( $userGroupId );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentType',
            $userGroupType
        );

        return $userGroupType;
    }

    /**
     * Test for the loadContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentType()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentType
     */
    public function testLoadContentTypeStructValues( $userGroupType )
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        $this->assertPropertiesCorrect(
            array(
                'id' => $this->generateId( 'type', 3 ),
                'status' => 0,
                'identifier' => 'user_group',
                'creationDate' => $this->createDateTime( 1024392098 ),
                'modificationDate' => $this->createDateTime( 1048494743 ),
                'creatorId' => $this->generateId( 'user', 14 ),
                'modifierId' => $this->generateId( 'user', 14 ),
                'remoteId' => '25b4268cdcd01921b808a0d854b877ef',
                'names' => array(
                    'eng-US' => 'User group',
                ),
                'descriptions' => array(),
                'nameSchema' => '<name>',
                'isContainer' => true,
                'mainLanguageCode' => 'eng-US',
                'defaultAlwaysAvailable' => true,
                'defaultSortField' => 1,
                'defaultSortOrder' => 1,
                'contentTypeGroups' => array(
                    0 => $contentTypeService->loadContentTypeGroup( $this->generateId( 'typegroup', 2 ) )
                ),
            ),
            $userGroupType
        );

        return $userGroupType->fieldDefinitions;
    }

    /**
     * Test for the loadContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentType()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeStructValues
     */
    public function testLoadContentTypeFieldDefinitions( array $fieldDefinitions )
    {
        $expectedFieldDefinitions = array(
            'name' => array(
                'identifier' => 'name',
                'fieldGroup' => '',
                'position' => 1,
                'fieldTypeIdentifier' => 'ezstring',
                'isTranslatable' => true,
                'isRequired' => true,
                'isInfoCollector' => false,
                'isSearchable' => true,
                'defaultValue' => null,
                'names' => array(
                    'eng-US' => 'Name',
                ),
                'descriptions' => array(),
            ),
            'description' => array(
                'identifier' => 'description',
                'fieldGroup' => '',
                'position' => 2,
                'fieldTypeIdentifier' => 'ezstring',
                'isTranslatable' => true,
                'isRequired' => false,
                'isInfoCollector' => false,
                'isSearchable' => true,
                'defaultValue' => null,
                'names' => array(
                    'eng-US' => 'Description',
                ),
                'descriptions' => array(),
            )
        );

        foreach ( $fieldDefinitions as $index => $fieldDefinition )
        {
            $this->assertInstanceOf(
                'eZ\\Publish\\API\\Repository\\Values\\ContentType\\FieldDefinition',
                $fieldDefinition
            );

            $this->assertNotNull( $fieldDefinition->id );

            if ( !isset( $expectedFieldDefinitions[$fieldDefinition->identifier] ) )
            {
                $this->fail(
                    sprintf(
                        'Unexpected FieldDefinition loaded: "%s" (%s)',
                        $fieldDefinition->identifier,
                        $fieldDefinition->id
                    )
                );
            }

            $this->assertPropertiesCorrect(
                $expectedFieldDefinitions[$fieldDefinition->identifier],
                $fieldDefinition
            );
            unset( $expectedFieldDefinitions[$fieldDefinition->identifier] );
            unset( $fieldDefinitions[$index] );
        }

        if ( 0 !== count( $expectedFieldDefinitions ) )
        {
            $this->fail(
                sprintf(
                    'Missing expected FieldDefinitions: %s',
                    implode(
                        ',',
                        array_map(
                            function ( $fieldDefArray )
                            {
                                return $fieldDefArray['identifier'];
                            },
                            $expectedFieldDefinitions
                        )
                    )
                )
            );
        }

        if ( 0 !== count( $fieldDefinitions ) )
        {
            $this->fail(
                sprintf(
                    'Loaded unexpected FieldDefinitions: %s',
                    implode(
                        ',',
                        array_map(
                            function ( $fieldDefinition )
                            {
                                return $fieldDefinition->identifier;
                            },
                            $fieldDefinitions
                        )
                    )
                )
            );
        }
    }

    /**
     * Test for the loadContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentType()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentType
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadContentTypeThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        $nonExistentTypeId = $this->generateId( 'type', 2342 );
        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        // Throws exception, since type with ID 2342 does not exist
        $contentTypeService->loadContentType( $nonExistentTypeId );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentTypeByIdentifier() method.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeByIdentifier()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentType
     * @group user
     */
    public function testLoadContentTypeByIdentifier()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $articleType = $contentTypeService->loadContentTypeByIdentifier( 'article' );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentType',
            $articleType
        );

        return $articleType;
    }

    /**
     * Test for the loadContentTypeByIdentifier() method.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeByIdentifier()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeByIdentifier
     */
    public function testLoadContentTypeByIdentifierReturnsCorrectInstance( $contentType )
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        $this->assertEquals(
            $contentTypeService->loadContentType( $contentType->id ),
            $contentType
        );
    }

    /**
     * Test for the loadContentTypeByIdentifier() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeByIdentifier()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeByIdentifier
     */
    public function testLoadContentTypeByIdentifierThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        // Throws an exception, since no type with this identifier exists
        $contentTypeService->loadContentTypeByIdentifier( 'sindelfingen' );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentTypeByRemoteId() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeByRemoteId()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentType
     */
    public function testLoadContentTypeByRemoteId()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        // Loads the standard "user_group" type
        $userGroupType = $contentTypeService->loadContentTypeByRemoteId(
            '25b4268cdcd01921b808a0d854b877ef'
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentType',
            $userGroupType
        );

        return $userGroupType;
    }

    /**
     * Test for the loadContentTypeByRemoteId() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeByRemoteId()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeByRemoteId
     */
    public function testLoadContentTypeByRemoteIdReturnsCorrectInstance( $contentType )
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        $this->assertEquals(
            $contentTypeService->loadContentType( $contentType->id ),
            $contentType
        );
    }

    /**
     * Test for the loadContentTypeByRemoteId() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeByRemoteId()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentType
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadContentTypeByRemoteIdThrowsNotFoundException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        // Throws an exception, since no type with this remote ID exists
        $contentTypeService->loadContentTypeByRemoteId( 'not-exists' );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentTypes() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypes()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentType
     */
    public function testLoadContentTypes()
    {
        $repository = $this->getRepository();

        $typeGroupId = $this->generateId( 'typegroup', 2 );
        /* BEGIN: Use Case */
        // $typeGroupId is a valid ID of a content type group
        $contentTypeService = $repository->getContentTypeService();

        $contentTypeGroup = $contentTypeService->loadContentTypeGroup( $typeGroupId );

        // Loads all types from content type group "Users"
        $types = $contentTypeService->loadContentTypes( $contentTypeGroup );
        /* END: Use Case */

        $this->assertInternalType( 'array', $types );

        return $types;
    }

    /**
     * Test for the loadContentTypes() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypes()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypes
     */
    public function testLoadContentTypesContent( array $types )
    {
        $repository = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        $this->assertEquals(
            array(
                $contentTypeService->loadContentType( $this->generateId( 'type', 3 ) ),
                $contentTypeService->loadContentType( $this->generateId( 'type', 4 ) ),
            ),
            $types
        );
    }

    /**
     * Test for the createContentTypeDraft() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentTypeDraft()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentType
     */
    public function testCreateContentTypeDraft()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $commentType = $contentTypeService->loadContentTypeByIdentifier( 'comment' );

        $commentTypeDraft = $contentTypeService->createContentTypeDraft( $commentType );
        /* END: Use Case */

        $this->assertInstanceOf(
            'eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentTypeDraft',
            $commentTypeDraft
        );

        return array(
            'originalType' => $commentType,
            'typeDraft' => $commentTypeDraft,
        );
    }

    /**
     * Test for the createContentTypeDraft() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentTypeDraft()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentTypeDraft
     */
    public function testCreateContentTypeDraftStructValues( array $data )
    {
        $originalType = $data['originalType'];
        $typeDraft = $data['typeDraft'];

        // Names and descriptions tested in corresponding language test
        $this->assertEquals(
            array(
                'id' => $originalType->id,
                'names' => $originalType->names,
                'descriptions' => $originalType->descriptions,
                'identifier' => $originalType->identifier,
                'creatorId' => $originalType->creatorId,
                'modifierId' => $originalType->modifierId,
                'remoteId' => $originalType->remoteId,
                'urlAliasSchema' => $originalType->urlAliasSchema,
                'nameSchema' => $originalType->nameSchema,
                'isContainer' => $originalType->isContainer,
                'mainLanguageCode' => $originalType->mainLanguageCode,
                'defaultAlwaysAvailable' => $originalType->defaultAlwaysAvailable,
                'defaultSortField' => $originalType->defaultSortField,
                'defaultSortOrder' => $originalType->defaultSortOrder,
                'contentTypeGroups' => $originalType->contentTypeGroups,
                'fieldDefinitions' => $originalType->fieldDefinitions,
            ),
            array(
                'id' => $typeDraft->id,
                'names' => $typeDraft->names,
                'descriptions' => $typeDraft->descriptions,
                'identifier' => $typeDraft->identifier,
                'creatorId' => $typeDraft->creatorId,
                'modifierId' => $typeDraft->modifierId,
                'remoteId' => $typeDraft->remoteId,
                'urlAliasSchema' => $typeDraft->urlAliasSchema,
                'nameSchema' => $typeDraft->nameSchema,
                'isContainer' => $typeDraft->isContainer,
                'mainLanguageCode' => $typeDraft->mainLanguageCode,
                'defaultAlwaysAvailable' => $typeDraft->defaultAlwaysAvailable,
                'defaultSortField' => $typeDraft->defaultSortField,
                'defaultSortOrder' => $typeDraft->defaultSortOrder,
                'contentTypeGroups' => $typeDraft->contentTypeGroups,
                'fieldDefinitions' => $typeDraft->fieldDefinitions,
            )
        );

        $this->assertInstanceOf(
            'DateTime',
            $typeDraft->modificationDate
        );
        $modificationDifference = $originalType->modificationDate->diff(
            $typeDraft->modificationDate
        );
        // No modification date is newer, interval is not inverted
        $this->assertEquals( 0, $modificationDifference->invert );

        $this->assertEquals(
            ContentType::STATUS_DRAFT,
            $typeDraft->status
        );

        return $data;
    }

    /**
     * Test for the createContentTypeDraft() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentTypeDraft()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentTypeDraftStructValues
     */
    public function testCreateContentTypeDraftStructLanguageDependentValues( array $data )
    {
        $originalType = $data['originalType'];
        $typeDraft = $data['typeDraft'];

        $this->assertEquals(
            array(
                'names' => $originalType->names,
                'descriptions' => $originalType->descriptions,
            ),
            array(
                'names' => $typeDraft->names,
                'descriptions' => $typeDraft->descriptions,
            )
        );
    }

    /**
     * Test for the createContentTypeDraft() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentTypeDraft()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentTypeDraft
     */
    public function testCreateContentTypeDraftThrowsBadStateException()
    {
        $this->markTestIncomplete(
            'Behavior to test is: If a draft *by a different user* exists, throw BadState. Cannot be tested on current fixture, since additional, privileged user is missing.'
        );

        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $commentType = $contentTypeService->loadContentTypeByIdentifier( 'comment' );

        $contentTypeService->createContentTypeDraft( $commentType );

        // Throws exception, since type draft already exists
        $contentTypeService->createContentTypeDraft( $commentType );
        /* END: Use Case */
    }

    /**
     * Test for the deleteContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::deleteContentType()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeByIdentifier
     */
    public function testDeleteContentType()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $commentType = $contentTypeService->loadContentTypeByIdentifier( 'comment' );

        $contentTypeService->deleteContentType( $commentType );
        /* END: Use Case */

        try
        {
            $contentTypeService->loadContentType( $commentType->id );
            $this->fail( 'Content type could be loaded after delete.' );
        }
        catch ( Exceptions\NotFoundException $e )
        {
            // All fine
        }
    }

    /**
     * Test for the deleteContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::deleteContentType()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testDeleteContentType
     */
    public function testDeleteContentTypeThrowsBadStateException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'user' );

        // This call will fail with a "BadStateException" because there is at
        // least on content object of type "user" in an eZ Publish demo
        $contentTypeService->deleteContentType( $contentType );
        /* END: Use Case */
    }

    /**
     * Test for the copyContentType() method.
     *
     * @return array
     * @see \eZ\Publish\API\Repository\ContentTypeService::copyContentType()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeByIdentifier
     */
    public function testCopyContentType()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $commentType = $contentTypeService->loadContentTypeByIdentifier( 'comment' );

        // Complete copy of the "comment" type
        $copiedType = $contentTypeService->copyContentType( $commentType );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\\eZ\\Publish\\API\\Repository\\Values\\ContentType\\ContentType',
            $copiedType
        );

        return array(
            'originalType' => $commentType,
            'copiedType' => $copiedType,
        );
    }

    /**
     * Test for the copyContentType() method.
     *
     * @param array $data
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::copyContentType()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCopyContentType
     */
    public function testCopyContentTypeStructValues( array $data )
    {
        $originalType = $data['originalType'];
        $copiedType = $data['copiedType'];

        $this->assertStructPropertiesCorrect(
            $originalType,
            $copiedType,
            array(
                'names',
                'descriptions',
                'creatorId',
                'modifierId',
                'urlAliasSchema',
                'nameSchema',
                'isContainer',
                'mainLanguageCode',
                'contentTypeGroups',
            )
        );

        $this->assertNotEquals(
            $originalType->id,
            $copiedType->id
        );
        $this->assertNotEquals(
            $originalType->remoteId,
            $copiedType->remoteId
        );
        $this->assertNotEquals(
            $originalType->identifier,
            $copiedType->identifier
        );
        $this->assertNotEquals(
            $originalType->creationDate,
            $copiedType->creationDate
        );
        $this->assertNotEquals(
            $originalType->modificationDate,
            $copiedType->modificationDate
        );

        foreach ( $originalType->fieldDefinitions as $originalFieldDefinition )
        {
            $copiedFieldDefinition = $copiedType->getFieldDefinition(
                $originalFieldDefinition->identifier
            );

            $this->assertStructPropertiesCorrect(
                $originalFieldDefinition,
                $copiedFieldDefinition,
                array(
                    'identifier',
                    'names',
                    'descriptions',
                    'fieldGroup',
                    'position',
                    'fieldTypeIdentifier',
                    'isTranslatable',
                    'isRequired',
                    'isInfoCollector',
                    'validatorConfiguration',
                    'defaultValue',
                    'isSearchable',
                )
            );
            $this->assertNotEquals(
                $originalFieldDefinition->id,
                $copiedFieldDefinition->id
            );
        }
    }

    /**
     * Test for the copyContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::copyContentType($contentType, $user)
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCopyContentType
     */
    public function testCopyContentTypeWithSecondParameter()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $user = $this->createUserVersion1();

        $commentType = $contentTypeService->loadContentTypeByIdentifier( 'comment' );

        // Complete copy of the "comment" type
        $copiedType = $contentTypeService->copyContentType( $commentType, $user );
        /* END: Use Case */

        $this->assertPropertiesCorrect(
            array(
                'creatorId' => $user->id,
                'modifierId' => $user->id
            ),
            $copiedType
        );
    }

    /**
     * Test for the assignContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::assignContentTypeGroup()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeGroupByIdentifier
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeByIdentifier
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentType
     */
    public function testAssignContentTypeGroup()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $mediaGroup = $contentTypeService->loadContentTypeGroupByIdentifier( 'Media' );
        $folderType = $contentTypeService->loadContentTypeByIdentifier( 'folder' );

        $contentTypeService->assignContentTypeGroup( $folderType, $mediaGroup );
        /* END: Use Case */

        $loadedType = $contentTypeService->loadContentType( $folderType->id );

        foreach ( $loadedType->contentTypeGroups as $loadedGroup )
        {
            if ( $mediaGroup->id == $loadedGroup->id )
            {
                return;
            }
        }
        $this->fail(
            sprintf(
                'Group with ID "%s" not assigned to content type.',
                $mediaGroup->id
            )
        );
    }

    /**
     * Test for the assignContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::assignContentTypeGroup()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testAssignContentTypeGroup
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testAssignContentTypeGroupThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $folderType = $contentTypeService->loadContentTypeByIdentifier( 'folder' );
        $assignedGroups = $folderType->contentTypeGroups;

        foreach ( $assignedGroups as $assignedGroup )
        {
            // Throws an exception, since group is already assigned
            $contentTypeService->assignContentTypeGroup( $folderType, $assignedGroup );
        }
        /* END: Use Case */
    }

    /**
     * Test for the unassignContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::unassignContentTypeGroup()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testAssignContentTypeGroup
     */
    public function testUnassignContentTypeGroup()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $folderType = $contentTypeService->loadContentTypeByIdentifier( 'folder' );

        $mediaGroup = $contentTypeService->loadContentTypeGroupByIdentifier( 'Media' );
        $contentGroup = $contentTypeService->loadContentTypeGroupByIdentifier( 'Content' );

        // May not unassign last group
        $contentTypeService->assignContentTypeGroup( $folderType, $mediaGroup );

        $contentTypeService->unassignContentTypeGroup( $folderType, $contentGroup );
        /* END: Use Case */

        $loadedType = $contentTypeService->loadContentType( $folderType->id );

        foreach ( $loadedType->contentTypeGroups as $assignedGroup )
        {
            if ( $assignedGroup->id == $contentGroup->id )
            {
                $this->fail(
                    sprintf(
                        'Group with ID "%s" not unassigned.',
                        $assignedGroup->id
                    )
                );
            }
        }
    }

    /**
     * Test for the unassignContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::unassignContentTypeGroup()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testUnassignContentTypeGroup
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testUnassignContentTypeGroupThrowsInvalidArgumentException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $folderType = $contentTypeService->loadContentTypeByIdentifier( 'folder' );
        $notAssignedGroup = $contentTypeService->loadContentTypeGroupByIdentifier( 'Media' );

        // Throws an exception, since "Media" group is not assigned to "folder"
        $contentTypeService->unassignContentTypeGroup( $folderType, $notAssignedGroup );
        /* END: Use Case */
    }

    /**
     * Test for the unassignContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::unassignContentTypeGroup()
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testUnassignContentTypeGroup
     * @expectedException \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function testUnassignContentTypeGroupThrowsBadStateException()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $folderType = $contentTypeService->loadContentTypeByIdentifier( 'folder' );
        $assignedGroups = $folderType->contentTypeGroups;

        foreach ( $assignedGroups as $assignedGroup )
        {
            // Throws an exception, when last group is to be removed
            $contentTypeService->unassignContentTypeGroup( $folderType, $assignedGroup );
        }
        /* END: Use Case */
    }

    /**
     * Test for the createContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentTypeGroup()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testRollback
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeGroup
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentTypeGroup
     */
    public function testCreateContentTypeGroupInTransactionWithRollback()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        // Get create struct and set language property
        $groupCreate = $contentTypeService->newContentTypeGroupCreateStruct( 'new-group' );
        /* @todo uncomment when support for multilingual names and descriptions is added
        $groupCreate->mainLanguageCode = 'eng-GB';
        */

        // Start a new transaction
        $repository->beginTransaction();

        try
        {
            // Create the new content type group
            $groupId = $contentTypeService->createContentTypeGroup( $groupCreate )->id;
        }
        catch ( \Exception $e )
        {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // Rollback all changes
        $repository->rollback();

        try
        {
            // This call will fail with a "NotFoundException"
            $contentTypeService->loadContentTypeGroup( $groupId );
        }
        catch ( NotFoundException $e )
        {
            return;
        }
        /* END: Use Case */

        $this->fail( 'Can still load content type group after rollback' );
    }

    /**
     * Test for the createContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentTypeGroup()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testCommit
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeGroup
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentTypeGroup
     */
    public function testCreateContentTypeGroupInTransactionWithCommit()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        // Get create struct and set language property
        $groupCreate = $contentTypeService->newContentTypeGroupCreateStruct( 'new-group' );
        /* @todo uncomment when support for multilingual names and descriptions is added
        $groupCreate->mainLanguageCode = 'eng-GB';
        */

        // Start a new transaction
        $repository->beginTransaction();

        try
        {
            // Create the new content type group
            $groupId = $contentTypeService->createContentTypeGroup( $groupCreate )->id;

            // Rollback all changes
            $repository->commit();
        }
        catch ( \Exception $e )
        {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // Load created content type group
        $group = $contentTypeService->loadContentTypeGroup( $groupId );
        /* END: Use Case */

        $this->assertEquals( $groupId, $group->id );
    }

    /**
     * Test for the updateContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::updateContentTypeGroup()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testRollback
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testUpdateContentTypeGroup
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeGroupByIdentifier
     */
    public function testUpdateContentTypeGroupInTransactionWithRollback()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        // Load an existing group
        $group = $contentTypeService->loadContentTypeGroupByIdentifier( 'Setup' );

        // Get an update struct and change the identifier
        $groupUpdate = $contentTypeService->newContentTypeGroupUpdateStruct();
        $groupUpdate->identifier = 'Teardown';

        // Start a new transaction
        $repository->beginTransaction();

        try
        {
            // Apply update to group
            $contentTypeService->updateContentTypeGroup( $group, $groupUpdate );
        }
        catch ( \Exception $e )
        {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // Rollback all changes
        $repository->rollback();

        // Load updated group, it will be unchanged
        $updatedGroup = $contentTypeService->loadContentTypeGroupByIdentifier( 'Setup' );
        /* END: Use Case */

        $this->assertEquals( 'Setup', $updatedGroup->identifier );
    }

    /**
     * Test for the updateContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::updateContentTypeGroup()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testCommit
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testUpdateContentTypeGroup
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeGroupByIdentifier
     */
    public function testUpdateContentTypeGroupInTransactionWithCommit()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        // Load an existing group
        $group = $contentTypeService->loadContentTypeGroupByIdentifier( 'Setup' );

        // Get an update struct and change the identifier
        $groupUpdate = $contentTypeService->newContentTypeGroupUpdateStruct();
        $groupUpdate->identifier = 'Teardown';

        // Start a new transaction
        $repository->beginTransaction();

        try
        {
            // Apply update to group
            $contentTypeService->updateContentTypeGroup( $group, $groupUpdate );

            // Commit all changes
            $repository->commit();
        }
        catch ( \Exception $e )
        {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // Load updated group by it's new identifier "Teardown"
        $updatedGroup = $contentTypeService->loadContentTypeGroupByIdentifier(
            'Teardown'
        );
        /* END: Use Case */

        $this->assertEquals( 'Teardown', $updatedGroup->identifier );
    }

    /**
     * Test for the deleteContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::deleteContentTypeGroup()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testRollback
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testDeleteContentTypeGroup
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeGroupByIdentifierThrowsNotFoundException
     */
    public function testDeleteContentTypeGroupWithRollback()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        // Get a group create struct
        $groupCreate = $contentTypeService->newContentTypeGroupCreateStruct(
            'new-group'
        );

        // Start a new transaction
        $repository->beginTransaction();

        try
        {
            // Create the new group
            $group = $contentTypeService->createContentTypeGroup( $groupCreate );

            // Delete the currently created group
            $contentTypeService->deleteContentTypeGroup( $group );
        }
        catch ( \Exception $e )
        {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // Rollback all changes
        $repository->rollback();

        try
        {
            // This call will fail with an "NotFoundException"
            $contentTypeService->loadContentTypeGroupByIdentifier( 'new-group' );
        }
        catch ( NotFoundException $e )
        {
            // Expected error path
        }
        /* END: Use Case */

        $this->assertTrue( isset( $e ), 'Group not deleted after rollback' );
    }

    /**
     * Test for the deleteContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::deleteContentTypeGroup()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testCommit
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testDeleteContentTypeGroup
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeGroupByIdentifierThrowsNotFoundException
     */
    public function testDeleteContentTypeGroupWithCommit()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        // Get a group create struct
        $groupCreate = $contentTypeService->newContentTypeGroupCreateStruct(
            'new-group'
        );

        // Start a new transaction
        $repository->beginTransaction();

        try
        {
            // Create the new group
            $group = $contentTypeService->createContentTypeGroup( $groupCreate );

            // Delete the currently created group
            $contentTypeService->deleteContentTypeGroup( $group );

            // Commit all changes
            $repository->commit();
        }
        catch ( \Exception $e )
        {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        try
        {
            // This call will fail with an "NotFoundException"
            $contentTypeService->loadContentTypeGroupByIdentifier( 'new-group' );
        }
        catch ( NotFoundException $e )
        {
            // Expected error path
        }
        /* END: Use Case */

        $this->assertTrue( isset( $e ), 'Group not deleted after commit.' );
    }

    /**
     * Test for the createContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentType()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testRollback
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentType
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeByIdentifierThrowsNotFoundException
     */
    public function testCreateContentTypeInTransactionWithRollback()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        // Start a new transaction
        $repository->beginTransaction();

        try
        {
            // Get create struct and set some properties
            $typeCreate = $contentTypeService->newContentTypeCreateStruct( 'blog-post' );
            $typeCreate->mainLanguageCode = 'eng-GB';
            $typeCreate->names = array( 'eng-GB' => 'Blog post' );

            $titleFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct(
                'title', 'ezstring'
            );
            $titleFieldCreate->names = array( 'eng-GB' => 'Title' );
            $titleFieldCreate->position = 1;
            $typeCreate->addFieldDefinition( $titleFieldCreate );

            $groups = array(
                $contentTypeService->loadContentTypeGroupByIdentifier( 'Setup' )
            );

            // Create content type
            $contentTypeDraft = $contentTypeService->createContentType(
                $typeCreate,
                $groups
            );

            // Publish the content type draft
            $contentTypeService->publishContentTypeDraft( $contentTypeDraft );
        }
        catch ( \Exception $e )
        {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // Rollback all changes.
        $repository->rollback();

        try
        {
            // This call will fail with a "NotFoundException"
            $contentTypeService->loadContentTypeByIdentifier( 'blog-post' );
        }
        catch ( NotFoundException $e )
        {
            // Expected execution path
        }
        /* END: Use Case */

        $this->assertTrue( isset( $e ), 'Can still load content type after rollback.' );
    }

    /**
     * Test for the createContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentType()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testCommit
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentType
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeByIdentifierThrowsNotFoundException
     */
    public function testCreateContentTypeInTransactionWithCommit()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        // Start a new transaction
        $repository->beginTransaction();

        try
        {
            // Get create struct and set some properties
            $typeCreate = $contentTypeService->newContentTypeCreateStruct( 'blog-post' );
            $typeCreate->mainLanguageCode = 'eng-GB';
            $typeCreate->names = array( 'eng-GB' => 'Blog post' );

            $titleFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct(
                'title', 'ezstring'
            );
            $titleFieldCreate->names = array( 'eng-GB' => 'Title' );
            $titleFieldCreate->position = 1;
            $typeCreate->addFieldDefinition( $titleFieldCreate );

            $groups = array(
                $contentTypeService->loadContentTypeGroupByIdentifier( 'Setup' )
            );

            // Create content type
            $contentTypeDraft = $contentTypeService->createContentType(
                $typeCreate,
                $groups
            );

            // Publish the content type draft
            $contentTypeService->publishContentTypeDraft( $contentTypeDraft );

            // Commit all changes.
            $repository->commit();
        }
        catch ( \Exception $e )
        {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // Load the newly created content type
        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'blog-post' );
        /* END: Use Case */

        $this->assertEquals( $contentTypeDraft->id, $contentType->id );
    }

    /**
     * Test for the copyContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::copyContentType()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testRollback
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCopyContentType
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeByIdentifier
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeThrowsNotFoundException
     */
    public function testCopyContentTypeInTransactionWithRollback()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        // Load content type to copy
        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'comment' );

        // Start a new transaction
        $repository->beginTransaction();

        try
        {
            // Complete copy of the content type
            $copiedType = $contentTypeService->copyContentType( $contentType );
        }
        catch ( \Exception $e )
        {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // Rollback all changes
        $repository->rollback();

        try
        {
            // This call will fail with a "NotFoundException"
            $contentTypeService->loadContentType( $copiedType->id );
        }
        catch ( NotFoundException $e )
        {
            // Expected execution path
        }
        /* END: Use Case */

        $this->assertTrue( isset( $e ), 'Can still load copied content type after rollback.' );
    }

    /**
     * Test for the copyContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::copyContentType()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testCommit
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCopyContentType
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeByIdentifier
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeThrowsNotFoundException
     */
    public function testCopyContentTypeInTransactionWithCommit()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        // Load content type to copy
        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'comment' );

        // Start a new transaction
        $repository->beginTransaction();

        try
        {
            // Complete copy of the content type
            $contentTypeId = $contentTypeService->copyContentType( $contentType )->id;

            // Commit all changes
            $repository->commit();
        }
        catch ( \Exception $e )
        {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // Load the new content type copy.
        $copiedContentType = $contentTypeService->loadContentType( $contentTypeId );
        /* END: Use Case */

        $this->assertEquals( $contentTypeId, $copiedContentType->id );
    }

    /**
     * Test for the deleteContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::deleteContentType()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testRollback
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCopyContentType
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeByIdentifierThrowsNotFoundException
     */
    public function testDeleteContentTypeInTransactionWithRollback()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        // Load content type to copy
        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'comment' );

        // Start a new transaction
        $repository->beginTransaction();

        try
        {
            // Delete the "comment" content type.
            $contentTypeService->deleteContentType( $contentType );
        }
        catch ( \Exception $e )
        {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // Rollback all changes
        $repository->rollback();

        // Load currently deleted and rollbacked content type
        $commentType = $contentTypeService->loadContentTypeByIdentifier( 'comment' );
        /* END: Use Case */

        $this->assertEquals( 'comment', $commentType->identifier );
    }

    /**
     * Test for the deleteContentType() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::deleteContentType()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testCommit
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCopyContentType
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testLoadContentTypeByIdentifierThrowsNotFoundException
     */
    public function testDeleteContentTypeInTransactionWithCommit()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        // Load content type to copy
        $contentType = $contentTypeService->loadContentTypeByIdentifier( 'comment' );

        // Start a new transaction
        $repository->beginTransaction();

        try
        {
            // Delete the "comment" content type.
            $contentTypeService->deleteContentType( $contentType );

            // Commit all changes
            $repository->commit();
        }
        catch ( \Exception $e )
        {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        try
        {
            // This call will fail with a "NotFoundException"
            $contentTypeService->loadContentTypeByIdentifier( 'comment' );
        }
        catch ( NotFoundException $e )
        {
            // Expected execution path
        }
        /* END: Use Case */

        $this->assertTrue( isset( $e ), 'Can still load content type after rollback.' );
    }

    /**
     * Test for the assignContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::assignContentTypeGroup()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testRollback
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testAssignContentTypeGroup
     */
    public function testAssignContentTypeGroupInTransactionWithRollback()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $mediaGroup = $contentTypeService->loadContentTypeGroupByIdentifier( 'Media' );
        $folderType = $contentTypeService->loadContentTypeByIdentifier( 'folder' );

        // Start a new transaction
        $repository->beginTransaction();

        try
        {
            // Assign group to content type
            $contentTypeService->assignContentTypeGroup( $folderType, $mediaGroup );
        }
        catch ( \Exception $e )
        {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // Rollback all changes
        $repository->rollback();

        // Load all content types assigned to media group
        $contentTypes = $contentTypeService->loadContentTypes( $mediaGroup );

        $contentTypeIds = array();
        foreach ( $contentTypes as $contentType )
        {
            $contentTypeIds[] = $contentType->id;
        }
        /* END: Use Case */

        $this->assertFalse(
            in_array( $folderType->id, $contentTypeIds ),
            'Folder content type is still in media group after rollback.'
        );
    }

    /**
     * Test for the assignContentTypeGroup() method.
     *
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::assignContentTypeGroup()
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testCommit
     * @depends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testAssignContentTypeGroup
     */
    public function testAssignContentTypeGroupInTransactionWithCommit()
    {
        $repository = $this->getRepository();

        /* BEGIN: Use Case */
        $contentTypeService = $repository->getContentTypeService();

        $mediaGroup = $contentTypeService->loadContentTypeGroupByIdentifier( 'Media' );
        $folderType = $contentTypeService->loadContentTypeByIdentifier( 'folder' );

        // Start a new transaction
        $repository->beginTransaction();

        try
        {
            // Assign group to content type
            $contentTypeService->assignContentTypeGroup( $folderType, $mediaGroup );

            // Commit all changes
            $repository->commit();
        }
        catch ( \Exception $e )
        {
            // Cleanup hanging transaction on error
            $repository->rollback();
            throw $e;
        }

        // Load all content types assigned to media group
        $contentTypes = $contentTypeService->loadContentTypes( $mediaGroup );

        $contentTypeIds = array();
        foreach ( $contentTypes as $contentType )
        {
            $contentTypeIds[] = $contentType->id;
        }
        /* END: Use Case */

        $this->assertTrue(
            in_array( $folderType->id, $contentTypeIds ),
            'Folder content type not in media group after commit.'
        );
    }
}
