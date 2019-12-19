<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Values\ContentType;

use ArrayIterator;
use BadMethodCallException;
use Closure;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCollection as FieldDefinitionCollectionInterface;
use Iterator;

final class FieldDefinitionCollection implements FieldDefinitionCollectionInterface
{
    /** @var \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[] */
    private $fieldDefinitions;

    /** @var \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[] */
    private $fieldDefinitionsByIdentifier;

    /**
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[]
     */
    public function __construct(iterable $fieldDefinitions = [])
    {
        $this->fieldDefinitions = [];
        $this->fieldDefinitionsByIdentifier = [];

        foreach ($fieldDefinitions as $fieldDefinition) {
            $this->fieldDefinitions[] = $fieldDefinition;
            $this->fieldDefinitionsByIdentifier[$fieldDefinition->identifier] = $fieldDefinition;
        }
    }

    public function get(string $fieldDefinitionIdentifier): ?FieldDefinition
    {
        return $this->fieldDefinitionsByIdentifier[$fieldDefinitionIdentifier] ?? null;
    }

    public function has(string $fieldDefinitionIdentifier): bool
    {
        return array_key_exists($fieldDefinitionIdentifier, $this->fieldDefinitionsByIdentifier);
    }

    public function first(): ?FieldDefinition
    {
        if (($result = reset($this->fieldDefinitions)) !== false) {
            return $result;
        }

        return null;
    }

    public function last(): ?FieldDefinition
    {
        if (($result = end($this->fieldDefinitions)) !== false) {
            return $result;
        }

        return null;
    }

    public function isEmpty(): bool
    {
        return empty($this->fieldDefinitions);
    }

    public function filter(Closure $predicate): FieldDefinitionCollectionInterface
    {
        return new self(array_filter($this->fieldDefinitions, $predicate));
    }

    public function filterByType(string $fieldTypeIdentifier): FieldDefinitionCollectionInterface
    {
        return $this->filter($this->getIsTypePredicate($fieldTypeIdentifier));
    }

    public function filterByGroup(string $fieldGroup): FieldDefinitionCollectionInterface
    {
        return $this->filter($this->getInGroupPredicate($fieldGroup));
    }

    public function map(Closure $predicate): array
    {
        return array_map($predicate, $this->fieldDefinitions);
    }

    public function all(Closure $predicate): bool
    {
        foreach ($this->fieldDefinitions as $fieldDefinition) {
            if (!$predicate($fieldDefinition)) {
                return false;
            }
        }

        return true;
    }

    public function any(Closure $predicate): bool
    {
        foreach ($this->fieldDefinitions as $fieldDefinition) {
            if ($predicate($fieldDefinition)) {
                return true;
            }
        }

        return false;
    }

    public function anyOfType(string $fieldTypeIdentifier): bool
    {
        return $this->any($this->getIsTypePredicate($fieldTypeIdentifier));
    }

    public function anyInGroup(string $fieldGroup): bool
    {
        return $this->any($this->getInGroupPredicate($fieldGroup));
    }

    public function partition(Closure $predicate): array
    {
        $matches = $noMatches = [];

        foreach ($this->fieldDefinitions as $fieldDefinition) {
            if ($predicate($fieldDefinition)) {
                $matches[] = $fieldDefinition;
            } else {
                $noMatches[] = $fieldDefinition;
            }
        }

        return [new self($matches), new self($noMatches)];
    }

    public function count(): int
    {
        return count($this->fieldDefinitions);
    }

    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->fieldDefinitions);
    }

    public function toArray(): array
    {
        return $this->fieldDefinitions;
    }

    private function getIsTypePredicate(string $fieldTypeIdentifier): Closure
    {
        return static function (FieldDefinition $fieldDefinition) use ($fieldTypeIdentifier) {
            return $fieldDefinition->fieldTypeIdentifier === $fieldTypeIdentifier;
        };
    }

    private function getInGroupPredicate(string $fieldGroup): Closure
    {
        return static function (FieldDefinition $fieldDefinition) use ($fieldGroup) {
            return $fieldDefinition->fieldGroup === $fieldGroup;
        };
    }

    public function offsetExists($offset): bool
    {
        return isset($this->fieldDefinitions[$offset]);
    }

    public function offsetGet($offset): FieldDefinition
    {
        return $this->fieldDefinitions[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        throw new BadMethodCallException(self::class . ' is read-only!');
    }

    public function offsetUnset($offset): void
    {
        throw new BadMethodCallException(self::class . ' is read-only!');
    }
}
