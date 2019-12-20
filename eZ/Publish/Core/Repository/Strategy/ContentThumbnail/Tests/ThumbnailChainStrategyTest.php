<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Strategy\ContentThumbnail\Tests;

use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\Thumbnail;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Repository\Strategy\ContentThumbnail\ThumbnailChainStrategy;
use eZ\Publish\SPI\Repository\Strategy\ContentThumbnail\ThumbnailStrategy;
use PHPUnit\Framework\TestCase;

class ThumbnailChainStrategyTest extends TestCase
{
    public function testThumbnailStrategyChaining()
    {
        $firstStrategyMock = $this->createMock(ThumbnailStrategy::class);
        $secondStrategyMock = $this->createMock(ThumbnailStrategy::class);

        $contentTypeMock = $this->createMock(ContentType::class);
        $fieldMocks = [
            $this->createMock(Field::class),
            $this->createMock(Field::class),
            $this->createMock(Field::class),
        ];

        $firstStrategyMock
            ->expects($this->once())
            ->method('getThumbnail')
            ->willReturn(null);

        $secondStrategyMock
            ->expects($this->once())
            ->method('getThumbnail')
            ->willReturn(new Thumbnail());

        $thumbnailChainStrategy = new ThumbnailChainStrategy([
            $firstStrategyMock,
            $secondStrategyMock,
        ]);

        $result = $thumbnailChainStrategy->getThumbnail(
            $contentTypeMock,
            $fieldMocks
        );

        $this->assertInstanceOf(Thumbnail::class, $result);
    }

    public function testThumbnailStrategyChainBreakOnThumbnailFound()
    {
        $firstStrategyMock = $this->createMock(ThumbnailStrategy::class);
        $secondStrategyMock = $this->createMock(ThumbnailStrategy::class);
        $thirdStrategyMock = $this->createMock(ThumbnailStrategy::class);

        $contentTypeMock = $this->createMock(ContentType::class);
        $fieldMocks = [
            $this->createMock(Field::class),
            $this->createMock(Field::class),
            $this->createMock(Field::class),
        ];

        $firstStrategyMock
            ->expects($this->once())
            ->method('getThumbnail')
            ->willReturn(null);

        $secondStrategyMock
            ->expects($this->once())
            ->method('getThumbnail')
            ->willReturn(new Thumbnail([
                'resource' => 'second',
            ]));

        $thirdStrategyMock
            ->expects($this->never())
            ->method('getThumbnail')
            ->willReturn(new Thumbnail([
                'resource' => 'third',
            ]));

        $thumbnailChainStrategy = new ThumbnailChainStrategy([
            $firstStrategyMock,
            $secondStrategyMock,
            $thirdStrategyMock,
        ]);

        $result = $thumbnailChainStrategy->getThumbnail(
            $contentTypeMock,
            $fieldMocks
        );

        $this->assertInstanceOf(Thumbnail::class, $result);
        $this->assertEquals(new Thumbnail(['resource' => 'second']), $result);
    }
}
