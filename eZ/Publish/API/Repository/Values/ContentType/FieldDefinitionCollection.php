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
     * @throws \eZ\Publish\API\Repository\Exceptions\OutOfBoundsException
     */
    public function get(string $fieldDefinitionIdentifier): FieldDefinition;

    /**
     * This method returns true if the field definition for the given identifier exists.
     */
    public function has(string $fieldDefinitionIdentifier): bool;

    /**
     * Return first element of collection.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\OutOfBoundsException
     */
    public function first(): FieldDefinition;

    /**
     * Return last element of collection.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\OutOfBoundsException
     */
    public function last(): FieldDefinition;

    /**
     * Checks whether the collection is empty (contains no elements).
     *
     * @return bool TRUE if the collection is empty, FALSE otherwise.
     */
    public function isEmpty(): bool;

    /**
     * Returns all the elements of this collection that satisfy the predicate p.
     * The order of the elements is preserved.
     */
    public function filter(Closure $predicate): FieldDefinitionCollection;

    /**
     * Returns field definitions with given field type identifier.
     */
    public function filterByType(string $fieldTypeIdentifier): FieldDefinitionCollection;

    /**
     * Returns field definitions with given group.
     */
    public function filterByGroup(string $fieldGroup): FieldDefinitionCollection;

    /**
     * Applies the given function to each element in the collection and returns
     * a new collection with the elements returned by the function.
     */
    public function map(Closure $predicate): array;

    /**
     * Tests whether the given predicate holds for all elements of this collection.
     */
    public function all(Closure $predicate): bool;

    /**
     * Tests for the existence of an element that satisfies the given predicate.
     */
    public function any(Closure $predicate): bool;

    /**
     * Tests for the existence of an field definition with given field type identifier.
     */
    public function anyOfType(string $fieldTypeIdentifier): bool;

    /**
     * Tests for the existence of an field definition in given field group.
     */
    public function anyInGroup(string $fieldGroup): bool;

    /**
     * Partitions this collection in two collections according to a predicate.
     *
     * Result is an array with two elements. The first element contains the collection
     * of elements where the predicate returned TRUE, the second element
     * contains the collection of elements where the predicate returned FALSE.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCollection[]
     */
    public function partition(Closure $predicate): array;

    /**
     * Gets a native PHP array representation of the collection.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[]
     */
    public function toArray(): array;
}
