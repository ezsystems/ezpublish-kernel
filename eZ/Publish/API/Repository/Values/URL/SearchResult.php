<?php

namespace eZ\Publish\API\Repository\Values\URL;

use eZ\Publish\API\Repository\Values\ValueObject;

class SearchResult extends ValueObject implements \IteratorAggregate
{
    /**
     * @var int
     */
    protected $count;

    /**
     * @var \eZ\Publish\API\Repository\Values\URL\URL[]
     */
    protected $items;

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }
}
