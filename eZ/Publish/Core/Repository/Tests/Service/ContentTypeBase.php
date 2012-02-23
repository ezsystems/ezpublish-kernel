<?php
/**
 * File contains: ezp\Publish\PublicAPI\Tests\Service\ContentTypeTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\Service;
use eZ\Publish\Core\Repository\Tests\Service\Base as BaseServiceTest,
    eZ\Publish\Core\Repository\Repository,
    eZ\Publish\API\Repository\Values\Content\Location,
    eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct,
    eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;

use ezp\Content\FieldType\Value;

/**
 * Test case for Language Service
 */
abstract class ContentTypeBase extends BaseServiceTest
{
    /**
     * Test for the newContentTypeGroupCreateStruct() method.
     *
     * @group groups
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct
     * @see \eZ\Publish\API\Repository\ContentTypeService::newContentTypeGroupCreateStruct()
     */
    public function testNewContentTypeGroupCreateStruct()
    {
        /* BEGIN: Use Case */
        $contentTypeService = $this->repository->getContentTypeService();

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
     * @group groups
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct $createStruct
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::newContentTypeGroupCreateStruct()
     * @depends testNewContentTypeGroupCreateStruct
     */
    public function testNewContentTypeGroupCreateStructValues( $createStruct )
    {
        $this->assertPropertiesCorrect(
            array(
                'identifier'       => 'new-group',
                'creatorId'        => null,
                'creationDate'     => null,
                'mainLanguageCode' => null,
                'names'            => null,
                'descriptions'     => null
            ),
            $createStruct
        );
    }

    /**
     * Test for the createContentTypeGroup() method.
     *
     * @group groups
     * @return array
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentTypeGroup()
     */
    public function testCreateContentTypeGroup()
    {
        /* BEGIN: Use Case */
        $contentTypeService = $this->repository->getContentTypeService();

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
     * Test for the newContentTypeGroupCreateStruct() method.
     *
     * @group groups
     * @param array $data
     * @return void
     * @see \eZ\Publish\Core\Repository\ContentTypeService::newContentTypeGroupCreateStruct()
     * @depends testCreateContentTypeGroup
     * @todo remove $notImplemented when implemented
     */
    public function testCreateContentTypeGroupStructValues( array $data )
    {
        $notImplemented = array(
            'descriptions',
            'mainLanguageCode'
        );

        $this->assertStructPropertiesCorrect(
            $data['expected'],
            $data['actual'],
            $notImplemented
        );

        $this->assertNotNull(
            $data['actual']->id
        );
    }

    /**
     * Test for the createContentTypeGroup() method.
     *
     * @group groups
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
     * @group groups
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     * @todo remove setting creatorId when users are plugged in
     */
    public function testCreateContentTypeGroupThrowsIllegalArgumentException()
    {
        /* BEGIN: Use Case */
        $contentTypeService = $this->repository->getContentTypeService();

        $groupCreate = $contentTypeService->newContentTypeGroupCreateStruct(
            'new-group'
        );
        $groupCreate->creatorId = 23;
        $contentTypeService->createContentTypeGroup( $groupCreate );

        $secondGroupCreate = $contentTypeService->newContentTypeGroupCreateStruct(
            'new-group'
        );

        // Throws an Exception
        $contentTypeService->createContentTypeGroup( $secondGroupCreate );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentTypeGroup() method.
     *
     * @group groups
     * @return array
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeGroup()
     */
    public function testLoadContentTypeGroup()
    {
        $contentTypeService = $this->repository->getContentTypeService();

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
     * @group groups
     * @param array $data
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeGroup()
     * @depends testLoadContentTypeGroup
     */
    public function testLoadContentTypeGroupValues( array $data )
    {
        $this->assertLoadContentTypeGroupValues( $data );
    }

    /**
     * Test for the loadContentTypeGroup() method.
     *
     * @group groups
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadContentTypeGroupThrowsNotFoundException()
    {
        /* BEGIN: Use Case */
        $contentTypeService = $this->repository->getContentTypeService();

        // Throws exception
        $loadedGroup = $contentTypeService->loadContentTypeGroup( PHP_INT_MAX );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentTypeGroupByIdentifier() method.
     *
     * @group groups
     * @return array
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeGroupByIdentifier()
     * @todo remove setting creatorId when users are plugged in
     */
    public function testLoadContentTypeGroupByIdentifier()
    {
        /* BEGIN: Use Case */
        $storedGroup = $this->createContentTypeGroup();
        $contentTypeService = $this->repository->getContentTypeService();

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
     * @group groups
     * @param array $data
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeGroupByIdentifier()
     * @depends testLoadContentTypeGroupByIdentifier
     * @todo remove $notImplemented when implemented
     */
    public function testLoadContentTypeGroupByIdentifierValues( array $data )
    {
        $this->assertLoadContentTypeGroupValues( $data );
    }

    protected function assertLoadContentTypeGroupValues( array $data )
    {
        /** @var $storedGroup \eZ\Publish\Core\Repository\Values\ContentType\ContentTypeGroup */
        $storedGroup = $data['expected'];
        /** @var $loadedGroup \eZ\Publish\Core\Repository\Values\ContentType\ContentTypeGroup */
        $loadedGroup = $data['actual'];
        $notImplemented = array(
            "mainLanguageCode",
            "names",
            "descriptions"
        );

        $this->assertPropertiesCorrect(
            array(
                "id"               => $storedGroup->id,
                "identifier"       => $storedGroup->identifier,
                "creationDate"     => $storedGroup->creationDate,
                "modificationDate" => $storedGroup->creationDate,
                "creatorId"        => $storedGroup->creatorId,
                "modifierId"       => $storedGroup->creatorId,
                "mainLanguageCode" => $storedGroup->mainLanguageCode,
                "names"            => $storedGroup->names,
                "descriptions"     => $storedGroup->descriptions
            ),
            $loadedGroup,
            $notImplemented
        );
    }

    /**
     * Test for the loadContentTypeGroupByIdentifier() method.
     *
     * @group groups
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeGroupByIdentifier()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadContentTypeGroupByIdentifierThrowsNotFoundException()
    {
        /* BEGIN: Use Case */
        $contentTypeService = $this->repository->getContentTypeService();

        // Throws exception
        $loadedGroup = $contentTypeService->loadContentTypeGroupByIdentifier(
            'the-no-identifier-like-this'
        );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentTypeGroups() method.
     *
     * @group groups
     * @return array
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeGroups()
     * @todo remove setting creatorId when users are plugged in
     */
    public function testLoadContentTypeGroups()
    {
        /* BEGIN: Use Case */
        $contentTypeService = $this->repository->getContentTypeService();
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
     * @group groups
     * @param array $groups
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeGroups()
     * @depends testLoadContentTypeGroups
     */
    public function testLoadContentTypeGroupsIdentifiers( array $groups )
    {
        $expectedIdentifiers = array( 'Content' => true, 'Users' => true, 'Media' => true, 'Setup' => true );

        $this->assertEquals( count( $expectedIdentifiers ), count( $groups ) );

        $actualIdentifiers  = array( 'Content' => false, 'Users' => false, 'Media' => false, 'Setup' => false );

        foreach ( $groups as $group )
        {
            $actualIdentifiers[$group->identifier] = true;
        }

        $this->assertEquals(
            $expectedIdentifiers,
            $actualIdentifiers,
            'Identifier mismatch in loaded groups.'
        );
    }

    /**
     * Test for the newContentTypeGroupUpdateStruct() method.
     *
     * @group groups
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::newContentTypeGroupUpdateStruct()
     */
    public function testNewContentTypeGroupUpdateStruct()
    {
        /* BEGIN: Use Case */
        $contentTypeService = $this->repository->getContentTypeService();

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
     * @group groups
     * @return array
     * @see \eZ\Publish\API\Repository\ContentTypeService::updateContentTypeGroup()
     */
    public function testUpdateContentTypeGroup()
    {
        /* BEGIN: Use Case */
        $contentTypeService = $this->repository->getContentTypeService();

        $this->createContentTypeGroup();
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
     * @group groups
     * @param array $data
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::updateContentTypeGroup()
     * @depends testUpdateContentTypeGroup
     * @todo remove $notImplemented when implemented
     */
    public function testUpdateContentTypeGroupStructValues( array $data )
    {
        $originalGroup = $data['originalGroup'];
        $updateStruct = $data['updateStruct'];
        $updatedGroup = $data['updatedGroup'];

        $notImplemented = array(
            "mainLanguageCode",
            "names",
            "descriptions"
        );

        $expectedValues = array(
            'identifier'       => $updateStruct->identifier,
            'creationDate'     => $originalGroup->creationDate,
            'modificationDate' => $updateStruct->modificationDate,
            'creatorId'        => $originalGroup->creatorId,
            'modifierId'       => $updateStruct->modifierId,
            'mainLanguageCode' => $updateStruct->mainLanguageCode,
            'names'            => $updateStruct->names,
            'descriptions'     => $updateStruct->descriptions,
        );

        $this->assertPropertiesCorrect(
            $expectedValues,
            $updatedGroup,
            $notImplemented
        );
    }

    /**
     * Creates a group with identifier "new-group"
     *
     * @group groups
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup
     */
    protected function createContentTypeGroup()
    {
        $contentTypeService = $this->repository->getContentTypeService();

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
     * @group groups
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
     * @group groups
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::updateContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     * @dep_ends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testUpdateContentTypeGroup
     */
    public function testUpdateContentTypeGroupThrowsIllegalArgumentException()
    {
        $this->createContentTypeGroup();

        /* BEGIN: Use Case */
        $contentTypeService = $this->repository->getContentTypeService();

        $groupCreate = $contentTypeService->newContentTypeGroupCreateStruct(
            'updated-group'
        );
        $groupCreate->creatorId = 23;
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
     * @group groups
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::deleteContentTypeGroup()
     * @dep_ends eZ\Publish\API\Repository\Tests\ContentTypeServiceTest::testCreateContentTypeGroup
     */
    public function testDeleteContentTypeGroup()
    {
        $this->createContentTypeGroup();

        /* BEGIN: Use Case */
        $contentTypeService = $this->repository->getContentTypeService();

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
     * Test for the deleteContentTypeGroup() method.
     *
     * @group groups
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::deleteContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     */
    public function testDeleteContentTypeGroupThrowsIllegalArgumentException()
    {
        $this->createContentTypeGroup();

        /* BEGIN: Use Case */
        $contentTypeService = $this->repository->getContentTypeService();
        $contentGroup = $contentTypeService->loadContentTypeGroup( 1 );

        // Throws exception because group content type has instances
        $contentTypeService->deleteContentTypeGroup( $contentGroup );
        /* END: Use Case */
    }

    /**
     * Test for the deleteContentTypeGroup() method.
     *
     * @group groups
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::deleteContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     */
    public function testDeleteContentTypeGroupThrowsUnauthorizedException()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::deleteContentTypeGroup() is not implemented." );
    }

    /**
     * Creates a number of ContentTypeGroup objects and returns them
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup[]
     */
    protected function createGroups()
    {
        $contentTypeService = $this->repository->getContentTypeService();

        $groups = array();

        $groupCreate = $contentTypeService->newContentTypeGroupCreateStruct(
            'first-group'
        );
        $groupCreate->creatorId        = 23;
        $groupCreate->creationDate     = new \DateTime();
        $groupCreate->mainLanguageCode = 'de_DE';
        $groupCreate->names            = array( 'en_US' => 'A name.' );
        $groupCreate->descriptions     = array( 'en_US' => 'A description.' );
        $groups[] = $contentTypeService->createContentTypeGroup( $groupCreate );

        $groupCreate->identifier = 'second-group';
        $groups[] = $contentTypeService->createContentTypeGroup( $groupCreate );

        return $groups;
    }

    /**
     * Creates (and publishes) a ContentType with identifier "new-type" and remoteId "new-remoteid"
     *
     * @param bool $draft
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    protected function createContentType( $draft = false )
    {
        $contentTypeService = $this->repository->getContentTypeService();

        $typeCreateStruct = $contentTypeService->newContentTypeCreateStruct(
            'new-type'
        );
        $typeCreateStruct->names = array(
            'eng-US' => 'American type title',
            'eng-GB' => 'British type title'
        );
        $typeCreateStruct->descriptions = array(
            'eng-US' => 'American type description',
            'eng-GB' => 'British type description'
        );
        $typeCreateStruct->remoteId             = "new-remoteid";
        $typeCreateStruct->creatorId            = 23;
        $typeCreateStruct->creationDate         = new \DateTime();
        $typeCreateStruct->mainLanguageCode     = 'eng-GB';
        $typeCreateStruct->nameSchema           = "<name>";
        $typeCreateStruct->urlAliasSchema       = "<name>";

        $titleFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'title',
            'ezstring'
        );
        $titleFieldCreate->names = array(
            'eng-US' => 'American title field name',
            'eng-GB' => 'British title field name',
        );
        $titleFieldCreate->descriptions = array(
            'eng-US' => 'American title field description',
            'eng-GB' => 'British title field description',
        );
        $titleFieldCreate->fieldGroup      = 'blog-content';
        $titleFieldCreate->position        = 1;
        $titleFieldCreate->isTranslatable  = true;
        $titleFieldCreate->isRequired      = true;
        $titleFieldCreate->isInfoCollector = false;
        $titleFieldCreate->isSearchable    = true;
        $titleFieldCreate->defaultValue    = new \eZ\Publish\SPI\Persistence\Content\FieldValue(
            array(
                "data" => new \ezp\Content\FieldType\TextLine\Value( 'New text line' )
            )
        );
        $titleFieldCreate->validators = array(
            // @todo
        );
        $titleFieldCreate->fieldSettings = array(
            // @todo
        );
        $typeCreateStruct->addFieldDefinition( $titleFieldCreate );

        $bodyFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'body',
            'eztext'
        );
        $bodyFieldCreate->names = array(
            'eng-US' => 'American body field name',
            'eng-GB' => 'British body field name',
        );
        $bodyFieldCreate->descriptions = array(
            'eng-US' => 'American body field description',
            'eng-GB' => 'British body field description',
        );
        $bodyFieldCreate->fieldGroup      = 'blog-content';
        $bodyFieldCreate->position        = 2;
        $bodyFieldCreate->isTranslatable  = true;
        $bodyFieldCreate->isRequired      = false;
        $bodyFieldCreate->isInfoCollector = false;
        $bodyFieldCreate->isSearchable    = true;
        $titleFieldCreate->defaultValue   = new \eZ\Publish\SPI\Persistence\Content\FieldValue(
            array(
                "data" => new \ezp\Content\FieldType\TextBlock\Value( 'New text line' )
            )
        );
        $bodyFieldCreate->validators = array(
            // @todo
        );
        $bodyFieldCreate->fieldSettings = array(
            // @todo
        );
        $typeCreateStruct->addFieldDefinition( $titleFieldCreate );

        $groups = $this->createGroups();

        $type = $contentTypeService->createContentType(
            $typeCreateStruct,
            $groups
        );

        if ( $draft === true )
        {
            return $type;
        }

        $contentTypeService->publishContentTypeDraft( $type );

        return $contentTypeService->loadContentType( $type->id );
    }

    /**
     * Creates a ContentTypeDraft with identifier "new-type" and remoteId "new-remoteid"
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     */
    protected function createContentTypeDraft()
    {
        return $this->createContentType( true );
    }

    /**
     * Test for the createContentTypeGroup() method.
     *
     * @group now
     * @group types
     * @return array
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentTypeGroup()
     */
    public function testCreateContentType()
    {
        /* BEGIN: Use Case */
        $contentTypeService = $this->repository->getContentTypeService();
        $typeCreateStruct = $contentTypeService->newContentTypeCreateStruct(
            'new-type'
        );
        $typeCreateStruct->names = array(
            'eng-US' => 'American type title',
            'eng-GB' => 'British type title'
        );
        $typeCreateStruct->descriptions = array(
            'eng-US' => 'American type description',
            'eng-GB' => 'British type description'
        );
        $typeCreateStruct->remoteId             = "new-remoteid";
        $typeCreateStruct->creatorId            = 23;
        $typeCreateStruct->creationDate         = new \DateTime();
        $typeCreateStruct->mainLanguageCode     = 'eng-GB';
        $typeCreateStruct->nameSchema           = "<name>";
        $typeCreateStruct->urlAliasSchema       = "<name>";

        $titleFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'title',
            'ezstring'
        );
        $titleFieldCreate->names = array(
            'eng-US' => 'American title field name',
            'eng-GB' => 'British title field name',
        );
        $titleFieldCreate->descriptions = array(
            'eng-US' => 'American title field description',
            'eng-GB' => 'British title field description',
        );
        $titleFieldCreate->fieldGroup      = 'blog-content';
        $titleFieldCreate->position        = 1;
        $titleFieldCreate->isTranslatable  = true;
        $titleFieldCreate->isRequired      = true;
        $titleFieldCreate->isInfoCollector = false;
        $titleFieldCreate->isSearchable    = true;
        $titleFieldCreate->defaultValue    = new \eZ\Publish\SPI\Persistence\Content\FieldValue(
            array(
                "data" => new \ezp\Content\FieldType\TextLine\Value( 'New text line' )
            )
        );
        $titleFieldCreate->validators = array(
            // @todo
        );
        $titleFieldCreate->fieldSettings = array(
            // @todo
        );
        $typeCreateStruct->addFieldDefinition( $titleFieldCreate );

        $bodyFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'body',
            'eztext'
        );
        $bodyFieldCreate->names = array(
            'eng-US' => 'American body field name',
            'eng-GB' => 'British body field name',
        );
        $bodyFieldCreate->descriptions = array(
            'eng-US' => 'American body field description',
            'eng-GB' => 'British body field description',
        );
        $bodyFieldCreate->fieldGroup      = 'blog-content';
        $bodyFieldCreate->position        = 2;
        $bodyFieldCreate->isTranslatable  = true;
        $bodyFieldCreate->isRequired      = false;
        $bodyFieldCreate->isInfoCollector = false;
        $bodyFieldCreate->isSearchable    = true;
        $titleFieldCreate->defaultValue   = new \eZ\Publish\SPI\Persistence\Content\FieldValue(
            array(
                "data" => new \ezp\Content\FieldType\TextBlock\Value( 'New text line' )
            )
        );
        $bodyFieldCreate->validators = array(
            // @todo
        );
        $bodyFieldCreate->fieldSettings = array(
            // @todo
        );
        $typeCreateStruct->addFieldDefinition( $titleFieldCreate );

        $groups = $this->createGroups();

        $type = $contentTypeService->createContentType(
            $typeCreateStruct,
            $groups
        );

        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft',
            $type
        );

        return array(
            'expected' => $typeCreateStruct,
            'actual'   => $type,
            'groups'   => $groups
        );
    }

    /**
     * Test for the newContentTypeGroupCreateStruct() method.
     *
     * @group types
     * @param array $data
     * @return void
     * @see \eZ\Publish\Core\Repository\ContentTypeService::newContentTypeGroupCreateStruct()
     * @depends testCreateContentType
     */
    public function testCreateContentTypeStructValues( array $data )
    {
        /** @var $groupCreateStruct \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct */
        $typeCreate = $data['expected'];
        /** @var $group \eZ\Publish\Core\Repository\Values\ContentType\ContentType */
        $contentType = $data['actual'];
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

        $this->assertContentTypeGroupsCorrect(
            $groups,
            $contentType->contentTypeGroups
        );

        $this->assertNotNull(
            $contentType->id
        );
    }

    /**
     * Asserts field definition creation
     *
     * Asserts that all field definitions defined through created structs in
     * $expectedDefinitionCreates have been correctly created in
     * $actualDefinitions.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct[] $expectedDefinitionCreates
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[] $actualDefinitions
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
     * create struct in $expectedDefinitionCreate.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct $expectedDefinitionCreate
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $actualDefinition
     * @return void
     */
    protected function assertFieldDefinitionsEqual( FieldDefinitionCreateStruct $expectedDefinitionCreate, FieldDefinition $actualDefinition )
    {
        foreach ( $expectedDefinitionCreate as $propertyName => $propertyValue )
        {
            $this->assertEquals(
                $propertyValue,
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
     * @todo remove $notImplemented as implemented
     */
    protected function assertContentTypeGroupsCorrect( array $expectedGroups, array $actualGroups )
    {
        $notImplemented = array(
            "names",
            "descriptions",
            "mainLanguageCode"
        );

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

        foreach ( $expectedGroups as $index => $expectedGroup )
        {
            $this->assertPropertiesCorrect(
                array(
                    "names"            => $expectedGroup->names,
                    "descriptions"     => $expectedGroup->descriptions,
                    "id"               => $expectedGroup->id,
                    "identifier"       => $expectedGroup->identifier,
                    "creationDate"     => $expectedGroup->creationDate,
                    "modificationDate" => $expectedGroup->modificationDate,
                    "creatorId"        => $expectedGroup->creatorId,
                    "modifierId"       => $expectedGroup->modifierId,
                    "mainLanguageCode" => $expectedGroup->mainLanguageCode
                ),
                $actualGroups[$index],
                $notImplemented
            );
        }
    }

    /**
     * Test for the createContentTypeGroup() method.
     *
     * @group types
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     */
    public function testCreateContentTypeThrowsIllegalArgumentExceptionGroupsEmpty()
    {
        /* BEGIN: Use Case */
        $contentTypeService = $this->repository->getContentTypeService();

        $typeCreateStruct = $contentTypeService->newContentTypeCreateStruct(
            'new-type'
        );

        $type = $contentTypeService->createContentType( $typeCreateStruct, array() );
        /* END: Use Case */
    }

    /**
     * Test for the createContentTypeGroup() method.
     *
     * @group types
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     */
    public function testCreateContentTypeThrowsIllegalArgumentExceptionContentTypeExistsWithIdentifier()
    {
        /* BEGIN: Use Case */
        $contentTypeService = $this->repository->getContentTypeService();

        $this->createContentType();
        $typeCreateStruct = $contentTypeService->newContentTypeCreateStruct(
            'new-type'
        );
        $typeCreateStruct->remoteId             = "other-remoteid";
        $typeCreateStruct->creatorId            = 23;
        $typeCreateStruct->creationDate         = new \DateTime();
        $typeCreateStruct->mainLanguageCode     = 'eng-GB';
        $typeCreateStruct->names                = array('eng-US' => 'A name.');
        $typeCreateStruct->descriptions         = array('eng-US' => 'A description.');

        $type = $contentTypeService->createContentType(
            $typeCreateStruct,
            array(
                // "Content" group
                $contentTypeService->loadContentTypeGroup( 1 )
            )
        );
        /* END: Use Case */
    }

    /**
     * Test for the createContentTypeGroup() method.
     *
     * @group types
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::createContentTypeGroup()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\IllegalArgumentException
     */
    public function testCreateContentTypeThrowsIllegalArgumentExceptionContentTypeExistsWithRemoteId()
    {
        /* BEGIN: Use Case */
        $contentTypeService = $this->repository->getContentTypeService();

        $this->createContentType();
        $typeCreateStruct = $contentTypeService->newContentTypeCreateStruct(
            'other-type'
        );
        $typeCreateStruct->remoteId             = "new-remoteid";
        $typeCreateStruct->creatorId            = 23;
        $typeCreateStruct->creationDate         = new \DateTime();
        $typeCreateStruct->mainLanguageCode     = 'eng-GB';
        $typeCreateStruct->names                = array('eng-US' => 'A name.');
        $typeCreateStruct->descriptions         = array('eng-US' => 'A description.');

        $type = $contentTypeService->createContentType(
            $typeCreateStruct,
            array(
                // "Content" group
                $contentTypeService->loadContentTypeGroup( 1 )
            )
        );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentType() method.
     *
     * @group types
     * @return array
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentType()
     */
    public function testLoadContentType()
    {
        $storedContentType = $this->createContentType();

        /* BEGIN: Use Case */
        $contentTypeService = $this->repository->getContentTypeService();

        $loadedContentType = $contentTypeService->loadContentType(
            $storedContentType->id
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\ContentType\ContentType',
            $loadedContentType
        );

        return array(
            'expected' => $storedContentType,
            'actual'   => $loadedContentType,
        );
    }

    /**
     * Test for the loadContentType() method.
     *
     * @group current
     * @group types
     * @param array $data
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentType()
     * @return void
     * @depends testLoadContentType
     */
    public function testLoadContentTypeValues( array $data )
    {
        /** @var $storedContentType \eZ\Publish\Core\Repository\Values\ContentType\ContentType */
        $storedContentType = $data['expected'];
        /** @var $loadedContentType \eZ\Publish\Core\Repository\Values\ContentType\ContentType */
        $loadedContentType = $data['actual'];

        $this->assertPropertiesCorrect(
            array(
                // Virtual properties
                "names"                  => $storedContentType->names,
                "descriptions"           => $storedContentType->descriptions,
                "contentTypeGroups"      => $storedContentType->contentTypeGroups,
                "fieldDefinitions"       => $storedContentType->fieldDefinitions,
                // Standard properties
                "id"                     => $storedContentType->id,
                "status"                 => $storedContentType->status,
                "identifier"             => $storedContentType->identifier,
                "creationDate"           => $storedContentType->creationDate,
                "modificationDate"       => $storedContentType->modificationDate,
                "creatorId"              => $storedContentType->creatorId,
                "modifierId"             => $storedContentType->modifierId,
                "remoteId"               => $storedContentType->remoteId,
                "urlAliasSchema"         => $storedContentType->urlAliasSchema,
                "nameSchema"             => $storedContentType->nameSchema,
                "isContainer"            => $storedContentType->isContainer,
                "mainLanguageCode"       => $storedContentType->mainLanguageCode,
                "defaultAlwaysAvailable" => $storedContentType->defaultAlwaysAvailable,
                "defaultSortField"       => $storedContentType->defaultSortField,
                "defaultSortOrder"       => $storedContentType->defaultSortOrder
            ),
            $loadedContentType
        );
    }

    /**
     * Test for the loadContentType() method.
     *
     * @group types
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentType()
     */
    public function testLoadContentTypeThrowsInvalidArgumentValue()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::loadContentType() is not implemented." );
    }

    /**
     * Test for the loadContentType() method.
     *
     * @group types
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentType()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadContentTypeThrowsNotFoundException()
    {
        /* BEGIN: Use Case */
        $contentTypeService = $this->repository->getContentTypeService();

        $loadedContentType = $contentTypeService->loadContentType(
            PHP_INT_MAX
        );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentTypeByIdentifier() method.
     *
     * @group types
     * @return array
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeByIdentifier()
     */
    public function testLoadContentTypeByIdentifier()
    {
        $storedContentType = $this->createContentType();

        /* BEGIN: Use Case */
        $contentTypeService = $this->repository->getContentTypeService();

        $loadedContentType = $contentTypeService->loadContentTypeByIdentifier(
            $storedContentType->identifier
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\ContentType\ContentType',
            $loadedContentType
        );

        return array(
            'expected' => $storedContentType,
            'actual'   => $loadedContentType,
        );
    }

    /**
     * Test for the loadContentTypeByIdentifier() method.
     *
     * @group types
     * @param array $data
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeByIdentifier()
     * @depends testLoadContentTypeByIdentifier
     */
    public function testLoadContentTypeByIdentifierStructValues( array $data )
    {
        $this->assertEquals(
            $data['expected'],
            $data['actual']
        );
    }

    /**
     * Test for the loadContentTypeByIdentifier() method.
     *
     * @group types
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeByIdentifier()
     */
    public function testLoadContentTypeByIdentifierThrowsInvalidArgumentValue()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::loadContentTypeByIdentifier() is not implemented." );
    }

    /**
     * Test for the loadContentTypeByIdentifier() method.
     *
     * @group types
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeByIdentifier()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadContentTypeByIdentifierThrowsNotFoundException()
    {
        /* BEGIN: Use Case */
        $contentTypeService = $this->repository->getContentTypeService();

        $loadedContentType = $contentTypeService->loadContentTypeByIdentifier(
            "non-existing-identifier"
        );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentTypeByRemoteId() method.
     *
     * @group types
     * @return array
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeByRemoteId()
     */
    public function testLoadContentTypeByRemoteId()
    {
        $storedContentType = $this->createContentType();

        /* BEGIN: Use Case */
        $contentTypeService = $this->repository->getContentTypeService();

        $loadedContentType = $contentTypeService->loadContentTypeByRemoteId(
            $storedContentType->remoteId
        );

        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\ContentType\ContentType',
            $loadedContentType
        );

        return array(
            'expected' => $storedContentType,
            'actual'   => $loadedContentType,
        );
    }

    /**
     * Test for the loadContentTypeByRemoteId() method.
     *
     * @group types
     * @param array $data
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeByRemoteId()
     * @depends testLoadContentTypeByRemoteId
     * @todo add additional props
     */
    public function testLoadContentTypeByRemoteIdStructValues( array $data )
    {
        $this->assertEquals(
            $data['expected'],
            $data['actual']
        );
    }

    /**
     * Test for the loadContentTypeByRemoteId() method.
     *
     * @group types
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeByRemoteId()
     */
    public function testLoadContentTypeByRemoteIdThrowsInvalidArgumentValue()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::loadContentTypeByIdentifier() is not implemented." );
    }

    /**
     * Test for the loadContentTypeByRemoteId() method.
     *
     * @group types
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeByRemoteId()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadContentTypeByRemoteIdThrowsNotFoundException()
    {
        /* BEGIN: Use Case */
        $contentTypeService = $this->repository->getContentTypeService();

        $loadedContentType = $contentTypeService->loadContentTypeByRemoteId(
            "non-existing-remoteid"
        );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentTypeDraft() method.
     *
     * @group types
     * @return array
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeDraft()
     */
    public function testLoadContentTypeDraft()
    {
        $storedContentTypeDraft = $this->createContentTypeDraft();

        /* BEGIN: Use Case */
        $contentTypeService = $this->repository->getContentTypeService();

        $loadedContentTypeDraft = $contentTypeService->loadContentTypeDraft(
            $storedContentTypeDraft->id
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft',
            $loadedContentTypeDraft
        );

        return array(
            'expected' => $storedContentTypeDraft,
            'actual'   => $loadedContentTypeDraft,
        );
    }

    /**
     * Test for the loadContentTypeDraft() method.
     *
     * @group types
     * @param array $data
     * @return array
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeDraft()
     * @depends testLoadContentTypeDraft
     * @todo add additional properties
     */
    public function testLoadContentTypeDraftValues( array $data )
    {
        /** @var $storedContentType \eZ\Publish\Core\Repository\Values\ContentType\ContentType */
        $storedContentType = $data['expected'];
        /** @var $loadedContentType \eZ\Publish\Core\Repository\Values\ContentType\ContentType */
        $loadedContentType = $data['actual'];

        $this->assertPropertiesCorrect(
            array(
                // Virtual properties
                "names"                  => $storedContentType->names,
                "descriptions"           => $storedContentType->descriptions,
                "contentTypeGroups"      => $storedContentType->contentTypeGroups,
                "fieldDefinitions"       => $storedContentType->fieldDefinitions,
                // Standard properties
                "id"                     => $storedContentType->id,
                "status"                 => $storedContentType->status,
                "identifier"             => $storedContentType->identifier,
                "creationDate"           => $storedContentType->creationDate,
                "modificationDate"       => $storedContentType->modificationDate,
                "creatorId"              => $storedContentType->creatorId,
                "modifierId"             => $storedContentType->modifierId,
                "remoteId"               => $storedContentType->remoteId,
                "urlAliasSchema"         => $storedContentType->urlAliasSchema,
                "nameSchema"             => $storedContentType->nameSchema,
                "isContainer"            => $storedContentType->isContainer,
                "mainLanguageCode"       => $storedContentType->mainLanguageCode,
                "defaultAlwaysAvailable" => $storedContentType->defaultAlwaysAvailable,
                "defaultSortField"       => $storedContentType->defaultSortField,
                "defaultSortOrder"       => $storedContentType->defaultSortOrder
            ),
            $loadedContentType
        );
    }

    /**
     * Test for the loadContentTypeDraft() method.
     *
     * @group types
     * @return array
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeDraft()
     */
    public function testLoadContentTypeDraftThrowsInvalidArgumentValue()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::loadContentTypeByIdentifier() is not implemented." );
    }

    /**
     * Test for the loadContentTypeDraft() method.
     *
     * @group types
     * @return array
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypeDraft()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testLoadContentTypeDraftThrowsNotFoundException()
    {
        /* BEGIN: Use Case */
        $contentTypeService = $this->repository->getContentTypeService();

        $loadedContentType = $contentTypeService->loadContentTypeDraft(
            PHP_INT_MAX
        );
        /* END: Use Case */
    }

    /**
     * Test for the loadContentTypes() method.
     *
     * @group types
     * @return array
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypes()
     */
    public function testLoadContentTypes()
    {
        $contentTypeService = $this->repository->getContentTypeService();

        $groupCreate = $contentTypeService->newContentTypeGroupCreateStruct(
            'test-group-1'
        );
        $groupCreate->creatorId        = 23;
        $groupCreate->creationDate     = new \DateTime();
        $groupCreate->mainLanguageCode = 'de_DE';
        $groupCreate->names            = array( 'en_US' => 'A name.' );
        $groupCreate->descriptions     = array( 'en_US' => 'A description.' );
        $group = $contentTypeService->createContentTypeGroup( $groupCreate );

        $typeCreateStruct = $contentTypeService->newContentTypeCreateStruct(
            'test-type-1'
        );
        $typeCreateStruct->names = array(
            'eng-US' => 'American type title',
            'eng-GB' => 'British type title'
        );
        $typeCreateStruct->descriptions = array(
            'eng-US' => 'American type description',
            'eng-GB' => 'British type description'
        );
        $typeCreateStruct->remoteId             = "test-remoteid-1";
        $typeCreateStruct->creatorId            = 23;
        $typeCreateStruct->creationDate         = new \DateTime();
        $typeCreateStruct->mainLanguageCode     = 'eng-GB';
        $typeCreateStruct->nameSchema           = "<name>";
        $typeCreateStruct->urlAliasSchema       = "<name>";

        $titleFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'title',
            'ezstring'
        );
        $titleFieldCreate->names = array(
            'eng-US' => 'American title field name',
            'eng-GB' => 'British title field name',
        );
        $titleFieldCreate->descriptions = array(
            'eng-US' => 'American title field description',
            'eng-GB' => 'British title field description',
        );
        $titleFieldCreate->fieldGroup      = 'blog-content';
        $titleFieldCreate->position        = 1;
        $titleFieldCreate->isTranslatable  = true;
        $titleFieldCreate->isRequired      = true;
        $titleFieldCreate->isInfoCollector = false;
        $titleFieldCreate->isSearchable    = true;
        $titleFieldCreate->defaultValue    = new \eZ\Publish\SPI\Persistence\Content\FieldValue(
            array(
                "data" => new \ezp\Content\FieldType\TextLine\Value( 'New text line' )
            )
        );
        $titleFieldCreate->validators = array(
            // @todo
        );
        $titleFieldCreate->fieldSettings = array(
            // @todo
        );
        $typeCreateStruct->addFieldDefinition( $titleFieldCreate );

        $type1 = $contentTypeService->createContentType(
            $typeCreateStruct,
            array( $group )
        );
        $contentTypeService->publishContentTypeDraft( $type1 );

        $typeCreateStruct->identifier = "test-type-2";
        $typeCreateStruct->remoteId   = "test-remoteid-2";
        $type2 = $contentTypeService->createContentType(
            $typeCreateStruct,
            array( $group )
        );
        $contentTypeService->publishContentTypeDraft( $type2 );

        /* BEGIN: Use Case */
        $loadedTypes = $contentTypeService->loadContentTypes( $group );
        /* END: Use Case */

        $this->assertInternalType(
            'array',
            $loadedTypes
        );

        return $loadedTypes;
    }

    /**
     * Test for the loadContentTypeGroups() method.
     *
     * @group types
     * @param array $types
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::loadContentTypes()
     * @depends testLoadContentTypes
     */
    public function testLoadContentTypesIdentifiers( array $types )
    {
        $expectedIdentifiers = array( 'test-type-1' => true, 'test-type-2' => true );

        $this->assertEquals( count( $expectedIdentifiers ), count( $types ) );

        $actualIdentifiers  = array( 'test-type-1' => false, 'test-type-2' => false );

        foreach ( $types as $type )
        {
            $actualIdentifiers[$type->identifier] = true;
        }

        $this->assertEquals(
            $expectedIdentifiers,
            $actualIdentifiers,
            'Identifier mismatch in loaded types.'
        );
    }

    public function testCreateContentTypeDraft()
    {

    }

    public function testCreateContentTypeDraftValues()
    {

    }

    public function testCreateContentTypeDraftThrowsUnauthorizedException()
    {

    }

    public function testCreateContentTypeDraftThrowsBadStateException()
    {

    }

    public function testUpdateContentTypeDraft()
    {

    }

    public function testUpdateContentTypeDraftStructValues()
    {

    }

    public function testUpdateContentTypeDraftThrowsUnauthorizedException()
    {

    }

    public function testUpdateContentTypeDraftThrowsIllegalArgumentException()
    {

    }

    public function testDeleteContentType()
    {

    }

    public function testDeleteContentTypeThrowsBadStateException()
    {

    }

    public function testDeleteContentTypeThrowsUnauthorizedException()
    {

    }

    public function testCopyContentType()
    {

    }

    public function testCopyContentTypeValues()
    {

    }

    public function testCopyContentTypeThrowsUnauthorizedException()
    {

    }

    public function testAssignContentTypeGroup()
    {

    }

    public function testAssignContentTypeGroupThrowsUnauthorizedException()
    {

    }

    public function testAssignContentTypeGroupThrowsIllegalArgumentException()
    {

    }

    public function testUnassignContentTypeGroup()
    {

    }

    public function testUnassignContentTypeGroupThrowsUnauthorizedException()
    {

    }

    public function testUnassignContentTypeGroupThrowsIllegalArgumentException()
    {

    }

    public function testUnassignContentTypeGroupThrowsBadStateException()
    {

    }

    public function testAddFieldDefinition()
    {

    }

    public function testAddFieldDefinitionThrowsIllegalArgumentException()
    {

    }

    public function testAddFieldDefinitionThrowsUnauthorizedException()
    {

    }

    public function testRemoveFieldDefinition()
    {

    }

    public function testRemoveFieldDefinitionThrowsIllegalArgumentException()
    {

    }

    public function testRemoveFieldDefinitionThrowsUnauthorizedException()
    {

    }

    public function testUpdateFieldDefinition()
    {

    }

    public function testUpdateFieldDefinitionThrowsInvalidArgumentException()
    {

    }

    public function testUpdateFieldDefinitionThrowsUnauthorizedException()
    {

    }

    public function testUpdateFieldDefinitionThrowsIllegalArgumentException()
    {

    }

    public function testPublishContentTypeDraft()
    {

    }

    public function testPublishContentTypeDraftThrowsBadStateException()
    {

    }

    public function testPublishContentTypeDraftThrowsUnauthorizedException()
    {

    }

    /**
     * Test for the newContentTypeCreateStruct() method.
     *
     * @group types
     * @see \eZ\Publish\Core\Repository\ContentTypeService::newContentTypeCreateStruct()
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct
     */
    public function testNewContentTypeCreateStruct()
    {
        /* BEGIN: Use Case */
        $contentTypeService = $this->repository->getContentTypeService();

        $contentTypeCreateStruct = $contentTypeService->newContentTypeCreateStruct(
            'new-type'
        );
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct',
            $contentTypeCreateStruct
        );
        return $contentTypeCreateStruct;
    }

    /**
     * Test for the newContentTypeCreateStruct() method.
     *
     * @group types
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct $contentTypeCreateStruct
     * @see \eZ\Publish\Core\Repository\ContentTypeService::newContentTypeCreateStruct()
     * @depends testNewContentTypeCreateStruct
     * @return void
     */
    public function testNewContentTypeCreateStructValues( $contentTypeCreateStruct )
    {
        $this->assertPropertiesCorrect(
            array(
                'identifier'             => 'new-type',
                "mainLanguageCode"       => null,
                "remoteId"               => null,
                "urlAliasSchema"         => null,
                "nameSchema"             => null,
                "isContainer"            => false,
                "defaultSortField"       => Location::SORT_FIELD_PUBLISHED,
                "defaultSortOrder"       => Location::SORT_ORDER_DESC,
                "defaultAlwaysAvailable" => true,
                "names"                  => null,
                "descriptions"           => null,
                "creatorId"              => null,
                "creationDate"           => null,
                "fieldDefinitions"       => array()
            ),
            $contentTypeCreateStruct
        );
    }

    /**
     * Test for the deleteContentTypeGroup() method.
     *
     * @group types
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::newContentTypeCreateStruct()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentValue
     */
    public function testNewContentTypeCreateStructThrowsInvalidArgumentValue()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::newContentTypeCreateStruct() is not implemented." );
    }

    /**
     * Test for the newContentTypeUpdateStruct() method.
     *
     * @group types
     * @see \eZ\Publish\Core\Repository\ContentTypeService::newContentTypeUpdateStruct()
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct
     */
    public function testNewContentTypeUpdateStruct()
    {
        /* BEGIN: Use Case */
        $contentTypeService = $this->repository->getContentTypeService();

        $contentTypeUpdateStruct = $contentTypeService->newContentTypeUpdateStruct();
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct',
            $contentTypeUpdateStruct
        );
        return $contentTypeUpdateStruct;
    }

    /**
     * Test for the newContentTypeUpdateStruct() method.
     *
     * @group types
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct $contentTypeUpdateStruct
     * @see \eZ\Publish\Core\Repository\ContentTypeService::newContentTypeUpdateStruct()
     * @depends testNewContentTypeUpdateStruct
     * @return void
     */
    public function testNewContentTypeUpdateStructValues( $contentTypeUpdateStruct )
    {
        $this->assertPropertiesCorrect(
            array(
                "identifier"              => null,
                "remoteId"                => null,
                "urlAliasSchema"          => null,
                "nameSchema"              => null,
                "isContainer"             => null,
                "mainLanguageCode"        => null,
                "defaultSortField"        => null,
                "defaultSortOrder"        => null,
                "defaultAlwaysAvailable"  => null,
                "modifierId"              => null,
                "modificationDate"        => null,
                "names"                   => null,
                "descriptions"            => null
            ),
            $contentTypeUpdateStruct
        );
    }

    /**
     * Test for the newContentTypeCreateStruct() method.
     *
     * @group types
     * @see \eZ\Publish\Core\Repository\ContentTypeService::newFieldDefinitionCreateStruct()
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct
     */
    public function testNewFieldDefinitionCreateStruct()
    {
        /* BEGIN: Use Case */
        $contentTypeService = $this->repository->getContentTypeService();

        $fieldDefinitionCreateStruct = $contentTypeService->newFieldDefinitionCreateStruct(
            "new-identifier",
            "new-fieldtype-identifier"
        );
        $fieldDefinitionCreateStruct->fieldGroup = "";
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct',
            $fieldDefinitionCreateStruct
        );
        return $fieldDefinitionCreateStruct;
    }

