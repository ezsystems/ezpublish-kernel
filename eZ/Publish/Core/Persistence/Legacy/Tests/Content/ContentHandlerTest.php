<?php

/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\Content\ContentHandlerTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Tests\Content;

use eZ\Publish\Core\Persistence\Legacy\Tests\TestCase;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\Type;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Relation;
use eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Location\CreateStruct as LocationCreateStruct;
use eZ\Publish\Core\Persistence\Legacy\Content\TreeHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Handler;
use eZ\Publish\API\Repository\Values\Content\Relation as RelationValue;
use eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct as RelationCreateStruct;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway as UrlAliasGateway;
use eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\SlugConverter;
use eZ\Publish\Core\Persistence\Legacy\Content\Gateway as ContentGateway;
use eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway as ContentTypeGateway;
use eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway as LocationGateway;
use eZ\Publish\Core\Persistence\Legacy\Content\Mapper;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler;
use eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler as ContentTypeHandler;

/**
 * Test case for Content Handler.
 */
class ContentHandlerTest extends TestCase
{
    /**
     * Content handler to test.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Handler
     */
    protected $contentHandler;

    /**
     * Gateway mock.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Gateway
     */
    protected $gatewayMock;

    /**
     * Location gateway mock.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway
     */
    protected $locationGatewayMock;

    /**
     * Type gateway mock.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway
     */
    protected $typeGatewayMock;

    /**
     * Mapper mock.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Mapper
     */
    protected $mapperMock;

    /**
     * Field handler mock.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler
     */
    protected $fieldHandlerMock;

    /**
     * Location handler mock.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\TreeHandler
     */
    protected $treeHandlerMock;

    /**
     * Slug converter mock.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\SlugConverter
     */
    protected $slugConverterMock;

    /**
     * Location handler mock.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway
     */
    protected $urlAliasGatewayMock;

