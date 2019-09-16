<?php

declare(strict_types=1);

namespace eZ\Publish\Core\LocationReference\Tests\NamedReferences;

use eZ\Publish\Core\LocationReference\NamedReferences\NamedReferencesCollection;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use PHPUnit\Framework\TestCase;

final class NamedReferencesCollectionTest extends TestCase
{
    private const EXAMPLE_REFERENCES = [
        'images' => 'remote_id("IMAGES")',
        'videos' => 'remote_id("VIDEOS")',
        'other' => 'remote_id("OTHER")',
    ];

    public function testGetReference(): void
    {
        $collection = new NamedReferencesCollection(self::EXAMPLE_REFERENCES);

        $this->assertEquals(
            self::EXAMPLE_REFERENCES['images'],
            $collection->getReference('images')
        );
    }

    public function testGetReferenceThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);

        $collection = new NamedReferencesCollection(self::EXAMPLE_REFERENCES);
        $collection->getReference('root');
    }

    public function testHasReference(): void
    {
        $collection = new NamedReferencesCollection(self::EXAMPLE_REFERENCES);

        $this->assertTrue($collection->hasReference('images'));
        $this->assertFalse($collection->hasReference('root'));
    }

    public function testCount(): void
    {
        $this->assertEquals(
            count(self::EXAMPLE_REFERENCES),
            count(new NamedReferencesCollection(self::EXAMPLE_REFERENCES))
        );
    }

    public function testCountEmptyCollection(): void
    {
        $this->assertEquals(0, count(new NamedReferencesCollection([])));
    }
}
