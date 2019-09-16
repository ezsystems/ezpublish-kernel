<?php

declare(strict_types=1);

namespace eZ\Publish\Core\LocationReference\NamedReferences;

use ArrayIterator;
use Countable;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use IteratorAggregate;
use Traversable;

final class NamedReferencesCollection implements IteratorAggregate, Countable
{
    /** @var string[] */
    private $references;

    public function __construct(array $references)
    {
        $this->references = $references;
    }

    public function getReference(string $name): string
    {
        if (!$this->hasReference($name)) {
            throw new NotFoundException('named reference', $name);
        }

        return $this->references[$name];
    }

    public function hasReference(string $name): bool
    {
        return isset($this->references[$name]);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->references);
    }

    public function count(): int
    {
        return count($this->references);
    }
}
