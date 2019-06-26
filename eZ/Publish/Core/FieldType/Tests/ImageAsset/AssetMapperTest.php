<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\Tests\ImageAsset;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\FieldType\ImageAsset\AssetMapper;
use eZ\Publish\Core\Repository\ContentTypeService;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\Core\FieldType\Image;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use PHPUnit\Framework\TestCase;

class AssetMapperTest extends TestCase
{
    const EXAMPLE_CONTENT_ID = 487;

    /** @var \eZ\Publish\API\Repository\ContentService|\PHPUnit_Framework_MockObject_MockObject */
    private $contentService;

    /** @var \eZ\Publish\API\Repository\LocationService|\PHPUnit_Framework_MockObject_MockObject */
    private $locationService;

    /** @var \eZ\Publish\API\Repository\ContentTypeService|\PHPUnit_Framework_MockObject_MockObject */
    private $contentTypeService;

    /** @var array */
    private $mappings = [
        'content_type_identifier' => 'image',
        'content_field_identifier' => 'image',
        'name_field_identifier' => 'name',
        'parent_location_id' => 51,
    ];

    protected function setUp()
    {
        $this->contentService = $this->createMock(ContentService::class);
        $this->locationService = $this->createMock(LocationService::class);
        $this->contentTypeService = $this->createMock(ContentTypeService::class);
    }

    public function testCreateAsset()
    {
        $name = 'Example asset';
        $value = new Image\Value();
        $contentType = new ContentType();
        $languageCode = 'eng-GB';
        $contentCreateStruct = $this->createMock(ContentCreateStruct::class);
        $locationCreateStruct = new LocationCreateStruct();
        $contentDraft = new Content([
            'versionInfo' => new VersionInfo(),
        ]);
        $content = new Content();

        $this->contentTypeService
            ->expects($this->once())
            ->method('loadContentTypeByIdentifier')
            ->with($this->mappings['content_type_identifier'])
            ->willReturn($contentType);

        $this->contentService
            ->expects($this->once())
            ->method('newContentCreateStruct')
            ->with($contentType, $languageCode)
            ->willReturn($contentCreateStruct);

        $contentCreateStruct
            ->expects($this->at(0))
            ->method('setField')
            ->with($this->mappings['name_field_identifier'], $name);

        $contentCreateStruct
            ->expects($this->at(1))
            ->method('setField')
            ->with($this->mappings['content_field_identifier'], $value);

        $this->locationService
            ->expects($this->once())
            ->method('newLocationCreateStruct')
            ->with($this->mappings['parent_location_id'])
            ->willReturn($locationCreateStruct);

        $this->contentService
            ->expects($this->once())
            ->method('createContent')
            ->with($contentCreateStruct, [$locationCreateStruct])
            ->willReturn($contentDraft);

        $this->contentService
            ->expects($this->once())
            ->method('publishVersion')
            ->with($contentDraft->versionInfo)
            ->willReturn($content);

        $mapper = $this->createMapper();
        $mapper->createAsset($name, $value, $languageCode);
    }

