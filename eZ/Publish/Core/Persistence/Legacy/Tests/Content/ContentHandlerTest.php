<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\ContentHandlerTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content;
use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase,
    eZ\Publish\SPI\Persistence\Content,
    eZ\Publish\SPI\Persistence\Content\ContentInfo,
    eZ\Publish\SPI\Persistence\Content\Field,
    eZ\Publish\SPI\Persistence\Content\FieldValue,
    eZ\Publish\SPI\Persistence\Content\Version,
    eZ\Publish\SPI\Persistence\Content\VersionInfo,
    eZ\Publish\SPI\Persistence\Content\RestrictedVersion,
    eZ\Publish\SPI\Persistence\Content\CreateStruct,
    eZ\Publish\SPI\Persistence\Content\UpdateStruct,
    eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct,
    eZ\Publish\SPI\Persistence\Content\Location\CreateStruct as LocationCreateStruct,
    eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue,
    eZ\Publish\Core\Persistence\Legacy\Content\Mapper,
    eZ\Publish\Core\Persistence\Legacy\Content\Gateway,
    eZ\Publish\Core\Persistence\Legacy\Content\Location,
    eZ\Publish\Core\Persistence\Legacy\Content\Type,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageRegistry,
    eZ\Publish\Core\Persistence\Legacy\Content\Handler;

/**
 * Test case for Content Handler
 */
class ContentHandlerTest extends TestCase
{
    /**
     * Content handler to test
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Handler
     */
    protected $contentHandler;

    /**
     * Gateway mock
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Gateway
     */
    protected $gatewayMock;

    /**
     * Location gateway mock
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway
     */
    protected $locationGatewayMock;

    /**
     * Type gateway mock
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway
     */
    protected $typeGatewayMock;

    /**
     * Mapper mock
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Mapper
     */
    protected $mapperMock;

