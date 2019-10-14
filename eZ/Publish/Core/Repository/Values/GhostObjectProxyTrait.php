<?php

declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Values;

use eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException;
use Generator;

trait GhostObjectProxyTrait
{
    /** @var \Generator|null */
    protected $initializer;

    /**
     * Needs to be protected as value objects often define this as well.
     *
     * TODO: Name of this property should be configurable!
     *
     * @var mixed
     */
    protected $id;

    /**
     * GhostObjectProxyTrait constructor.
     *
     * @param \Generator $initializer
     * @param mixed $id Object id to use for loading the object on demand.
     */
    public function __construct(Generator $initializer, $id)
    {
        $this->initializer = $initializer;
        $this->id = $id;
    }

    public function __call($name, $arguments)
    {
        if (!$this->isInitialized()) {
            $this->initialize();
        }

        return $this->$name(...$arguments);
    }

    public function __invoke(...$args)
    {
        if (!$this->isInitialized()) {
            $this->initialize();
        }

        return ($this)(...$args);
    }

    public function __get($name)
    {
        if ($name === 'id') {
            return $this->id;
        }

        if (!$this->isInitialized()) {
            $this->initialize();
        }

        if (property_exists($this, $name)) {
            return $this->$name;
        }

        throw new PropertyNotFoundException($name, get_class($this));
    }

    public function __toString()
    {
        if (!$this->isInitialized()) {
            $this->initialize();
        }

        return (string)$this;
    }

    public function __sleep()
    {
        if (!$this->isInitialized()) {
            $this->initialize();
        }

        return ['id'];
    }

    public function __debugInfo()
    {
        if (!$this->isInitialized()) {
            $this->initialize();
        }

        return [
            'id' => $this->id,
        ];
    }

    protected function isInitialized(): bool
    {
        return $this->initializer === null;
    }

    protected function initialize(): void
    {
        $data = $this->initializer->send($this->id);
        foreach ($data as $property => $value) {
            $this->$property = $value;
        }

        $this->initializer->next();
        $this->initializer = null;
    }
}
