<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Values;

use ArrayAccess;
use Countable;
use Generator;
use Iterator;

/**
 * Class GeneratorCollection, allows for lazy loaded arrays using Generators.
 *
 * This collection class takes Generator as argument, and allows user to threat it like any kind of iterable. It will take
 * care about storing the values returned from generator so user can iterate over it several times, it also allows for
 * count usage and offset usage.
 */
class GeneratorCollection implements Iterator, ArrayAccess, Countable
{
    private $array = [];
    private $generator;
    private $generatorUsed = false;

    /**
     * GeneratorCollection constructor.
     *
     * @param Generator $generator
     */
    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    public function rewind()
    {
        if (isset($this->generator)) {
            // If generator has already been used, given generators can't start over we will need to load all
            if ($this->generatorUsed) {
                $this->loadAll();
            } else {
                $this->generatorUsed = true;
                $this->generator->rewind();
            }
        }

        reset($this->array);
    }

    public function current()
    {
        if (isset($this->generator)) {
            // As the generator is iterated, set the values on array here so we can also iterate on it later
            return $this->array[$this->generator->key()] = $this->generator->current();
        }

        return current($this->array);
    }

    public function key()
    {
        if (isset($this->generator)) {
            return $this->generator->key();
        }

        return key($this->array);
    }

    public function next()
    {
        if (isset($this->generator)) {
            $this->generatorUsed = true;
            $this->generator->next();
        }

        next($this->array);
    }

    public function valid()
    {
        if (isset($this->generator)) {
            if ($this->generator->valid()) {
                return true;
            }

            // If not valid we are at the end of the array, for future iteration we'll unset this and use array only
            unset($this->generator);
        }

        return key($this->array) !== null;
    }

    public function offsetExists($offset)
    {
        if (isset($this->generator)) {
            $this->loadAll();
        }

        return isset($this->array[$offset]);
    }

    public function offsetGet($offset)
    {
        if (isset($this->generator)) {
            $this->loadAll();
        }

        return $this->array[$offset];
    }

    public function offsetSet($offset, $value)
    {
        // Read only collection, so not handled here (for making it writable it would have to loadAll first if needed)
    }

    public function offsetUnset($offset)
    {
        // Read only collection, so not handled here (for making it writable it would have to loadAll first if needed)
    }

    public function count()
    {
        if (isset($this->generator)) {
            $this->loadAll();
        }
        return count($this->array);
    }

    /**
     * Loads the whole/remaining items in the Generator into internal array.
     */
    private function loadAll()
    {
        // As we might have loaded some elements already we use array union to retain everything
        $this->array = iterator_to_array($this->generator) + $this->array;
        unset($this->generator);
    }
}