    /**
     * Field handler mock
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler
     */
    protected $fieldHandlerMock;

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Handler::__construct
     */
    public function testCtor()
    {
        $handler = $this->getContentHandler();

        $this->assertAttributeSame(
            $this->getGatewayMock(),
            'contentGateway',
            $handler
        );
        $this->assertAttributeSame(
            $this->getMapperMock(),
            'mapper',
            $handler
        );
        $this->assertAttributeSame(
            $this->getFieldHandlerMock(),
            'fieldHandler',
            $handler
        );
        // @TODO Assert missing ptoperties
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Handler::create
     * @todo Current method way to complex to test, refactor!
     */
    public function testCreate()
    {
        $handler = $this->getContentHandler();

        $mapperMock         = $this->getMapperMock();
        $gatewayMock        = $this->getGatewayMock();
        $fieldHandlerMock   = $this->getFieldHandlerMock();
        $locationMock       = $this->getLocationGatewayMock();

        $mapperMock->expects( $this->once() )
            ->method( 'createContentFromCreateStruct' )
            ->with(
                $this->isInstanceOf(
                    'eZ\\Publish\\SPI\\Persistence\\Content\\CreateStruct'
                )
            )->will(
                $this->returnValue(
                    new Content(
                        array(
                            'contentInfo' => new ContentInfo
                        )
                    )
                )
            );
        $mapperMock->expects( $this->once() )
            ->method( 'createVersionInfoForContent' )
            ->with(
                $this->isInstanceOf(
                    'eZ\\Publish\\SPI\\Persistence\\Content'
                )
            )->will(
                $this->returnValue( new VersionInfo )
            );
        $mapperMock->expects( $this->once() )
            ->method( 'createLocationCreateStruct' )
            ->with(
                $this->isInstanceOf(
                    'eZ\\Publish\\SPI\\Persistence\\Content'
                )
            )->will(
                $this->returnValue( new \eZ\Publish\SPI\Persistence\Content\Location\CreateStruct() )
            );

        $gatewayMock->expects( $this->once() )
            ->method( 'insertContentObject' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\CreateStruct' )
            )->will( $this->returnValue( 23 ) );

        $gatewayMock->expects( $this->once() )
            ->method( 'insertVersion' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\VersionInfo' ),
                $this->isType( 'array' )
            )->will( $this->returnValue( 1 ) );

        $fieldHandlerMock->expects( $this->once() )
            ->method( 'createNewFields' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content' ) );

        $locationMock->expects( $this->once() )
            ->method( 'createNodeAssignment' )
            ->with(
                $this->isInstanceOf(
                    'eZ\\Publish\\SPI\\Persistence\\Content\\Location\\CreateStruct'
                ),
                $this->equalTo( 42 ),
                $this->equalTo( 3 ) // Location\Gateway::NODE_ASSIGNMENT_OP_CODE_CREATE
            );

        $res = $handler->create( $this->getCreateStructFixture() );

        // @TODO Make subsequent tests

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\Content',
            $res,
            'Content not created'
        );
        $this->assertEquals(
            23,
            $res->contentInfo->contentId,
            'Content ID not set correctly'
        );
        $this->assertInstanceOf(
            '\\eZ\\Publish\\SPI\\Persistence\\Content\\VersionInfo',
            $res->versionInfo,
            'Version infos not created'
        );
        $this->assertEquals(
            1,
            $res->versionInfo->id,
            'Version ID not set correctly'
        );
        $this->assertEquals(
            2,
            count( $res->fields ),
            'Fields not set correctly in version'
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Handler::publish
     */
    public function testPublish()
    {
        $handler = $this->getPartlyMockedHandler( array( 'setStatus' ) );

        $gatewayMock = $this->getGatewayMock();
        $mapperMock = $this->getMapperMock();
        $locationMock = $this->getLocationGatewayMock();
        $fieldHandlerMock = $this->getFieldHandlerMock();
        $metadataUpdateStruct = new MetadataUpdateStruct();

        $gatewayMock->expects( $this->once() )
            ->method( 'load' )
            ->with(
            $this->equalTo( 23 ),
            $this->equalTo( 2 ),
            $this->equalTo( null )
        )->will(
            $this->returnValue( array( 42 ) )
        );

        $mapperMock->expects( $this->once() )
            ->method( 'extractContentFromRows' )
            ->with( $this->equalTo( array( 42 ) ) )
            ->will( $this->returnValue( array( $this->getContentFixtureForDraft() ) ) );

        $fieldHandlerMock->expects( $this->once() )
            ->method( 'loadExternalFieldData' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content' ) );

        $gatewayMock
            ->expects( $this->once() )
            ->method( 'updateContent' )
            ->with( 23, $metadataUpdateStruct );

        $locationMock
            ->expects( $this->once() )
            ->method( 'createLocationsFromNodeAssignments' )
            ->with( 23, 2 );

        $handler
            ->expects( $this->once() )
            ->method( 'setStatus' )
            ->with( 23, VersionInfo::STATUS_PUBLISHED, 2 );

        $handler->publish( 23, 2, $metadataUpdateStruct );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Handler::createDraftFromVersion
     */
    public function testCreateDraftFromVersion()
    {
        $handler = $this->getPartlyMockedHandler( array( 'load' ) );

        $mapperMock = $this->getMapperMock();
        $gatewayMock = $this->getGatewayMock();
        $fieldHandlerMock = $this->getFieldHandlerMock();

        $handler->expects( $this->once() )
            ->method( 'load' )
            ->with( 23, 2 )
            ->will( $this->returnValue( $this->getContentFixtureForDraft() ) );

        $mapperMock->expects( $this->once() )
            ->method( 'createVersionInfoForContent' )
            ->with(
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content' ),
                $this->equalTo( 3 )
            )->will( $this->returnValue( new VersionInfo ) );

        $gatewayMock->expects( $this->once() )
            ->method( 'insertVersion' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\VersionInfo' ) )
            ->will( $this->returnValue( 42 ) );

        $fieldHandlerMock->expects( $this->once() )
            ->method( 'createNewFields' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content' ) );

        $result = $handler->createDraftFromVersion( 23, 2 );

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\Content\\VersionInfo',
            $result
        );
        $this->assertEquals(
            42,
            $result->id
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Handler::load
     */
    public function testLoad()
    {
        $handler = $this->getContentHandler();

        $gatewayMock = $this->getGatewayMock();
        $mapperMock = $this->getMapperMock();
        $fieldHandlerMock = $this->getFieldHandlerMock();

        $gatewayMock->expects( $this->once() )
            ->method( 'load' )
            ->with(
                $this->equalTo( 23 ),
                $this->equalTo( 2 ),
                $this->equalTo( array( 'eng-GB' ) )
            )->will(
                $this->returnValue( array( 42 ) )
            );

        $mapperMock->expects( $this->once() )
            ->method( 'extractContentFromRows' )
            ->with( $this->equalTo( array( 42 ) ) )
            ->will( $this->returnValue( array( $this->getContentFixtureForDraft() ) ) );

        $fieldHandlerMock->expects( $this->once() )
            ->method( 'loadExternalFieldData' )
            ->with( $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content' ) );

        $result = $handler->load( 23, 2, array( 'eng-GB' ) );

        $this->assertEquals(
            $result,
            $this->getContentFixtureForDraft()
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Handler::load
     * @expectedException eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testLoadErrorNotFound()
    {
        $handler = $this->getContentHandler();

        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects( $this->once() )
            ->method( 'load' )
            ->will(
                $this->returnValue( array() )
            );

        $result = $handler->load( 23, 2, array( 'eng-GB' ) );
    }

    /**
     * Returns a Content for {@link testCreateDraftFromVersion()}
     *
     * @return \eZ\Publish\SPI\Persistence\Content
     */
    protected function getContentFixtureForDraft()
    {
        $content = new Content;
        $content->versionInfo = new VersionInfo;
        $content->versionInfo->versionNo = 2;

        $content->contentInfo = new ContentInfo;

        $field = new Field;
        $field->versionNo = 2;

        $content->fields = array( $field );

        return $content;
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Handler::updateContent
     */
    public function testUpdateContent()
    {
        $handler = $this->getPartlyMockedHandler( array( 'load', 'loadContentInfo' ) );

        $gatewayMock = $this->getGatewayMock();
        $fieldHandlerMock = $this->getFieldHandlerMock();

        $gatewayMock->expects( $this->once() )
            ->method( 'updateContent' )
            ->with( 14, $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\MetadataUpdateStruct' ) );
        $gatewayMock->expects( $this->once() )
            ->method( 'updateVersion' )
            ->with( 14, 4, $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\UpdateStruct' ) );

        $fieldHandlerMock->expects( $this->once() )
            ->method( 'updateFields' )
            ->with(
                14,
                4,
                $this->isInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\UpdateStruct' )
            );

        $handler->expects( $this->at( 0 ) )
            ->method( 'load' )
            ->with( 14, 4 );

        $handler->expects( $this->at( 1 ) )
            ->method( 'loadContentInfo' )
            ->with( 14 );

        $resultContent = $handler->updateContent(
            14, // ContentId
            4, // VersionNo
            new UpdateStruct(
                array(
                    'creatorId' => 14,
                    'modificationDate' => time(),
                    'initialLanguageId' => 2,
                    'fields' => array(
                        new Field(
                            array(
                                'id' => 23,
                                'fieldDefinitionId' => 42,
                                'type' => 'some-type',
                                'value' => new FieldValue(),
                            )
                        ),
                        new Field(
                            array(
                                'id' => 23,
                                'fieldDefinitionId' => 43,
                                'type' => 'some-type',
                                'value' => new FieldValue(),
                            )
                        ),
                    )
                )
            )
        );

        $resultContentInfo = $handler->updateMetadata(
            14, // ContentId
            new MetadataUpdateStruct(
                array(
                    'ownerId' => 14,
                    'name' => 'Some name',
                    'modificationDate' => time(),
                    'alwaysAvailable' => true
                )
            )
        );
    }

    /**
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Handler::updateMetadata
     */
    public function testUpdateMetadata()
    {
        $handler = $this->getPartlyMockedHandler( array( 'load', 'loadContentInfo' ) );

        $gatewayMock = $this->getGatewayMock();
        $fieldHandlerMock = $this->getFieldHandlerMock();
        $updateStruct = new MetadataUpdateStruct(
                array(
                    'ownerId' => 14,
                    'name' => 'Some name',
                    'modificationDate' => time(),
                    'alwaysAvailable' => true
                )
            );

        $gatewayMock->expects( $this->once() )
            ->method( 'updateContent' )
            ->with( 14, $updateStruct );

        $handler->expects( $this->once() )
            ->method( 'loadContentInfo' )
            ->with( 14 )
            ->will(
                $this->returnValue(
                    $this->getMock( 'eZ\\Publish\\SPI\\Persistence\\Content\\ContentInfo' )
                )
            );

        $resultContentInfo = $handler->updateMetadata(
            14, // ContentId
            $updateStruct
        );
        self::assertInstanceOf( 'eZ\\Publish\\SPI\\Persistence\\Content\\ContentInfo', $resultContentInfo );
    }

    /**
     * Returns a CreateStruct fixture.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\CreateStruct
     */
    public function getCreateStructFixture()
    {
        $struct = new CreateStruct();

        $firstField = new Field();
        $firstField->type = 'some-type';
        $firstField->value = new FieldValue();

        $secondField = clone $firstField;

        $struct->fields = array(
            $firstField, $secondField
        );

        $struct->locations = array(
            new LocationCreateStruct(
                array( 'parentId' => 42 )
            )
        );

        $struct->name = array(
            'eng-GB' => 'This is a test name'
        );

        return $struct;
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Handler::listVersions
     */
    public function testListVersions()
    {
        $handler = $this->getContentHandler();

        $gatewayMock = $this->getGatewayMock();
        $mapperMock = $this->getMapperMock();

        $gatewayMock->expects( $this->once() )
            ->method( 'listVersions' )
            ->with( $this->equalTo( 23 ) )
            ->will( $this->returnValue( array() ) );

        $mapperMock->expects( $this->once() )
            ->method( 'extractVersionListFromRows' )
            ->with( $this->equalTo( array() ) )
            ->will( $this->returnValue( array( new RestrictedVersion() ) ) );

        $res = $handler->listVersions( 23 );

        $this->assertEquals(
            array( new RestrictedVersion() ),
            $res
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Handler::deleteContent
     */
    public function testDeleteContent()
    {
        $handler = $this->getContentHandler();

        $gatewayMock = $this->getGatewayMock();
        $locationHandlerMock = $this->getLocationGatewayMock();
        $fieldHandlerMock  = $this->getFieldHandlerMock();

        $gatewayMock->expects( $this->once() )
            ->method( 'getAllLocationIds' )
            ->with( $this->equalTo( 23 ) )
            ->will( $this->returnValue( array( 42, 24 ) ) );
        $locationHandlerMock->expects( $this->exactly( 2 ) )
            ->method( 'removeSubtree' )
            ->with(
            $this->logicalOr(
                $this->equalTo( 42 ),
                $this->equalTo( 24 )
            )
        );

        $fieldHandlerMock->expects( $this->once() )
            ->method( 'deleteFields' )
            ->with( $this->equalTo( 23 ) );
        $gatewayMock->expects( $this->once() )
            ->method( 'deleteRelations' )
            ->with( $this->equalTo( 23 ) );
        $gatewayMock->expects( $this->once() )
            ->method( 'deleteVersions' )
            ->with( $this->equalTo( 23 ) );
        $gatewayMock->expects( $this->once() )
            ->method( 'deleteNames' )
            ->with( $this->equalTo( 23 ) );
        $gatewayMock->expects( $this->once() )
            ->method( 'deleteContent' )
            ->with( $this->equalTo( 23 ) );

        $handler->deleteContent( 23 );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Handler::deleteVersion
     */
    public function testDeleteVersion()
    {
        $handler = $this->getContentHandler();

        $gatewayMock = $this->getGatewayMock();
        $locationHandlerMock = $this->getLocationGatewayMock();
        $fieldHandlerMock  = $this->getFieldHandlerMock();

        $locationHandlerMock->expects( $this->once() )
            ->method( 'deleteNodeAssignment' )
            ->with(
                $this->equalTo( 225 ),
                $this->equalTo( 2 )
            );

        $fieldHandlerMock->expects( $this->once() )
            ->method( 'deleteFields' )
            ->with(
                $this->equalTo( 225 ),
                $this->equalTo( 2 )
            );
        $gatewayMock->expects( $this->once() )
            ->method( 'deleteRelations' )
            ->with(
                $this->equalTo( 225 ),
                $this->equalTo( 2 )
            );
        $gatewayMock->expects( $this->once() )
            ->method( 'deleteVersions' )
            ->with(
                $this->equalTo( 225 ),
                $this->equalTo( 2 )
            );
        $gatewayMock->expects( $this->once() )
            ->method( 'deleteNames' )
            ->with(
                $this->equalTo( 225 ),
                $this->equalTo( 2 )
            );

        $handler->deleteVersion( 225, 2 );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Handler::createCopy
     */
    public function testCreateCopy()
    {
        $handler = $this->getPartlyMockedHandler( array( 'create' ) );

        $gatewayMock        = $this->getGatewayMock();
        $mapperMock         = $this->getMapperMock();
        $fieldHandlerMock = $this->getFieldHandlerMock();

        $gatewayMock->expects( $this->once() )
            ->method( 'loadLatestPublishedData' )
            ->with( $this->equalTo( 23 ) )
            ->will( $this->returnValue( array( 0 => array() ) ) );

        $mapperMock->expects( $this->once() )
            ->method( 'extractContentFromRows' )
            ->with( $this->isType( 'array' ) )
            ->will( $this->returnValue( array( new Content() ) ) );

        $mapperMock->expects( $this->once() )
            ->method( 'createCreateStructFromContent' )
            ->with(
                $this->isInstanceOf(
                    'eZ\\Publish\\SPI\\Persistence\\Content'
                )
            )->will(
                $this->returnValue( new CreateStruct() )
            );

        $handler->expects( $this->once() )
            ->method( 'create' )
            ->with(
                $this->isInstanceOf(
                    'eZ\\Publish\\SPI\\Persistence\\Content\\CreateStruct'
                )
            )->will( $this->returnValue( new Content() ) );

        $result = $handler->createCopy( 23 );

        $this->assertInstanceOf(
            'eZ\\Publish\\SPI\\Persistence\\Content',
            $result
        );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Handler::createCopy
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testCreateCopyErrorNotFound()
    {
        $handler = $this->getPartlyMockedHandler( array( 'create' ) );

        $gatewayMock        = $this->getGatewayMock();
        $mapperMock         = $this->getMapperMock();
        $fieldHandlerMock = $this->getFieldHandlerMock();

        $gatewayMock->expects( $this->once() )
            ->method( 'loadLatestPublishedData' )
            ->with( $this->equalTo( 23 ) )
            ->will( $this->returnValue( array() ) );

        $result = $handler->createCopy( 23 );
    }

    /**
     * @return void
     * @covers eZ\Publish\Core\Persistence\Legacy\Content\Handler::setStatus
     */
    public function testSetStatus()
    {
        $handler = $this->getContentHandler();

        $mapperMock = $this->getMapperMock();
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects( $this->once() )
            ->method( 'setStatus' )
            ->with( 23, 5, 2 )
            ->will( $this->returnValue( true ) );

        $this->assertTrue(
            $handler->setStatus( 23, 2, 5 )
        );
    }

    /**
     * Returns the handler to test
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Handler
     */
    protected function getContentHandler()
    {
        if ( !isset( $this->contentHandler ) )
        {
            $this->contentHandler = new Handler(
                $this->getGatewayMock(),
                $this->getLocationGatewayMock(),
                $this->getMapperMock(),
                $this->getFieldHandlerMock()
            );
        }
        return $this->contentHandler;
    }

    /**
     * Returns the handler to test with $methods mocked
     *
     * @param string[] $methods
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Handler
     */
    protected function getPartlyMockedHandler( array $methods )
    {
        return $this->getMock(
            '\\eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Handler',
            $methods,
            array(
                $this->getGatewayMock(),
                $this->getLocationGatewayMock(),
                $this->getMapperMock(),
                $this->getFieldHandlerMock()
            )
        );
    }

    /**
     * Returns a FieldHandler mock.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler
     */
    protected function getFieldHandlerMock()
    {
        if ( !isset( $this->fieldHandlerMock ) )
        {
            $this->fieldHandlerMock = $this->getMock(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\FieldHandler',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->fieldHandlerMock;
    }

    /**
     * Returns a Mapper mock.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Mapper
     */
    protected function getMapperMock()
    {
        if ( !isset( $this->mapperMock ) )
        {
            $this->mapperMock = $this->getMock(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Mapper',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->mapperMock;
    }

    /**
     * Returns a Location Gateway mock
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway
     */
    protected function getLocationGatewayMock()
    {
        if ( !isset( $this->locationGatewayMock ) )
        {
            $this->locationGatewayMock = $this->getMock(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Location\\Gateway'
            );
        }
        return $this->locationGatewayMock;
    }

    /**
     * Returns a Content Type gateway mock
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway
     */
    protected function getTypeGatewayMock()
    {
        if ( !isset( $this->typeGatewayMock ) )
        {
            $this->typeGatewayMock = $this->getMock(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Type\\Gateway'
            );
        }
        return $this->typeGatewayMock;
    }

    /**
     * Returns a mock object for the Content Gateway.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Gateway
     */
    protected function getGatewayMock()
    {
        if ( !isset( $this->gatewayMock ) )
        {
            $this->gatewayMock = $this->getMockForAbstractClass(
                'eZ\\Publish\\Core\\Persistence\\Legacy\\Content\\Gateway'
            );
        }
        return $this->gatewayMock;
    }

    /**
     * Returns the test suite with all tests declared in this class.
     *
     * @return \PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        return new \PHPUnit_Framework_TestSuite( __CLASS__ );
    }
}
