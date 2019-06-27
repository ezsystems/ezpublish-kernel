<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Values;

use Generator;

/**
 * Trait for proxies, covers all relevant magic methods and exposes private method to load object.
 *
 * Uses generator internally to be able to cleanly execute object load async on-demand.
 *
 * @internal Meant for internal use by Repository!
 */
trait GeneratorProxyTrait
{
    /**
     * Overload this for correct type hint on object type.
     *
     * @var object|null
     */
    protected $object;

    /**
     * Needs to be protected as value objects often define this as well.
     *
     * @var mixed
     */
    protected $id;

    /** @var Generator|null */
    private $generator;

    /**
     * GeneratorProxyTrait constructor.
     *
     * @param Generator $generator
     * @param mixed $id Object id to use for loading the object on demand.
     */
    public function __construct(Generator $generator, $id)
    {
        $this->generator = $generator;
        $this->id = $id;
    }

    public function __call($name, $arguments)
    {
        if ($this->object === null) {
            $this->loadObject();
        }

        return $this->object->$name(...$arguments);
    }

    public function __invoke(...$args)
    {
        if ($this->object === null) {
            $this->loadObject();
        }

        return ($this->object)(...$args);
    }

    public function __get($name)
    {
        if ($name === 'id') {
            return $this->id;
        }

        if ($this->object === null) {
            $this->loadObject();
        }

        return $this->object->$name;
    }

    public function __isset($name)
    {
        if ($name === 'id') {
            return true;
        }

        if ($this->object === null) {
            $this->loadObject();
        }

        return isset($this->object->$name);
    }

    public function __unset($name)
    {
        if ($this->object === null) {
            $this->loadObject();
        }

        unset($this->object->$name);
    }

    public function __set($name, $value)
    {
        if ($this->object === null) {
            $this->loadObject();
        }

        $this->object->$name = $value;
    }

    public function __toString()
    {
        if ($this->object === null) {
            $this->loadObject();
        }

        return (string)$this->object;
    }

    public function __sleep()
    {
        if ($this->object === null) {
            $this->loadObject();
        }

        return ['object', 'id'];
    }

    public function __debugInfo()
    {
        if ($this->object === null) {
            $this->loadObject();
        }

        return [
            'object' => $this->object,
            'id' => $this->id,
        ];
    }

    /**
     * Loads the generator to object value and unset's generator.
     *
     * Can only be called once, so check presence of $this->object before using it.
     *
     * This assumes generator is made to support bulk loading of several objects, and thus takes object id as input
     * in order to return right object at a time, and thus performs two yields per item.
     * -> See PR that came with this trait for example generators.
     */
    protected function loadObject()
    {
        $this->object = $this->generator->send($this->id);
        $this->generator->next();
        unset($this->generator);
    }
}