    public function testGetAssetField()
    {
        $expectedValue = new Field();
        $content = $this->createContentWithId(self::EXAMPLE_CONTENT_ID);

        $mapper = $this->createPartialMapper(['isAsset']);
        $mapper
            ->expects($this->once())
            ->method('isAsset')
            ->with($content)
            ->willReturn(true);

        $content
            ->expects($this->once())
            ->method('getField')
            ->with($this->mappings['content_field_identifier'])
            ->willReturn($expectedValue);

        $this->assertEquals($expectedValue, $mapper->getAssetField($content));
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testGetAssetFieldThrowsInvalidArgumentException()
    {
        $content = $this->createContentWithId(self::EXAMPLE_CONTENT_ID);

        $mapper = $this->createPartialMapper(['isAsset']);
        $mapper
            ->expects($this->once())
            ->method('isAsset')
            ->with($content)
            ->willReturn(false);

        $mapper->getAssetField($content);
    }

    public function testGetAssetFieldDefinition()
    {
        $fieldDefinition = new FieldDefinition();

        $contentType = $this->createMock(ContentType::class);
        $contentType
            ->expects($this->once())
            ->method('getFieldDefinition')
            ->with($this->mappings['content_field_identifier'])
            ->willReturn($fieldDefinition);

        $this->contentTypeService
            ->expects($this->once())
            ->method('loadContentTypeByIdentifier')
            ->with($this->mappings['content_type_identifier'])
            ->willReturn($contentType);

        $this->assertEquals($fieldDefinition, $this->createMapper()->getAssetFieldDefinition());
    }

    public function testGetAssetValue()
    {
        $expectedValue = new Image\Value();
        $content = $this->createContentWithId(self::EXAMPLE_CONTENT_ID);

        $mapper = $this->createPartialMapper(['isAsset']);
        $mapper
            ->expects($this->once())
            ->method('isAsset')
            ->with($content)
            ->willReturn(true);

        $content
            ->expects($this->once())
            ->method('getFieldValue')
            ->with($this->mappings['content_field_identifier'])
            ->willReturn($expectedValue);

        $this->assertEquals($expectedValue, $mapper->getAssetValue($content));
    }

    /**
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testGetAssetValueThrowsInvalidArgumentException()
    {
        $content = $this->createContentWithId(self::EXAMPLE_CONTENT_ID);

        $mapper = $this->createPartialMapper(['isAsset']);
        $mapper
            ->expects($this->once())
            ->method('isAsset')
            ->with($content)
            ->willReturn(false);

        $mapper->getAssetField($content);
    }

    /**
     * @dataProvider dataProviderForIsAsset
     */
    public function testIsAsset(int $contentContentTypeId, int $assetContentTypeId, bool $expected)
    {
        $assetContentType = new ContentType([
            'id' => $assetContentTypeId,
        ]);

        $this->contentTypeService
            ->expects($this->once())
            ->method('loadContentTypeByIdentifier')
            ->with($this->mappings['content_type_identifier'])
            ->willReturn($assetContentType);

        $actual = $this
            ->createMapper()
            ->isAsset($this->createContentWithContentType($contentContentTypeId));

        $this->assertEquals($expected, $actual);
    }

    public function dataProviderForIsAsset(): array
    {
        return [
            [487, 487, true],
            [487, 784, false],
        ];
    }

    public function testGetContentFieldIdentifier()
    {
        $mapper = $this->createMapper();

        $this->assertEquals(
            $this->mappings['content_field_identifier'],
            $mapper->getContentFieldIdentifier()
        );
    }

    public function testGetParentLocationId()
    {
        $mapper = $this->createMapper();

        $this->assertEquals(
            $this->mappings['parent_location_id'],
            $mapper->getParentLocationId()
        );
    }

    private function createMapper(): AssetMapper
    {
        return new AssetMapper(
            $this->contentService,
            $this->locationService,
            $this->contentTypeService,
            $this->mappings
        );
    }

    private function createPartialMapper(array $methods = []): AssetMapper
    {
        return $this
            ->getMockBuilder(AssetMapper::class)
            ->setConstructorArgs([
                $this->contentService,
                $this->locationService,
                $this->contentTypeService,
                $this->mappings,
            ])
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->setMethods($methods)
            ->getMock();
    }

    private function createContentWithId(int $id): Content
    {
        $content = $this->createMock(Content::class);
        $content
            ->expects($this->any())
            ->method('__get')
            ->with('id')
            ->willReturn($id);

        return $content;
    }

    private function createContentWithContentType(int $contentTypeId): Content
    {
        $contentInfo = new ContentInfo([
            'contentTypeId' => $contentTypeId,
        ]);

        $content = $this->createMock(Content::class);
        $content
            ->expects($this->any())
            ->method('__get')
            ->with('contentInfo')
            ->willReturn($contentInfo);

        return $content;
    }
}
