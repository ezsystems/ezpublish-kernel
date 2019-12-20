<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Tests\Values\ContentType;

use Closure;
use eZ\Publish\API\Repository\Exceptions\OutOfBoundsException;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition as APIFieldDefinition;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinitionCollection;
use PHPUnit\Framework\TestCase;

final class FieldDefinitionCollectionTest extends TestCase
{
    /**
     * @covers \eZ\Publish\Core\Repository\Values\ContentType\FieldDefinitionCollection::get
     */
    public function testGet(): void
    {
        list($a, $b, $c) = $this->createFieldDefinitions('A', 'B', 'C');

        $collection = new FieldDefinitionCollection([$a, $b, $c]);

        $this->assertEquals($a, $collection->get('A'));
        $this->assertEquals($b, $collection->get('B'));
        $this->assertEquals($c, $collection->get('C'));
    }

    /**
     * @covers \eZ\Publish\Core\Repository\Values\ContentType\FieldDefinitionCollection::get
     */
    public function testGetThrowsOutOfBoundsExceptionForNonExistingFieldDefinition(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage("Field Definition Collection does not contain element with identifier 'Z'");

        $collection = new FieldDefinitionCollection(
            $this->createFieldDefinitions('A', 'B', 'C')
        );

        $collection->get('Z');
    }

    /**
     * @covers \eZ\Publish\Core\Repository\Values\ContentType\FieldDefinitionCollection::has
     */
    public function testHasReturnTrueForExistingFieldDefinition(): void
    {
        $collection = new FieldDefinitionCollection(
            $this->createFieldDefinitions('A', 'B', 'C')
        );

        $this->assertTrue($collection->has('A'));
        $this->assertTrue($collection->has('B'));
        $this->assertTrue($collection->has('C'));
    }

    /**
     * @covers \eZ\Publish\Core\Repository\Values\ContentType\FieldDefinitionCollection::has
     */
    public function testHasReturnFalseForNonExistingFieldDefinition(): void
    {
        $collection = new FieldDefinitionCollection(
            $this->createFieldDefinitions('A', 'B', 'C')
        );

        $this->assertFalse($collection->has('Z'));
    }

    /**
     * @covers \eZ\Publish\Core\Repository\Values\ContentType\FieldDefinitionCollection::isEmpty
     */
    public function testIsEmptyReturnsTrueForEmptyCollection(): void
    {
        $collection = new FieldDefinitionCollection();

        $this->assertTrue($collection->isEmpty());
    }

    /**
     * @covers \eZ\Publish\Core\Repository\Values\ContentType\FieldDefinitionCollection::isEmpty
     */
    public function testIsEmptyReturnsFalseForNonEmptyCollection(): void
    {
        $collection = new FieldDefinitionCollection([
            $this->createFieldDefinition('Example'),
        ]);

        $this->assertFalse($collection->isEmpty());
    }

    /**
     * @covers \eZ\Publish\Core\Repository\Values\ContentType\FieldDefinitionCollection::first
     */
    public function testFirstThrowsOutOfBoundsExceptionForEmptyCollection(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Field Definition Collection is empty');

        $collection = new FieldDefinitionCollection();
        $collection->first();
    }

    /**
     * @covers \eZ\Publish\Core\Repository\Values\ContentType\FieldDefinitionCollection::first
     */
    public function testFirstReturnsFieldDefinitionForNonEmptyCollection(): void
    {
        list($a, $b, $c) = $this->createFieldDefinitions('A', 'B', 'C');

        $collection = new FieldDefinitionCollection([$a, $b, $c]);

        $this->assertEquals($a, $collection->first());
    }

    /**
     * @covers \eZ\Publish\Core\Repository\Values\ContentType\FieldDefinitionCollection::last
     */
    public function testLastReturnsFieldDefinitionForNonEmptyCollection(): void
    {
        list($a, $b, $c) = $this->createFieldDefinitions('A', 'B', 'C');

        $collection = new FieldDefinitionCollection([$a, $b, $c]);

        $this->assertEquals($c, $collection->last());
    }

    /**
     * @covers \eZ\Publish\Core\Repository\Values\ContentType\FieldDefinitionCollection::last
     */
    public function testLastThrowsOutOfBoundsExceptionForEmptyCollection(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('Field Definition Collection is empty');

        $collection = new FieldDefinitionCollection();
        $collection->last();
    }

