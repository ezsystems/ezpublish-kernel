<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\Tests;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Relation;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\FieldType\ImageAsset as ImageAsset;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

/**
 * @group fieldType
 * @group ezimageasset
 */
class ImageAssetTest extends FieldTypeTest
{
    /** @var \eZ\Publish\API\Repository\ContentService|\PHPUnit\Framework\MockObject\MockObject */
    private $contentServiceMock;

    /** @var \eZ\Publish\API\Repository\ContentTypeService|\PHPUnit\Framework\MockObject\MockObject */
    private $contentTypeServiceMock;

    /** @var \eZ\Publish\Core\FieldType\ImageAsset\AssetMapper|\PHPUnit\Framework\MockObject\MockObject */
    private $assetMapperMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->contentServiceMock = $this->createMock(ContentService::class);
        $this->contentTypeServiceMock = $this->createMock(ContentTypeService::class);
        $this->assetMapperMock = $this->createMock(ImageAsset\AssetMapper::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function provideFieldTypeIdentifier(): string
    {
        return ImageAsset\Type::FIELD_TYPE_IDENTIFIER;
    }

    /**
     * {@inheritdoc}
     */
    protected function createFieldTypeUnderTest()
    {
        return new ImageAsset\Type(
            $this->contentServiceMock,
            $this->contentTypeServiceMock,
            $this->assetMapperMock
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getValidatorConfigurationSchemaExpectation(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function getSettingsSchemaExpectation(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function getEmptyValueExpectation()
    {
        return new ImageAsset\Value();
    }

    /**
     * {@inheritdoc}
     */
    public function provideInvalidInputForAcceptValue(): array
    {
        return [
            [
                true,
                InvalidArgumentException::class,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function provideValidInputForAcceptValue(): array
    {
        $destinationContentId = 7;

        return [
            [
                null,
                $this->getEmptyValueExpectation(),
            ],
            [
                $destinationContentId,
                new ImageAsset\Value($destinationContentId),
            ],
            [
                new ContentInfo([
                    'id' => $destinationContentId,
                ]),
                new ImageAsset\Value($destinationContentId),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function provideInputForToHash(): array
    {
        $destinationContentId = 7;
        $alternativeText = 'The alternative text for image';

        return [
            [
                new ImageAsset\Value(),
                [
                    'destinationContentId' => null,
                    'alternativeText' => null,
                ],
            ],
            [
                new ImageAsset\Value($destinationContentId),
                [
                    'destinationContentId' => $destinationContentId,
                    'alternativeText' => null,
                ],
            ],
            [
                new ImageAsset\Value($destinationContentId, $alternativeText),
                [
                    'destinationContentId' => $destinationContentId,
                    'alternativeText' => $alternativeText,
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function provideInputForFromHash(): array
    {
        $destinationContentId = 7;
        $alternativeText = 'The alternative text for image';

        return [
            [
                null,
                new ImageAsset\Value(),
            ],
            [
                [
                    'destinationContentId' => $destinationContentId,
                    'alternativeText' => null,
                ],
                new ImageAsset\Value($destinationContentId),
            ],
            [
                [
                    'destinationContentId' => $destinationContentId,
                    'alternativeText' => $alternativeText,
                ],
                new ImageAsset\Value($destinationContentId, $alternativeText),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function provideInvalidDataForValidate(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function testValidateNonAsset()
    {
        $destinationContentId = 7;
        $destinationContent = $this->createMock(Content::class);
        $invalidContentTypeId = 789;
        $invalidContentTypeIdentifier = 'article';
        $invalidContentType = $this->createMock(ContentType::class);

        $destinationContentInfo = $this->createMock(ContentInfo::class);

        $destinationContentInfo
            ->expects($this->once())
            ->method('__get')
            ->with('contentTypeId')
            ->willReturn($invalidContentTypeId);

        $destinationContent
            ->expects($this->once())
            ->method('__get')
            ->with('contentInfo')
            ->willReturn($destinationContentInfo);

        $this->contentServiceMock
            ->expects($this->once())
            ->method('loadContent')
            ->with($destinationContentId)
            ->willReturn($destinationContent);

        $this->assetMapperMock
            ->expects($this->once())
            ->method('isAsset')
            ->with($destinationContent)
            ->willReturn(false);

        $this->contentTypeServiceMock
            ->expects($this->once())
            ->method('loadContentType')
            ->with($invalidContentTypeId)
            ->willReturn($invalidContentType);

        $invalidContentType
            ->expects($this->once())
            ->method('__get')
            ->with('identifier')
            ->willReturn($invalidContentTypeIdentifier);

        $validationErrors = $this->doValidate([], new ImageAsset\Value($destinationContentId));

        $this->assertInternalType('array', $validationErrors);
        $this->assertEquals([
            new ValidationError(
                'Content %type% is not a valid asset target',
                null,
                [
                    '%type%' => $invalidContentTypeIdentifier,
                ],
                'destinationContentId'
            ),
        ], $validationErrors);
    }

    /**
     * {@inheritdoc}
     */
    public function provideValidDataForValidate(): array
    {
        return [
            [
                [],
                $this->getEmptyValueExpectation(),
            ],
        ];
    }

    public function testValidateValidNonEmptyAssetValue()
    {
        $destinationContentId = 7;
        $destinationContent = $this->createMock(Content::class);

        $this->contentServiceMock
            ->expects($this->once())
            ->method('loadContent')
            ->with($destinationContentId)
            ->willReturn($destinationContent);

        $this->assetMapperMock
            ->expects($this->once())
            ->method('isAsset')
            ->with($destinationContent)
            ->willReturn(true);

        $validationErrors = $this->doValidate([], new ImageAsset\Value($destinationContentId));

        $this->assertInternalType('array', $validationErrors);
        $this->assertEmpty($validationErrors, "Got value:\n" . var_export($validationErrors, true));
    }

    /**
     * {@inheritdoc}
     */
    public function provideDataForGetName(): array
    {
        return [
            [
                $this->getEmptyValueExpectation(),
                '',
            ],
        ];
    }

    /**
     * @dataProvider provideDataForGetName
     *
     * @expectedException \RuntimeException
     */
    public function testGetName(SPIValue $value, $expected)
    {
        $this->getFieldTypeUnderTest()->getName($value);
    }

    public function testIsSearchable()
    {
        $this->assertTrue($this->getFieldTypeUnderTest()->isSearchable());
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\Relation\Type::getRelations
     */
    public function testGetRelations()
    {
        $destinationContentId = 7;
        $fieldType = $this->createFieldTypeUnderTest();

        $this->assertEquals(
            [
                Relation::ASSET => [$destinationContentId],
            ],
            $fieldType->getRelations($fieldType->acceptValue($destinationContentId))
        );
    }
}
