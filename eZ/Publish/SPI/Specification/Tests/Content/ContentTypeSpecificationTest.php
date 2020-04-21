<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Specification\Tests\Content;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\SPI\Specification\Content\ContentSpecification;
use eZ\Publish\SPI\Specification\Content\ContentTypeSpecification;
use PHPUnit\Framework\TestCase;

final class ContentTypeSpecificationTest extends TestCase
{
    private const EXISTING_CONTENT_TYPE_IDENTIFIER = 'article';
    private const NOT_EXISTING_CONTENT_TYPE_IDENTIFIER = 'Some-Not-Existing-CT-Identifier';

    public function testConstructorWithExistingContentTypeIdentifier(): void
    {
        $contentTypeSpecification = new ContentTypeSpecification(
            self::EXISTING_CONTENT_TYPE_IDENTIFIER
        );

        $this->assertInstanceOf(ContentSpecification::class, $contentTypeSpecification);
    }

    public function testConstructorWithNotExistingContentTypeIdentifier(): void
    {
        $contentTypeSpecification = new ContentTypeSpecification(
            self::NOT_EXISTING_CONTENT_TYPE_IDENTIFIER
        );

        $this->assertInstanceOf(ContentSpecification::class, $contentTypeSpecification);
    }

    /**
     * @covers \eZ\Publish\SPI\Specification\Content\ContentTypeSpecification::isSatisfiedBy
     * @dataProvider providerForIsSatisfiedBy
     */
    public function testIsSatisfiedBy(
        string $contentTypeSpecificationIdentifier,
        string $contentTypeIdentifier,
        bool $shouldBeSatisfied
    ): void {
        $contentTypeSpecification = new ContentTypeSpecification(
            $contentTypeSpecificationIdentifier
        );

        $contentTypeMock = $this->getMockBuilder(ContentType::class)
            ->setConstructorArgs(
                [['identifier' => $contentTypeIdentifier]]
            )
            ->getMockForAbstractClass();

        $contentMock = $this->createMock(Content::class);
        $contentMock->expects($this->once())
            ->method('getContentType')
            ->willReturn($contentTypeMock);

        $this->assertEquals(
            $contentTypeSpecification->isSatisfiedBy($contentMock),
            $shouldBeSatisfied
        );
    }

    public function providerForIsSatisfiedBy(): array
    {
        return [
            [self::EXISTING_CONTENT_TYPE_IDENTIFIER, self::EXISTING_CONTENT_TYPE_IDENTIFIER, true],
            [self::NOT_EXISTING_CONTENT_TYPE_IDENTIFIER, self::EXISTING_CONTENT_TYPE_IDENTIFIER, false],
        ];
    }
}