    /**
     * @covers \eZ\Publish\Core\Repository\Values\ContentType\FieldDefinitionCollection::first
     * @covers \eZ\Publish\Core\Repository\Values\ContentType\FieldDefinitionCollection::last
     */
    public function testFirstAndLastAreEqualForCollectionWithOneElement(): void
    {
        $fieldDefinition = $this->createFieldDefinition('Example');

        $collection = new FieldDefinitionCollection([$fieldDefinition]);

        $this->assertEquals($fieldDefinition, $collection->first());
        $this->assertEquals($fieldDefinition, $collection->last());
    }

    /**
     * @covers \eZ\Publish\Core\Repository\Values\ContentType\FieldDefinitionCollection::count
     */
    public function testCountForNonEmptyCollection(): void
    {
        list($a, $b, $c) = $this->createFieldDefinitions('A', 'B', 'C');

        $collection = new FieldDefinitionCollection([$a, $b, $c]);

        $this->assertEquals(3, $collection->count());
    }

    /**
     * @covers \eZ\Publish\Core\Repository\Values\ContentType\FieldDefinitionCollection::count
     */
    public function testCountReturnsZeroForEmptyCollection(): void
    {
        $collection = new FieldDefinitionCollection();

        $this->assertEquals(0, $collection->count());
    }

    /**
     * @covers \eZ\Publish\Core\Repository\Values\ContentType\FieldDefinitionCollection::map
     */
    public function testMap(): void
    {
        $collection = new FieldDefinitionCollection($this->createFieldDefinitions('A', 'B', 'C'));

        $closure = static function (FieldDefinition $fieldDefinition): string {
            return strtolower($fieldDefinition->identifier);
        };

        $this->assertEquals(['a', 'b', 'c'], $collection->map($closure));
    }

    /**
     * @covers \eZ\Publish\Core\Repository\Values\ContentType\FieldDefinitionCollection::filter
     */
    public function testFilter(): void
    {
        list($a, $b, $c) = $this->createFieldDefinitions('A', 'B', 'C');

        $collection = new FieldDefinitionCollection([$a, $b, $c]);

        $this->assertEquals(
            new FieldDefinitionCollection([$a, $c]),
            $collection->filter($this->getIdentifierIsEqualPredicate('A', 'C'))
        );

        $this->assertEquals(
            new FieldDefinitionCollection(),
            $collection->filter($this->getContraction())
        );

        $this->assertEquals(
            new FieldDefinitionCollection([$a, $b, $c]),
            $collection->filter($this->getTautology())
        );
    }

    /**
     * @covers \eZ\Publish\Core\Repository\Values\ContentType\FieldDefinitionCollection::filterByType
     */
    public function testFilterByType(): void
    {
        list($a, $b, $c) = $this->createFieldDefinitionsWith('fieldTypeIdentifier', ['ezstring', 'ezstring', 'ezimage']);

        $collection = new FieldDefinitionCollection([$a, $b, $c]);

        $this->assertEquals(
            new FieldDefinitionCollection([$a, $b]),
            $collection->filterByType('ezstring')
        );
    }

    /**
     * @covers \eZ\Publish\Core\Repository\Values\ContentType\FieldDefinitionCollection::filterByGroup
     */
    public function filterByGroup(): void
    {
        list($a, $b, $c) = $this->createFieldDefinitionsWith('fieldGroup', ['default', 'default', 'seo']);

        $collection = new FieldDefinitionCollection([$a, $b, $c]);

        $this->assertEquals(
            new FieldDefinitionCollection([$c]),
            $collection->filterByType('seo')
        );
    }

    /**
     * @covers \eZ\Publish\Core\Repository\Values\ContentType\FieldDefinitionCollection::all
     */
    public function testAll(): void
    {
        $collection = new FieldDefinitionCollection($this->createFieldDefinitions('A', 'B', 'C'));

        $this->assertTrue($collection->all($this->getIdentifierIsEqualPredicate('A', 'B', 'C')));
        $this->assertFalse($collection->all($this->getIdentifierIsEqualPredicate('A')));

        $this->assertTrue($collection->all($this->getTautology()));
        $this->assertFalse($collection->all($this->getContraction()));
    }

