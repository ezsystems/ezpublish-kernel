<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Specification\Tests\Content;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\SPI\Specification\Content\ContentContainerSpecification;
use eZ\Publish\SPI\Specification\Content\ContentSpecification;
use PHPUnit\Framework\TestCase;

/**
 * @covers \eZ\Publish\SPI\Specification\Content\ContentContainerSpecification
 */
final class ContentContainerSpecificationTest extends TestCase
{
    public function testConstructor(): void
    {
        $contentTypeSpecification = new ContentContainerSpecification();

        $this->assertInstanceOf(ContentSpecification::class, $contentTypeSpecification);
    }

    /**
     * @covers \eZ\Publish\SPI\Specification\Content\ContentContainerSpecification::isSatisfiedBy
     * @dataProvider providerForIsSatisfiedBy
     */
    public function testIsSatisfiedBy(
        bool $isContainer,
        bool $shouldBeSatisfied
    ): void {
        $contentContainerSpecification = new ContentContainerSpecification();

        $contentTypeMock = $this->getMockBuilder(ContentType::class)
            ->setConstructorArgs(
                [['isContainer' => $isContainer]]
            )
            ->getMockForAbstractClass();

        $contentMock = $this->createMock(Content::class);
        $contentMock->expects($this->once())
            ->method('getContentType')
            ->willReturn($contentTypeMock);

        $this->assertEquals(
            $contentContainerSpecification->isSatisfiedBy($contentMock),
            $shouldBeSatisfied
        );
    }

    public function providerForIsSatisfiedBy(): array
    {
        return [
            [true, true],
            [false, false],
        ];
    }
}
