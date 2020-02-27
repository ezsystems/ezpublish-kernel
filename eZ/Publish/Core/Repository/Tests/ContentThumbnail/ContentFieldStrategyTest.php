<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Tests\ContentThumbnail;

use ArrayIterator;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\Thumbnail;
use eZ\Publish\Core\Repository\Strategy\ContentThumbnail\Field\ContentFieldStrategy;
use eZ\Publish\SPI\Repository\Strategy\ContentThumbnail\Field\FieldTypeBasedThumbnailStrategy;
use PHPUnit\Framework\TestCase;

class ContentFieldStrategyTest extends TestCase
{
    private function getFieldTypeBasedThumbnailStrategy(string $fieldTypeIdentifier): FieldTypeBasedThumbnailStrategy
    {
        return new class($fieldTypeIdentifier) implements FieldTypeBasedThumbnailStrategy {
            /** @var string */
            private $fieldTypeIdentifier;

            public function __construct(string $fieldTypeIdentifier)
            {
                $this->fieldTypeIdentifier = $fieldTypeIdentifier;
            }

            public function getFieldTypeIdentifier(): string
            {
                return $this->fieldTypeIdentifier;
            }

            public function getThumbnail(Field $field): ?Thumbnail
            {
                return new Thumbnail([
                    'resource' => $field->value,
                ]);
            }
        };
    }

    public function testHasStrategy(): void
    {
        $contentFieldStrategy = new ContentFieldStrategy(new ArrayIterator([
            $this->getFieldTypeBasedThumbnailStrategy('example'),
        ]));

        $this->assertTrue($contentFieldStrategy->hasStrategy('example'));
        $this->assertFalse($contentFieldStrategy->hasStrategy('something_else'));
    }

    public function testAddStrategy(): void
    {
        $contentFieldStrategy = new ContentFieldStrategy(new ArrayIterator());

        $this->assertFalse($contentFieldStrategy->hasStrategy('example'));

        $contentFieldStrategy->addStrategy('example', $this->getFieldTypeBasedThumbnailStrategy('example'));

        $this->assertTrue($contentFieldStrategy->hasStrategy('example'));
    }

    public function testSetStrategies(): void
    {
        $contentFieldStrategy = new ContentFieldStrategy(new ArrayIterator([
            $this->getFieldTypeBasedThumbnailStrategy('previous'),
        ]));

        $this->assertTrue($contentFieldStrategy->hasStrategy('previous'));

        $contentFieldStrategy->setStrategies([
            $this->getFieldTypeBasedThumbnailStrategy('new-example-1'),
            $this->getFieldTypeBasedThumbnailStrategy('new-example-2'),
        ]);

        $this->assertFalse($contentFieldStrategy->hasStrategy('previous'));
        $this->assertTrue($contentFieldStrategy->hasStrategy('new-example-1'));
        $this->assertTrue($contentFieldStrategy->hasStrategy('new-example-2'));
    }

    public function testGetThumbnailFound(): void
    {
        $contentFieldStrategy = new ContentFieldStrategy(new ArrayIterator([
            $this->getFieldTypeBasedThumbnailStrategy('example'),
        ]));

        $field = new Field([
            'fieldTypeIdentifier' => 'example',
            'value' => 'example-value',
        ]);

        $thumbnail = $contentFieldStrategy->getThumbnail($field);

        $this->assertInstanceOf(Thumbnail::class, $thumbnail);
        $this->assertEquals('example-value', $thumbnail->resource);
    }

    public function testGetThumbnailNotFound(): void
    {
        $contentFieldStrategy = new ContentFieldStrategy(new ArrayIterator([
            $this->getFieldTypeBasedThumbnailStrategy('something-else'),
        ]));

        $field = new Field([
            'fieldTypeIdentifier' => 'example',
            'value' => 'example-value',
        ]);

        $this->expectException(NotFoundException::class);

        $contentFieldStrategy->getThumbnail($field);
    }
}
