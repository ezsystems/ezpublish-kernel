<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Tests\Values\ContentType;

use Closure;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition as APIFieldDefinition;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinitionCollection;
use PHPUnit\Framework\TestCase;

final class FieldDefinitionCollectionTest extends TestCase
{
    public function testGet(): void
    {
        list($a, $b, $c) = $this->createFieldDefinitions('A', 'B', 'C');

        $collection = new FieldDefinitionCollection([$a, $b, $c]);

        $this->assertEquals($a, $collection->get('A'));
        $this->assertEquals($b, $collection->get('B'));
        $this->assertEquals($c, $collection->get('C'));
    }

    public function testGetReturnsNullForNonExistingFieldDefinition(): void
    {
        $collection = new FieldDefinitionCollection(
            $this->createFieldDefinitions('A', 'B', 'C')
        );

        $this->assertNull($collection->get('Z'));
    }

    public function testHasReturnTrueForExistingFieldDefinition(): void
    {
        $collection = new FieldDefinitionCollection(
            $this->createFieldDefinitions('A', 'B', 'C')
        );

        $this->assertTrue($collection->has('A'));
        $this->assertTrue($collection->has('B'));
        $this->assertTrue($collection->has('C'));
    }

    public function testHasReturnFalseForNonExistingFieldDefinition(): void
    {
        $collection = new FieldDefinitionCollection(
            $this->createFieldDefinitions('A', 'B', 'C')
        );

        $this->assertFalse($collection->has('Z'));
    }

    public function testIsEmptyReturnsTrueForEmptyCollection(): void
    {
        $collection = new FieldDefinitionCollection();

        $this->assertTrue($collection->isEmpty());
    }

    public function testIsEmptyReturnsFalseForNonEmptyCollection(): void
    {
        $collection = new FieldDefinitionCollection([
            $this->createFieldDefinition('Example'),
        ]);

        $this->assertFalse($collection->isEmpty());
    }

    public function testFirstReturnsNullForEmptyCollection(): void
    {
        $collection = new FieldDefinitionCollection();

        $this->assertNull($collection->first());
    }

    public function testFirstReturnsFieldDefinitionForNonEmptyCollection(): void
    {
        list($a, $b, $c) = $this->createFieldDefinitions('A', 'B', 'C');

        $collection = new FieldDefinitionCollection([$a, $b, $c]);

        $this->assertEquals($a, $collection->first());
    }

    public function testLastReturnsFieldDefinitionForNonEmptyCollection(): void
    {
        list($a, $b, $c) = $this->createFieldDefinitions('A', 'B', 'C');

        $collection = new FieldDefinitionCollection([$a, $b, $c]);

        $this->assertEquals($c, $collection->last());
    }

    public function testLastReturnsNullForEmptyCollection(): void
    {
        $collection = new FieldDefinitionCollection();

        $this->assertNull($collection->last());
    }

    public function testFirstAndLastAreEqualForCollectionWithOneElement(): void
    {
        $fieldDefinition = $this->createFieldDefinition('Example');

        $collection = new FieldDefinitionCollection([$fieldDefinition]);

        $this->assertEquals($fieldDefinition, $collection->first());
        $this->assertEquals($fieldDefinition, $collection->last());
    }

    public function testCountForNonEmptyCollection(): void
    {
        list($a, $b, $c) = $this->createFieldDefinitions('A', 'B', 'C');

        $collection = new FieldDefinitionCollection([$a, $b, $c]);

        $this->assertEquals(3, $collection->count());
    }

    public function testCountReturnsZeroForEmptyCollection(): void
    {
        $collection = new FieldDefinitionCollection();

        $this->assertEquals(0, $collection->count());
    }

    public function testMap(): void
    {
        $collection = new FieldDefinitionCollection($this->createFieldDefinitions('A', 'B', 'C'));

        $closure = static function (FieldDefinition $fieldDefinition): string {
            return strtolower($fieldDefinition->identifier);
        };

        $this->assertEquals(['a', 'b', 'c'], $collection->map($closure));
    }

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

    public function testFilterByType(): void
    {
        list($a, $b, $c) = $this->createFieldDefinitionsWith('fieldTypeIdentifier', ['ezstring', 'ezstring', 'ezimage']);

        $collection = new FieldDefinitionCollection([$a, $b, $c]);

        $this->assertEquals(
            new FieldDefinitionCollection([$a, $b]),
            $collection->filterByType('ezstring')
        );
    }

    public function filterByGroup(): void
    {
        list($a, $b, $c) = $this->createFieldDefinitionsWith('fieldGroup', ['default', 'default', 'seo']);

        $collection = new FieldDefinitionCollection([$a, $b, $c]);

        $this->assertEquals(
            new FieldDefinitionCollection([$c]),
            $collection->filterByType('seo')
        );
    }

    public function testAll(): void
    {
        $collection = new FieldDefinitionCollection($this->createFieldDefinitions('A', 'B', 'C'));

        $this->assertTrue($collection->all($this->getIdentifierIsEqualPredicate('A', 'B', 'C')));
        $this->assertFalse($collection->all($this->getIdentifierIsEqualPredicate('A')));

        $this->assertTrue($collection->all($this->getTautology()));
        $this->assertFalse($collection->all($this->getContraction()));
    }

    public function testAny(): void
    {
        $collection = new FieldDefinitionCollection($this->createFieldDefinitions('A', 'B', 'C'));

        $this->assertTrue($collection->any($this->getIdentifierIsEqualPredicate('A')));
        $this->assertFalse($collection->any($this->getIdentifierIsEqualPredicate('Z')));

        $this->assertTrue($collection->any($this->getTautology()));
        $this->assertFalse($collection->any($this->getContraction()));
    }

    public function testAnyOfType(): void
    {
        $collection = new FieldDefinitionCollection(
            $this->createFieldDefinitionsWith('fieldTypeIdentifier', ['ezstring', 'ezstring', 'ezimage'])
        );

        $this->assertTrue($collection->anyOfType('ezstring'));
        $this->assertFalse($collection->anyOfType('ezrichtext'));
    }

    public function testAnyInGroup(): void
    {
        $collection = new FieldDefinitionCollection(
            $this->createFieldDefinitionsWith('fieldGroup', ['default', 'default', 'seo'])
        );

        $this->assertTrue($collection->anyInGroup('default'));
        $this->assertFalse($collection->anyInGroup('comments'));
    }

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
    public function getIdentifierIsEqualPredicate(string ...$identifiers): Closure
    {
        return static function (APIFieldDefinition $fieldDefinition) use ($identifiers): bool {
            return in_array($fieldDefinition->identifier, $identifiers);
        };
    }

    /**
     * Returns predicate with is always true.
     */
    private function getTautology(): Closure
    {
        return static function (APIFieldDefinition $fieldDefinition): bool {
            return true;
        };
    }

    /**
     * Returns predicate with is always false.
     */
    private function getContraction(): Closure
    {
        return static function (APIFieldDefinition $fieldDefinition): bool {
            return false;
        };
    }
}