    /**
     * ContentType handler mock.
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler
     */
    protected $contentTypeHandlerMock;

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Handler::__construct
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
        // @todo Assert missing properties
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Handler::create
     *
     * @todo Current method way to complex to test, refactor!
     */
    public function testCreate()
    {
        $handler = $this->getContentHandler();

        $mapperMock = $this->getMapperMock();
        $gatewayMock = $this->getGatewayMock();
        $fieldHandlerMock = $this->getFieldHandlerMock();
        $locationMock = $this->getLocationGatewayMock();
        $contentTypeHandlerMock = $this->getContentTypeHandlerMock();
        $contentTypeMock = $this->createMock(Type::class);
        $createStruct = $this->getCreateStructFixture();

        $contentTypeHandlerMock->expects($this->once())
            ->method('load')
            ->with($createStruct->typeId)
            ->will($this->returnValue($contentTypeMock));

        $mapperMock->expects($this->once())
            ->method('createVersionInfoFromCreateStruct')
            ->with(
                $this->isInstanceOf(
                    CreateStruct::class
                )
            )->will(
                $this->returnValue(
                    new VersionInfo(
                        array(
                            'names' => array(),
                            'contentInfo' => new ContentInfo(),
                        )
                    )
                )
            );

        $gatewayMock->expects($this->once())
            ->method('insertContentObject')
            ->with(
                $this->isInstanceOf(CreateStruct::class)
            )->will($this->returnValue(23));

        $gatewayMock->expects($this->once())
            ->method('insertVersion')
            ->with(
                $this->isInstanceOf(VersionInfo::class),
                $this->isType('array')
            )->will($this->returnValue(1));

        $fieldHandlerMock->expects($this->once())
            ->method('createNewFields')
            ->with(
                $this->isInstanceOf(Content::class),
                $this->isInstanceOf(Type::class)
            );

        $locationMock->expects($this->once())
            ->method('createNodeAssignment')
            ->with(
                $this->isInstanceOf(
                    LocationCreateStruct::class
                ),
                $this->equalTo(42),
                $this->equalTo(3) // Location\Gateway::NODE_ASSIGNMENT_OP_CODE_CREATE
            );

        $res = $handler->create($createStruct);

        // @todo Make subsequent tests

        $this->assertInstanceOf(
            Content::class,
            $res,
            'Content not created'
        );
        $this->assertEquals(
            23,
            $res->versionInfo->contentInfo->id,
            'Content ID not set correctly'
        );
        $this->assertInstanceOf(
            VersionInfo::class,
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
            count($res->fields),
            'Fields not set correctly in version'
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Handler::publish
     */
    public function testPublishFirstVersion()
    {
        $handler = $this->getPartlyMockedHandler(array('loadVersionInfo', 'setStatus'));

        $gatewayMock = $this->getGatewayMock();
        $mapperMock = $this->getMapperMock();
        $locationMock = $this->getLocationGatewayMock();
        $fieldHandlerMock = $this->getFieldHandlerMock();
        $metadataUpdateStruct = new MetadataUpdateStruct();

        $handler->expects($this->at(0))
            ->method('loadVersionInfo')
            ->with(23, 1)
            ->will(
                $this->returnValue(
                    new VersionInfo(array('contentInfo' => new ContentInfo(array('currentVersionNo' => 1))))
                )
            );

        $gatewayMock->expects($this->once())
            ->method('load')
            ->with(
                $this->equalTo(23),
                $this->equalTo(1),
                $this->equalTo(null)
            )->will(
                $this->returnValue(array(42))
            );

        $gatewayMock->expects($this->once())
            ->method('loadVersionedNameData')
            ->with(
                $this->equalTo(array(array('id' => 23, 'version' => 1)))
            )->will(
                $this->returnValue(array(22))
            );

        $mapperMock->expects($this->once())
            ->method('extractContentFromRows')
            ->with($this->equalTo(array(42)), $this->equalTo(array(22)))
            ->will($this->returnValue(array($this->getContentFixtureForDraft())));

        $fieldHandlerMock->expects($this->once())
            ->method('loadExternalFieldData')
            ->with($this->isInstanceOf(Content::class));

        $gatewayMock
            ->expects($this->once())
            ->method('updateContent')
            ->with(23, $metadataUpdateStruct);

        $locationMock
            ->expects($this->once())
            ->method('createLocationsFromNodeAssignments')
            ->with(23, 1);

        $locationMock
            ->expects($this->once())
            ->method('updateLocationsContentVersionNo')
            ->with(23, 1);

        $handler
            ->expects($this->once())
            ->method('setStatus')
            ->with(23, VersionInfo::STATUS_PUBLISHED, 1);

        $handler->publish(23, 1, $metadataUpdateStruct);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Handler::publish
     */
    public function testPublish()
    {
        $handler = $this->getPartlyMockedHandler(array('loadVersionInfo', 'setStatus'));

        $gatewayMock = $this->getGatewayMock();
        $mapperMock = $this->getMapperMock();
        $locationMock = $this->getLocationGatewayMock();
        $fieldHandlerMock = $this->getFieldHandlerMock();
        $metadataUpdateStruct = new MetadataUpdateStruct();

        $handler->expects($this->at(0))
            ->method('loadVersionInfo')
            ->with(23, 2)
            ->will(
                $this->returnValue(
                    new VersionInfo(array('contentInfo' => new ContentInfo(array('currentVersionNo' => 1))))
                )
            );

        $handler
            ->expects($this->at(1))
            ->method('setStatus')
            ->with(23, VersionInfo::STATUS_ARCHIVED, 1);

        $gatewayMock->expects($this->once())
            ->method('load')
            ->with(
                $this->equalTo(23),
                $this->equalTo(2),
                $this->equalTo(null)
            )
            ->will($this->returnValue(array(42)));

        $gatewayMock->expects($this->once())
            ->method('loadVersionedNameData')
            ->with(
                $this->equalTo(array(array('id' => 23, 'version' => 2)))
            )->will(
                $this->returnValue(array(22))
            );

        $mapperMock->expects($this->once())
            ->method('extractContentFromRows')
            ->with($this->equalTo(array(42)), $this->equalTo(array(22)))
            ->will($this->returnValue(array($this->getContentFixtureForDraft())));

        $fieldHandlerMock->expects($this->once())
            ->method('loadExternalFieldData')
            ->with($this->isInstanceOf(Content::class));

        $gatewayMock
            ->expects($this->once())
            ->method('updateContent')
            ->with(23, $metadataUpdateStruct, $this->isInstanceOf(VersionInfo::class));

        $locationMock
            ->expects($this->once())
            ->method('createLocationsFromNodeAssignments')
            ->with(23, 2);

        $locationMock
            ->expects($this->once())
            ->method('updateLocationsContentVersionNo')
            ->with(23, 2);

        $handler
            ->expects($this->at(2))
            ->method('setStatus')
            ->with(23, VersionInfo::STATUS_PUBLISHED, 2);

        $handler->publish(23, 2, $metadataUpdateStruct);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Handler::createDraftFromVersion
     */
    public function testCreateDraftFromVersion()
    {
        $handler = $this->getPartlyMockedHandler(array('load'));

        $mapperMock = $this->getMapperMock();
        $gatewayMock = $this->getGatewayMock();
        $fieldHandlerMock = $this->getFieldHandlerMock();

        $handler->expects($this->once())
            ->method('load')
            ->with(23, 2)
            ->will($this->returnValue($this->getContentFixtureForDraft()));

        $mapperMock->expects($this->once())
            ->method('createVersionInfoForContent')
            ->with(
                $this->isInstanceOf(Content::class),
                $this->equalTo(3),
                $this->equalTo(14)
            )->will(
                $this->returnValue(
                    new VersionInfo(
                        array(
                            'names' => array(),
                            'versionNo' => 3,
                        )
                    )
                )
            );

        $gatewayMock->expects($this->once())
            ->method('insertVersion')
            ->with(
                $this->isInstanceOf(VersionInfo::class),
                $this->getContentFixtureForDraft()->fields
            )->will($this->returnValue(42));

        $gatewayMock->expects($this->once())
            ->method('getLastVersionNumber')
            ->with($this->equalTo(23))
            ->will($this->returnValue(2));

        $fieldHandlerMock->expects($this->once())
            ->method('createExistingFieldsInNewVersion')
            ->with($this->isInstanceOf(Content::class));

        $relationData = array(
            array(
                'ezcontentobject_link_contentclassattribute_id' => 0,
                'ezcontentobject_link_to_contentobject_id' => 42,
                'ezcontentobject_link_relation_type' => 1,
            ),
        );

        $gatewayMock->expects($this->once())
            ->method('loadRelations')
            ->with(
                $this->equalTo(23),
                $this->equalTo(2)
            )
            ->will($this->returnValue($relationData));

        $relationStruct = new RelationCreateStruct(
            array(
                'sourceContentId' => 23,
                'sourceContentVersionNo' => 3,
                'sourceFieldDefinitionId' => 0,
                'destinationContentId' => 42,
                'type' => 1,
            )
        );

        $gatewayMock->expects($this->once())
            ->method('insertRelation')
            ->with($this->equalTo($relationStruct));

        $result = $handler->createDraftFromVersion(23, 2, 14);

        $this->assertInstanceOf(
            Content::class,
            $result
        );
        $this->assertEquals(
            42,
            $result->versionInfo->id
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Handler::load
     */
    public function testLoad()
    {
        $handler = $this->getContentHandler();

        $gatewayMock = $this->getGatewayMock();
        $mapperMock = $this->getMapperMock();
        $fieldHandlerMock = $this->getFieldHandlerMock();

        $gatewayMock->expects($this->once())
            ->method('load')
            ->with(
                $this->equalTo(23),
                $this->equalTo(2),
                $this->equalTo(array('eng-GB'))
            )->will(
                $this->returnValue(array(42))
            );

        $gatewayMock->expects($this->once())
            ->method('loadVersionedNameData')
            ->with(
                $this->equalTo(array(array('id' => 23, 'version' => 2)))
            )->will(
                $this->returnValue(array(22))
            );

        $mapperMock->expects($this->once())
            ->method('extractContentFromRows')
            ->with($this->equalTo(array(42)), $this->equalTo(array(22)))
            ->will($this->returnValue(array($this->getContentFixtureForDraft())));

        $fieldHandlerMock->expects($this->once())
            ->method('loadExternalFieldData')
            ->with($this->isInstanceOf(Content::class));

        $result = $handler->load(23, 2, array('eng-GB'));

        $this->assertEquals(
            $result,
            $this->getContentFixtureForDraft()
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Handler::loadContentInfoByRemoteId
     */
    public function testLoadContentInfoByRemoteId()
    {
        $contentInfoData = array(new ContentInfo());
        $this->getGatewayMock()->expects($this->once())
            ->method('loadContentInfoByRemoteId')
            ->with(
                $this->equalTo('15b256dbea2ae72418ff5facc999e8f9')
            )->will(
                $this->returnValue(array(42))
            );

        $this->getMapperMock()->expects($this->once())
            ->method('extractContentInfoFromRow')
            ->with($this->equalTo(array(42)))
            ->will($this->returnValue($contentInfoData));

        $this->assertSame(
            $contentInfoData,
            $this->getContentHandler()->loadContentInfoByRemoteId('15b256dbea2ae72418ff5facc999e8f9')
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Handler::load
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testLoadErrorNotFound()
    {
        $handler = $this->getContentHandler();

        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects($this->once())
            ->method('load')
            ->will(
                $this->returnValue(array())
            );

        $result = $handler->load(23, 2, array('eng-GB'));
    }

    /**
     * Returns a Content for {@link testCreateDraftFromVersion()}.
     *
     * @return \eZ\Publish\SPI\Persistence\Content
     */
    protected function getContentFixtureForDraft()
    {
        $content = new Content();
        $content->versionInfo = new VersionInfo();
        $content->versionInfo->versionNo = 2;

        $content->versionInfo->contentInfo = new ContentInfo();

        $field = new Field();
        $field->versionNo = 2;

        $content->fields = array($field);

        return $content;
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Handler::updateContent
     */
    public function testUpdateContent()
    {
        $handler = $this->getPartlyMockedHandler(array('load', 'loadContentInfo'));

        $gatewayMock = $this->getGatewayMock();
        $fieldHandlerMock = $this->getFieldHandlerMock();
        $contentTypeHandlerMock = $this->getContentTypeHandlerMock();
        $contentTypeMock = $this->createMock(Type::class);
        $contentStub = new Content(
            array(
                'versionInfo' => new VersionInfo(
                    array(
                        'contentInfo' => new ContentInfo(
                            array(
                                'contentTypeId' => 4242,
                            )
                        ),
                    )
                ),
            )
        );

        $contentTypeHandlerMock->expects($this->once())
            ->method('load')
            ->with($contentStub->versionInfo->contentInfo->contentTypeId)
            ->will($this->returnValue($contentTypeMock));

        $gatewayMock->expects($this->once())
            ->method('updateContent')
            ->with(14, $this->isInstanceOf(MetadataUpdateStruct::class));
        $gatewayMock->expects($this->once())
            ->method('updateVersion')
            ->with(14, 4, $this->isInstanceOf(UpdateStruct::class));

        $fieldHandlerMock->expects($this->once())
            ->method('updateFields')
            ->with(
                $this->isInstanceOf(Content::class),
                $this->isInstanceOf(UpdateStruct::class),
                $this->isInstanceOf(Type::class)
            );

        $handler->expects($this->at(0))
            ->method('load')
            ->with(14, 4)
            ->will($this->returnValue($contentStub));

        $handler->expects($this->at(1))
            ->method('load')
            ->with(14, 4);

        $handler->expects($this->at(2))
            ->method('loadContentInfo')
            ->with(14);

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
                    ),
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
                    'alwaysAvailable' => true,
                )
            )
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Handler::updateMetadata
     */
    public function testUpdateMetadata()
    {
        $handler = $this->getPartlyMockedHandler(array('load', 'loadContentInfo'));

        $gatewayMock = $this->getGatewayMock();
        $fieldHandlerMock = $this->getFieldHandlerMock();
        $updateStruct = new MetadataUpdateStruct(
            array(
                'ownerId' => 14,
                'name' => 'Some name',
                'modificationDate' => time(),
                'alwaysAvailable' => true,
            )
        );

        $gatewayMock->expects($this->once())
            ->method('updateContent')
            ->with(14, $updateStruct);

        $handler->expects($this->once())
            ->method('loadContentInfo')
            ->with(14)
            ->will(
                $this->returnValue(
                    $this->createMock(ContentInfo::class)
                )
            );

        $resultContentInfo = $handler->updateMetadata(
            14, // ContentId
            $updateStruct
        );
        self::assertInstanceOf(ContentInfo::class, $resultContentInfo);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Handler::updateMetadata
     */
    public function testUpdateMetadataUpdatesPathIdentificationString()
    {
        $handler = $this->getPartlyMockedHandler(array('load', 'loadContentInfo'));
        $locationGatewayMock = $this->getLocationGatewayMock();
        $slugConverterMock = $this->getSlugConverterMock();
        $urlAliasGatewayMock = $this->getUrlAliasGatewayMock();
        $gatewayMock = $this->getGatewayMock();
        $updateStruct = new MetadataUpdateStruct(array('mainLanguageId' => 2));

        $gatewayMock->expects($this->once())
            ->method('updateContent')
            ->with(14, $updateStruct);

        $locationGatewayMock->expects($this->once())
            ->method('loadLocationDataByContent')
            ->with(14)
            ->will(
                $this->returnValue(
                    array(
                        array(
                            'node_id' => 100,
                            'parent_node_id' => 200,
                        ),
                    )
                )
            );

        $urlAliasGatewayMock->expects($this->once())
            ->method('loadLocationEntries')
            ->with(100, false, 2)
            ->will(
                $this->returnValue(
                    array(
                        array(
                            'text' => 'slug',
                        ),
                    )
                )
            );

        $slugConverterMock->expects($this->once())
            ->method('convert')
            ->with('slug', 'node_100', 'urlalias_compat')
            ->will($this->returnValue('transformed_slug'));

        $locationGatewayMock->expects($this->once())
            ->method('updatePathIdentificationString')
            ->with(100, 200, 'transformed_slug');

        $handler->expects($this->once())
            ->method('loadContentInfo')
            ->with(14)
            ->will(
                $this->returnValue(
                    $this->createMock(ContentInfo::class)
                )
            );

        $handler->updateMetadata(
            14, // ContentId
            $updateStruct
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Handler::loadRelations
     */
    public function testLoadRelations()
    {
        $handler = $this->getContentHandler();

        $gatewayMock = $this->getGatewayMock();
        $mapperMock = $this->getMapperMock();

        $gatewayMock->expects($this->once())
            ->method('loadRelations')
            ->with(
                $this->equalTo(23),
                $this->equalTo(null),
                $this->equalTo(null)
            )->will(
                $this->returnValue(array(42))
            );

        $mapperMock->expects($this->once())
            ->method('extractRelationsFromRows')
            ->with($this->equalTo(array(42)))
            ->will($this->returnValue($this->getRelationFixture()));

        $result = $handler->loadRelations(23);

        $this->assertEquals(
            $result,
            $this->getRelationFixture()
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Handler::loadReverseRelations
     */
    public function testLoadReverseRelations()
    {
        $handler = $this->getContentHandler();

        $gatewayMock = $this->getGatewayMock();
        $mapperMock = $this->getMapperMock();

        $gatewayMock->expects($this->once())
            ->method('loadReverseRelations')
            ->with(
                $this->equalTo(23),
                $this->equalTo(null)
            )->will(
                $this->returnValue(array(42))
            );

        $mapperMock->expects($this->once())
            ->method('extractRelationsFromRows')
            ->with($this->equalTo(array(42)))
            ->will($this->returnValue($this->getRelationFixture()));

        $result = $handler->loadReverseRelations(23);

        $this->assertEquals(
            $result,
            $this->getRelationFixture()
        );
    }

    public function testAddRelation()
    {
        // expected relation object after creation
        $expectedRelationObject = new Relation();
        $expectedRelationObject->id = 42; // mocked value, not a real one
        $expectedRelationObject->sourceContentId = 23;
        $expectedRelationObject->sourceContentVersionNo = 1;
        $expectedRelationObject->destinationContentId = 66;
        $expectedRelationObject->type = RelationValue::COMMON;

        // relation create struct
        $relationCreateStruct = new Relation\CreateStruct();
        $relationCreateStruct->destinationContentId = 66;
        $relationCreateStruct->sourceContentId = 23;
        $relationCreateStruct->sourceContentVersionNo = 1;
        $relationCreateStruct->type = RelationValue::COMMON;

        $handler = $this->getContentHandler();

        $gatewayMock = $this->getGatewayMock();
        $mapperMock = $this->getMapperMock();

        $mapperMock->expects($this->once())
            ->method('createRelationFromCreateStruct')
            // @todo Connected with the todo above
            ->with($this->equalTo($relationCreateStruct))
            ->will($this->returnValue($expectedRelationObject));

        $gatewayMock->expects($this->once())
            ->method('insertRelation')
            ->with($this->equalTo($relationCreateStruct))
            ->will(
                // @todo Should this return a row as if it was selected from the database, the id... ? Check with other, similar create methods
                $this->returnValue(42)
            );

        $result = $handler->addRelation($relationCreateStruct);

        $this->assertEquals(
            $result,
            $expectedRelationObject
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Handler::removeRelation
     */
    public function testRemoveRelation()
    {
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects($this->once())
            ->method('deleteRelation')
            ->with($this->equalTo(1, RelationValue::COMMON));

        $this->getContentHandler()->removeRelation(1, RelationValue::COMMON);
    }

    protected function getRelationFixture()
    {
        $relation = new Relation();
        $relation->sourceContentId = 23;
        $relation->sourceContentVersionNo = 1;
        $relation->destinationContentId = 69;

        return $relation;
    }

    /**
     * Returns a CreateStruct fixture.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\CreateStruct
     */
    public function getCreateStructFixture()
    {
        $struct = new CreateStruct();

        $struct->typeId = 4242;

        $firstField = new Field();
        $firstField->type = 'some-type';
        $firstField->value = new FieldValue();

        $secondField = clone $firstField;

        $struct->fields = array(
            $firstField, $secondField,
        );

        $struct->locations = array(
            new LocationCreateStruct(
                array('parentId' => 42)
            ),
        );

        $struct->name = array(
            'eng-GB' => 'This is a test name',
        );

        return $struct;
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Handler::loadDraftsForUser
     */
    public function testLoadDraftsForUser()
    {
        $handler = $this->getContentHandler();
        $rows = array(array('ezcontentobject_version_contentobject_id' => 42, 'ezcontentobject_version_version' => 2));

        $gatewayMock = $this->getGatewayMock();
        $mapperMock = $this->getMapperMock();

        $gatewayMock->expects($this->once())
            ->method('listVersionsForUser')
            ->with($this->equalTo(23))
            ->will($this->returnValue($rows));

        $gatewayMock->expects($this->once())
            ->method('loadVersionedNameData')
            ->with($this->equalTo(array(array('id' => 42, 'version' => 2))))
            ->will($this->returnValue(array()));

        $mapperMock->expects($this->once())
            ->method('extractVersionInfoListFromRows')
            ->with($this->equalTo($rows), $this->equalTo(array()))
            ->will($this->returnValue(array(new VersionInfo())));

        $res = $handler->loadDraftsForUser(23);

        $this->assertEquals(
            array(new VersionInfo()),
            $res
        );
    }

    public function testListVersions()
    {
        $handler = $this->getContentHandler();

        $treeHandlerMock = $this->getTreeHandlerMock();

        $treeHandlerMock
            ->expects($this->once())
            ->method('listVersions')
            ->with(23)
            ->will($this->returnValue(array(new VersionInfo())));

        $versions = $handler->listVersions(23);

        $this->assertEquals(
            array(new VersionInfo()),
            $versions
        );
    }

    public function testRemoveRawContent()
    {
        $handler = $this->getContentHandler();
        $treeHandlerMock = $this->getTreeHandlerMock();

        $treeHandlerMock
            ->expects($this->once())
            ->method('removeRawContent')
            ->with(23);

        $handler->removeRawContent(23);
    }

    /**
     * Test for the deleteContent() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Handler::deleteContent
     */
    public function testDeleteContentWithLocations()
    {
        $handlerMock = $this->getPartlyMockedHandler(array('getAllLocationIds'));
        $gatewayMock = $this->getGatewayMock();
        $treeHandlerMock = $this->getTreeHandlerMock();

        $gatewayMock->expects($this->once())
            ->method('getAllLocationIds')
            ->with($this->equalTo(23))
            ->will($this->returnValue(array(42, 24)));
        $treeHandlerMock->expects($this->exactly(2))
            ->method('removeSubtree')
            ->with(
                $this->logicalOr(
                    $this->equalTo(42),
                    $this->equalTo(24)
                )
            );

        $handlerMock->deleteContent(23);
    }

    /**
     * Test for the deleteContent() method.
     *
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Handler::deleteContent
     */
    public function testDeleteContentWithoutLocations()
    {
        $handlerMock = $this->getPartlyMockedHandler(array('removeRawContent'));
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects($this->once())
            ->method('getAllLocationIds')
            ->with($this->equalTo(23))
            ->will($this->returnValue(array()));
        $handlerMock->expects($this->once())
            ->method('removeRawContent')
            ->with($this->equalTo(23));

        $handlerMock->deleteContent(23);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Handler::deleteVersion
     */
    public function testDeleteVersion()
    {
        $handler = $this->getContentHandler();

        $gatewayMock = $this->getGatewayMock();
        $mapperMock = $this->getMapperMock();
        $locationHandlerMock = $this->getLocationGatewayMock();
        $fieldHandlerMock = $this->getFieldHandlerMock();

        // Load VersionInfo to delete fields
        $gatewayMock->expects($this->once())
            ->method('loadVersionInfo')
            ->with($this->equalTo(225), $this->equalTo(2))
            ->will($this->returnValue(array(42)));

        $gatewayMock->expects($this->once())
            ->method('loadVersionedNameData')
            ->with($this->equalTo(array(array('id' => 225, 'version' => 2))))
            ->will($this->returnValue(array(22)));

        $mapperMock->expects($this->once())
            ->method('extractVersionInfoListFromRows')
            ->with($this->equalTo(array(42)), $this->equalTo(array(22)))
            ->will($this->returnValue(array(new VersionInfo())));

        $locationHandlerMock->expects($this->once())
            ->method('deleteNodeAssignment')
            ->with(
                $this->equalTo(225),
                $this->equalTo(2)
            );

        $fieldHandlerMock->expects($this->once())
            ->method('deleteFields')
            ->with(
                $this->equalTo(225),
                $this->isInstanceOf(VersionInfo::class)
            );
        $gatewayMock->expects($this->once())
            ->method('deleteRelations')
            ->with(
                $this->equalTo(225),
                $this->equalTo(2)
            );
        $gatewayMock->expects($this->once())
            ->method('deleteVersions')
            ->with(
                $this->equalTo(225),
                $this->equalTo(2)
            );
        $gatewayMock->expects($this->once())
            ->method('deleteNames')
            ->with(
                $this->equalTo(225),
                $this->equalTo(2)
            );

        $handler->deleteVersion(225, 2);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Handler::copy
     */
    public function testCopySingleVersion()
    {
        $handler = $this->getPartlyMockedHandler(array('load', 'internalCreate'));
        $gatewayMock = $this->getGatewayMock();
        $mapperMock = $this->getMapperMock();

        $handler->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            $this->equalTo(23),
            $this->equalTo(32)
        )->will(
            $this->returnValue(new Content())
        );

        $mapperMock->expects(
            $this->once()
        )->method(
            'createCreateStructFromContent'
        )->with(
            $this->isInstanceOf(Content::class)
        )->will(
            $this->returnValue(new CreateStruct())
        );

        $handler->expects(
            $this->once()
        )->method(
            'internalCreate'
        )->with(
            $this->isInstanceOf(CreateStruct::class),
            $this->equalTo(32)
        )->will(
            $this->returnValue(
                new Content(
                    [
                        'versionInfo' => new VersionInfo(['contentInfo' => new ContentInfo(['id' => 24])]),
                    ]
                )
            )
        );

        $gatewayMock->expects($this->once())
            ->method('copyRelations')
            ->with(
                $this->equalTo(23),
                $this->equalTo(24),
                $this->equalTo(32)
            )
            ->will($this->returnValue(null));

        $result = $handler->copy(23, 32);

        $this->assertInstanceOf(
            Content::class,
            $result
        );
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Handler::copy
     */
    public function testCopyAllVersions()
    {
        $handler = $this->getPartlyMockedHandler(
            array(
                'loadContentInfo',
                'load',
                'internalCreate',
                'listVersions',
            )
        );
        $gatewayMock = $this->getGatewayMock();
        $mapperMock = $this->getMapperMock();
        $fieldHandlerMock = $this->getFieldHandlerMock();
        $contentTypeHandlerMock = $this->getContentTypeHandlerMock();
        $contentTypeMock = $this->createMock(Type::class);
        $time = time();
        $createStructStub = new CreateStruct(
            array(
                'modified' => $time,
                'typeId' => 4242,
            )
        );

        $contentTypeHandlerMock->expects($this->once())
            ->method('load')
            ->with($createStructStub->typeId)
            ->will($this->returnValue($contentTypeMock));

        $handler->expects($this->once())
            ->method('loadContentInfo')
            ->with($this->equalTo(23))
            ->will($this->returnValue(new ContentInfo(array('currentVersionNo' => 2))));

        $handler->expects($this->at(1))
            ->method('load')
            ->with($this->equalTo(23), $this->equalTo(2))
            ->will($this->returnValue(new Content()));

        $mapperMock->expects($this->once())
            ->method('createCreateStructFromContent')
            ->with($this->isInstanceOf(Content::class))
            ->will(
                $this->returnValue($createStructStub)
            );

        $handler->expects($this->once())
            ->method('internalCreate')
            ->with(
                $this->isInstanceOf(CreateStruct::class),
                $this->equalTo(2)
            )->will(
                $this->returnValue(
                    new Content(
                        array(
                            'versionInfo' => new VersionInfo(
                                array(
                                    'contentInfo' => new ContentInfo(array('id' => 24)),
                                )
                            ),
                        )
                    )
                )
            );

        $handler->expects($this->once())
            ->method('listVersions')
            ->with($this->equalTo(23))
            ->will(
                $this->returnValue(
                    array(
                        new VersionInfo(array('versionNo' => 1)),
                        new VersionInfo(array('versionNo' => 2)),
                    )
                )
            );

        $versionInfo = new VersionInfo(
            array(
                'names' => array('eng-US' => 'Test'),
                'contentInfo' => new ContentInfo(
                    array(
                        'id' => 24,
                        'alwaysAvailable' => true,
                    )
                ),
            )
        );
        $handler->expects($this->at(4))
            ->method('load')
            ->with($this->equalTo(23), $this->equalTo(1))
            ->will(
                $this->returnValue(
                    new Content(
                        array(
                            'versionInfo' => $versionInfo,
                            'fields' => array(),
                        )
                    )
                )
            );

        $versionInfo->creationDate = $time;
        $versionInfo->modificationDate = $time;
        $gatewayMock->expects($this->once())
            ->method('insertVersion')
            ->with(
                $this->equalTo($versionInfo),
                $this->isType('array')
            )->will($this->returnValue(42));

        $versionInfo = clone $versionInfo;
        $versionInfo->id = 42;
        $fieldHandlerMock->expects($this->once())
            ->method('createNewFields')
            ->with(
                $this->equalTo(
                    new Content(
                        array(
                            'versionInfo' => $versionInfo,
                            'fields' => array(),
                        )
                    )
                ),
                $this->isInstanceOf(Type::class)
            );

        $gatewayMock->expects($this->once())
            ->method('setName')
            ->with(
                $this->equalTo(24),
                $this->equalTo(1),
                $this->equalTo('Test'),
                $this->equalTo('eng-US')
            );

        $gatewayMock->expects($this->once())
            ->method('copyRelations')
            ->with(
                $this->equalTo(23),
                $this->equalTo(24),
                $this->equalTo(null)
            )
            ->will($this->returnValue(null));

        $result = $handler->copy(23);

        $this->assertInstanceOf(
            Content::class,
            $result
        );
    }

    /**
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testCopyThrowsNotFoundExceptionContentNotFound()
    {
        $handler = $this->getContentHandler();

        $treeHandlerMock = $this->getTreeHandlerMock();
        $treeHandlerMock
            ->expects($this->once())
            ->method('loadContentInfo')
            ->with($this->equalTo(23))
            ->will(
                $this->throwException(new NotFoundException('ContentInfo', 23))
            );

        $handler->copy(23);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Handler::copy
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testCopyThrowsNotFoundExceptionVersionNotFound()
    {
        $handler = $this->getContentHandler();

        $gatewayMock = $this->getGatewayMock();
        $gatewayMock->expects($this->once())
            ->method('load')
            ->with($this->equalTo(23, 32))
            ->will($this->returnValue(array()));

        $result = $handler->copy(23, 32);
    }

    /**
     * @covers \eZ\Publish\Core\Persistence\Legacy\Content\Handler::setStatus
     */
    public function testSetStatus()
    {
        $handler = $this->getContentHandler();

        $mapperMock = $this->getMapperMock();
        $gatewayMock = $this->getGatewayMock();

        $gatewayMock->expects($this->once())
            ->method('setStatus')
            ->with(23, 5, 2)
            ->will($this->returnValue(true));

        $this->assertTrue(
            $handler->setStatus(23, 2, 5)
        );
    }

    /**
     * Returns the handler to test.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Handler
     */
    protected function getContentHandler()
    {
        if (!isset($this->contentHandler)) {
            $this->contentHandler = new Handler(
                $this->getGatewayMock(),
                $this->getLocationGatewayMock(),
                $this->getMapperMock(),
                $this->getFieldHandlerMock(),
                $this->getSlugConverterMock(),
                $this->getUrlAliasGatewayMock(),
                $this->getContentTypeHandlerMock(),
                $this->getTreeHandlerMock()
            );
        }

        return $this->contentHandler;
    }

    /**
     * Returns the handler to test with $methods mocked.
     *
     * @param string[] $methods
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Handler
     */
    protected function getPartlyMockedHandler(array $methods)
    {
        return $this->getMockBuilder(Handler::class)
            ->setMethods($methods)
            ->setConstructorArgs(
                array(
                    $this->getGatewayMock(),
                    $this->getLocationGatewayMock(),
                    $this->getMapperMock(),
                    $this->getFieldHandlerMock(),
                    $this->getSlugConverterMock(),
                    $this->getUrlAliasGatewayMock(),
                    $this->getContentTypeHandlerMock(),
                    $this->getTreeHandlerMock(),
                )
            )
            ->getMock();
    }

    /**
     * Returns a TreeHandler mock.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\Persistence\Legacy\Content\TreeHandler
     */
    protected function getTreeHandlerMock()
    {
        if (!isset($this->treeHandlerMock)) {
            $this->treeHandlerMock = $this->createMock(TreeHandler::class);
        }

        return $this->treeHandlerMock;
    }

    /**
     * Returns a ContentTypeHandler mock.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\eZ\Publish\Core\Persistence\Legacy\Content\Type\Handler
     */
    protected function getContentTypeHandlerMock()
    {
        if (!isset($this->contentTypeHandlerMock)) {
            $this->contentTypeHandlerMock = $this->createMock(ContentTypeHandler::class);
        }

        return $this->contentTypeHandlerMock;
    }

    /**
     * Returns a FieldHandler mock.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\FieldHandler
     */
    protected function getFieldHandlerMock()
    {
        if (!isset($this->fieldHandlerMock)) {
            $this->fieldHandlerMock = $this->createMock(FieldHandler::class);
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
        if (!isset($this->mapperMock)) {
            $this->mapperMock = $this->createMock(Mapper::class);
        }

        return $this->mapperMock;
    }

    /**
     * Returns a Location Gateway mock.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Location\Gateway
     */
    protected function getLocationGatewayMock()
    {
        if (!isset($this->locationGatewayMock)) {
            $this->locationGatewayMock = $this->createMock(LocationGateway::class);
        }

        return $this->locationGatewayMock;
    }

    /**
     * Returns a Content Type gateway mock.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\Type\Gateway
     */
    protected function getTypeGatewayMock()
    {
        if (!isset($this->typeGatewayMock)) {
            $this->typeGatewayMock = $this->createMock(ContentTypeGateway::class);
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
        if (!isset($this->gatewayMock)) {
            $this->gatewayMock = $this->getMockForAbstractClass(ContentGateway::class);
        }

        return $this->gatewayMock;
    }

    /**
     * Returns a mock object for the UrlAlias Handler.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\SlugConverter
     */
    protected function getSlugConverterMock()
    {
        if (!isset($this->slugConverterMock)) {
            $this->slugConverterMock = $this->createMock(SlugConverter::class);
        }

        return $this->slugConverterMock;
    }

    /**
     * Returns a mock object for the UrlAlias Gateway.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\UrlAlias\Gateway
     */
    protected function getUrlAliasGatewayMock()
    {
        if (!isset($this->urlAliasGatewayMock)) {
            $this->urlAliasGatewayMock = $this->getMockForAbstractClass(UrlAliasGateway::class);
        }

        return $this->urlAliasGatewayMock;
    }
}