    /**
     * @covers \eZ\Publish\Core\Repository\Values\ContentType\FieldDefinitionCollection::any
     */
    public function testAny(): void
    {
        $collection = new FieldDefinitionCollection($this->createFieldDefinitions('A', 'B', 'C'));

        $this->assertTrue($collection->any($this->getIdentifierIsEqualPredicate('A')));
        $this->assertFalse($collection->any($this->getIdentifierIsEqualPredicate('Z')));

        $this->assertTrue($collection->any($this->getTautology()));
        $this->assertFalse($collection->any($this->getContraction()));
    }

    /**
     * @covers \eZ\Publish\Core\Repository\Values\ContentType\FieldDefinitionCollection::anyOfType
     */
    public function testAnyOfType(): void
    {
        $collection = new FieldDefinitionCollection(
            $this->createFieldDefinitionsWith('fieldTypeIdentifier', ['ezstring', 'ezstring', 'ezimage'])
        );

        $this->assertTrue($collection->anyOfType('ezstring'));
        $this->assertFalse($collection->anyOfType('ezrichtext'));
    }

    /**
     * @covers \eZ\Publish\Core\Repository\Values\ContentType\FieldDefinitionCollection::anyInGroup
     */
    public function testAnyInGroup(): void
    {
        $collection = new FieldDefinitionCollection(
            $this->createFieldDefinitionsWith('fieldGroup', ['default', 'default', 'seo'])
        );

        $this->assertTrue($collection->anyInGroup('default'));
        $this->assertFalse($collection->anyInGroup('comments'));
    }

    /**
     * @covers \eZ\Publish\Core\Repository\Values\ContentType\FieldDefinitionCollection::partition
     */
    public function testPartition(): void
    {
        list($a, $b, $c) = $this->createFieldDefinitions('A', 'B', 'C');

        $collection = new FieldDefinitionCollection([$a, $b, $c]);

        $this->assertEquals(
            [
                new FieldDefinitionCollection([$a, $c]),
                new FieldDefinitionCollection([$b]),
            ],
            $collection->partition($this->getIdentifierIsEqualPredicate('A', 'C'))
        );

        $this->assertEquals(
            [
                new FieldDefinitionCollection([$a, $b, $c]),
                new FieldDefinitionCollection(),
            ],
            $collection->partition($this->getTautology())
        );

        $this->assertEquals(
            [
                new FieldDefinitionCollection(),
                new FieldDefinitionCollection([$a, $b, $c]),
            ],
            $collection->partition($this->getContraction())
        );
    }

    /**
     * @covers \eZ\Publish\Core\Repository\Values\ContentType\FieldDefinitionCollection::toArray
     */
    public function testToArray(): void
    {
        $fieldDefinitions = $this->createFieldDefinitions('A', 'B', 'C');

        $collection = new FieldDefinitionCollection($fieldDefinitions);

        $this->assertEquals($fieldDefinitions, $collection->toArray());
    }

    private function createFieldDefinitions(string ...$identifiers): array
    {
        return $this->createFieldDefinitionsWith('identifier', $identifiers);
    }

    private function createFieldDefinitionsWith(string $property, array $values): array
    {
        return array_map(function (string $identifier) use ($property): APIFieldDefinition {
            return $this->createFieldDefinition($identifier, $property);
        }, $values);
    }

    private function createFieldDefinition(string $identifier, string $property = 'identifier'): APIFieldDefinition
    {
        return new FieldDefinition([$property => $identifier]);
    }

    /**
     * Returns predicate which test if field definition identifier belongs to given set.
     */
    private function getIdentifierIsEqualPredicate(string ...$identifiers): Closure
    {
        return static function (APIFieldDefinition $fieldDefinition) use ($identifiers): bool {
            return in_array($fieldDefinition->identifier, $identifiers);
        };
    }

    /**
     * Returns a predicate which is always true.
     */
    private function getTautology(): Closure
    {
        return static function (APIFieldDefinition $fieldDefinition): bool {
            return true;
        };
    }

    /**
     * Returns a predicate which is always false.
     */
    private function getContraction(): Closure
    {
        return static function (APIFieldDefinition $fieldDefinition): bool {
            return false;
        };
    }
}
