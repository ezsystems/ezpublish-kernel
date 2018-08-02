<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Bundle\EzPublishCoreBundle\Tests\Imagine\ImageAsset;

use eZ\Bundle\EzPublishCoreBundle\Imagine\ImageAsset\AliasGenerator;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\FieldType\ImageAsset;
use eZ\Publish\Core\FieldType\Image;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\SPI\Variation\Values\Variation;
use eZ\Publish\SPI\Variation\VariationHandler;
use PHPUnit\Framework\TestCase;

class AliasGeneratorTest extends TestCase
{
    /** @var \eZ\Bundle\EzPublishCoreBundle\Imagine\ImageAsset\AliasGenerator */
    private $aliasGenerator;

    /** @var \eZ\Publish\SPI\Variation\VariationHandler|\PHPUnit_Framework_MockObject_MockObject */
    private $innerAliasGenerator;

    /** @var \eZ\Publish\API\Repository\ContentService|\PHPUnit_Framework_MockObject_MockObject */
    private $contentService;

    /** @var \eZ\Publish\Core\FieldType\ImageAsset\AssetMapper|\PHPUnit_Framework_MockObject_MockObject */
    private $assetMapper;

    protected function setUp()
    {
        $this->innerAliasGenerator = $this->createMock(VariationHandler::class);
        $this->contentService = $this->createMock(ContentService::class);
        $this->assetMapper = $this->createMock(ImageAsset\AssetMapper::class);

        $this->aliasGenerator = new AliasGenerator(
            $this->innerAliasGenerator,
            $this->contentService,
            $this->assetMapper
        );
    }

    public function testGetVariationOfImageAsset()
    {
        $assetField = new Field([
            'value' => new ImageAsset\Value([
                'destinationContentId' => 486,
            ]),
        ]);
        $imageField = new Field([
            'value' => new Image\Value([
                'id' => 'images/6/8/4/0/486-10-eng-GB/photo.jpg',
            ]),
        ]);

        $assetVersionInfo = new VersionInfo();
        $imageVersionInfo = new VersionInfo();
        $imageContent = new Content([
            'versionInfo' => $imageVersionInfo,
        ]);

        $variationName = 'thumbnail';
        $parameters = [];

        $expectedVariation = new Variation();

        $this->contentService
            ->expects($this->once())
            ->method('loadContent')
            ->with($assetField->value->destinationContentId)
            ->willReturn($imageContent);

        $this->assetMapper
            ->expects($this->once())
            ->method('getAssetField')
            ->with($imageContent)
            ->willReturn($imageField);

        $this->innerAliasGenerator
            ->expects($this->once())
            ->method('getVariation')
            ->with($imageField, $imageVersionInfo, $variationName, $parameters)
            ->willReturn($expectedVariation);

        $actualVariation = $this->aliasGenerator->getVariation(
            $assetField,
            $assetVersionInfo,
            $variationName,
            $parameters
        );

        $this->assertEquals($expectedVariation, $actualVariation);
    }

    public function testGetVariationOfNonImageAsset()
    {
        $imageField = new Field([
            'value' => new Image\Value([
                'id' => 'images/6/8/4/0/486-10-eng-GB/photo.jpg',
            ]),
        ]);

        $imageVersionInfo = new VersionInfo();
        $variationName = 'thumbnail';
        $parameters = [];

        $expectedVariation = new Variation();

        $this->contentService
            ->expects($this->never())
            ->method('loadContent');

        $this->assetMapper
            ->expects($this->never())
            ->method('getAssetField');

        $this->innerAliasGenerator
            ->expects($this->once())
            ->method('getVariation')
            ->with($imageField, $imageVersionInfo, $variationName, $parameters)
            ->willReturn($expectedVariation);

        $actualVariation = $this->aliasGenerator->getVariation(
            $imageField,
            $imageVersionInfo,
            $variationName,
            $parameters
        );

        $this->assertEquals($expectedVariation, $actualVariation);
    }

    public function testSupport()
    {
        $this->assertTrue($this->aliasGenerator->supportsValue(new ImageAsset\Value()));
        $this->assertFalse($this->aliasGenerator->supportsValue(new Image\Value()));
    }
}
