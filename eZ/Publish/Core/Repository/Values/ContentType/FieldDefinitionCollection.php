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

    /**
     * Field definitions indexed by identifier.
     *
     * @var \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition[]
     */
    private $fieldDefinitionsByIdentifier;

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

    public function filter(Closure $p): FieldDefinitionCollectionInterface
    {
        return new self(array_filter($this->fieldDefinitions, $p));
    }

    public function filterByType(string $fieldTypeIdentifier): FieldDefinitionCollectionInterface
    {
        return $this->filter($this->getIsTypePredicate($fieldTypeIdentifier));
    }

    public function filterByGroup(string $fieldGroup): FieldDefinitionCollectionInterface
    {
        return $this->filter($this->getInGroupPredicate($fieldGroup));
    }

    public function map(Closure $p): array
    {
        return array_map($p, $this->fieldDefinitions);
    }

    public function all(Closure $p): bool
    {
        foreach ($this->fieldDefinitions as $fieldDefinition) {
            if (!$p($fieldDefinition)) {
                return false;
            }
        }

        return true;
    }

    public function any(Closure $p): bool
    {
        foreach ($this->fieldDefinitions as $fieldDefinition) {
            if ($p($fieldDefinition)) {
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

    public function partition(Closure $p): array
    {
        $matches = $noMatches = [];

        foreach ($this->fieldDefinitions as $fieldDefinition) {
            if ($p($fieldDefinition)) {
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

    public function offsetExists($offset)
    {
        return isset($this->fieldDefinitions[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->fieldDefinitions[$offset];
    }

    public function offsetSet($offset, $value)
    {
        throw new BadMethodCallException(self::class . ' is read-only!');
    }

    public function offsetUnset($offset)
    {
        throw new BadMethodCallException(self::class . ' is read-only!');
    }
}
