<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\ContentType;

use ArrayAccess;
use Closure;
use Countable;
use IteratorAggregate;

interface FieldDefinitionCollection extends Countable, IteratorAggregate, ArrayAccess
{
    /**
     * This method returns the field definition for the given identifier.
     *
     * @param string $fieldDefinitionIdentifier
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition|null
     */
    public function get(string $fieldDefinitionIdentifier): ?FieldDefinition;

    /**
     * This method returns true if the field definition for the given identifier exists.
     *
     * @param string $fieldDefinitionIdentifier
     *
     * @return bool
     */
    public function has(string $fieldDefinitionIdentifier): bool;

    /**
     * Return first element of collection.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition|null
     */
    public function first(): ?FieldDefinition;

    /**
     * Return last element of collection.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition|null
     */
    public function last(): ?FieldDefinition;

    /**
     * Checks whether the collection is empty (contains no elements).
     *
     * @return bool TRUE if the collection is empty, FALSE otherwise.
     */
    public function isEmpty(): bool;

    /**
     * Returns all the elements of this collection that satisfy the predicate p.
     * The order of the elements is preserved.
     *
     * @param Closure $p The predicate used for filtering.
     *
     * @return FieldDefinitionCollection A collection with the results of the filter operation.
     */
    public function filter(Closure $p): FieldDefinitionCollection;

    /**
     * Returns field definitions with given field type identifier.
     *
     * @param string $fieldTypeIdentifier
     *
     * @return FieldDefinitionCollection A collection with the results of the filter operation.
     */
    public function filterByType(string $fieldTypeIdentifier): FieldDefinitionCollection;

    /**
     * Returns field definitions with given group.
     *
     * @param string $fieldGroup
     *
     * @return FieldDefinitionCollection A collection with the results of the filter operation.
     */
    public function filterByGroup(string $fieldGroup): FieldDefinitionCollection;

    /**
     * Applies the given function to each element in the collection and returns
     * a new collection with the elements returned by the function.
     *
     * @param Closure $p The predicate.
     *
     * @return array
     */
    public function map(Closure $p): array;

    /**
     * Tests whether the given predicate p holds for all elements of this collection.
     *
     * @param Closure $p The predicate.
     *
     * @return bool TRUE, if the predicate yields TRUE for all elements, FALSE otherwise.
     */
    public function all(Closure $p): bool;

    /**
     * Tests for the existence of an element that satisfies the given predicate.
     *
     * @param Closure $p The predicate.
     *
     * @return bool TRUE if the predicate is TRUE for at least one element, FALSE otherwise.
     */
    public function any(Closure $p): bool;

    /**
     * Tests for the existence of an field definition with given field type identifier.
     *
     * @param string $fieldTypeIdentifier
     *
     * @return bool TRUE if the predicate is TRUE for at least one field definition, FALSE otherwise.
     */
    public function anyOfType(string $fieldTypeIdentifier): bool;

    /**
     * Tests for the existence of an field definition in given field group.
     *
     * @param string $fieldGroup
     *
     * @return bool TRUE if the predicate is TRUE for at least one field definition, FALSE otherwise.
     */
    public function anyInGroup(string $fieldGroup): bool;

    /**
     * Partitions this collection in two collections according to a predicate.
     *
     * @param Closure $p The predicate on which to partition.
     *
     * @return FieldDefinitionCollection[] An array with two elements. The first element contains the collection
     *                      of elements where the predicate returned TRUE, the second element
     *                      contains the collection of elements where the predicate returned FALSE.
     */
    public function partition(Closure $p): array;

    /**
     * Gets a native PHP array representation of the collection.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[]
     */
    public function toArray(): array;
}