    /**
     * Test for the newContentTypeCreateStruct() method.
     *
     * @group types
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct $fieldDefinitionCreateStruct
     * @see \eZ\Publish\Core\Repository\ContentTypeService::newFieldDefinitionCreateStruct()
     * @depends testNewFieldDefinitionCreateStruct
     * @return void
     */
    public function testNewFieldDefinitionCreateStructValues( $fieldDefinitionCreateStruct )
    {
        $this->assertPropertiesCorrect(
            array(
                "fieldTypeIdentifier" => "new-fieldtype-identifier",
                "identifier"          => "new-identifier",
                "names"               => null,
                "descriptions"        => null,
                "fieldGroup"          => "",
                "position"            => null,
                "isTranslatable"      => null,
                "isRequired"          => null,
                "isInfoCollector"     => null,
                "validators"          => null,
                "fieldSettings"       => null,
                "defaultValue"        => null,
                "isSearchable"        => null
            ),
            $fieldDefinitionCreateStruct
        );
    }

    /**
     * Test for the deleteContentTypeGroup() method.
     *
     * @group types
     * @return void
     * @see \eZ\Publish\API\Repository\ContentTypeService::newFieldDefinitionCreateStruct()
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentValue
     */
    public function testNewFieldDefinitionCreateStructThrowsInvalidArgumentValue()
    {
        $this->markTestIncomplete( "Test for ContentTypeService::newContentTypeCreateStruct() is not implemented." );
    }

    /**
     * Test for the newFieldDefinitionUpdateStruct() method.
     *
     * @group types
     * @see \eZ\Publish\Core\Repository\ContentTypeService::newFieldDefinitionUpdateStruct()
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct
     */
    public function testNewFieldDefinitionUpdateStruct()
    {
        /* BEGIN: Use Case */
        $contentTypeService = $this->repository->getContentTypeService();

        $fieldDefinitionCreateStruct = $contentTypeService->newFieldDefinitionUpdateStruct();
        /* END: Use Case */

        $this->assertInstanceOf(
            '\eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct',
            $fieldDefinitionCreateStruct
        );
    }
}
