<?php

namespace eZ\Publish\API\Repository\Values\Content\Trash;

use eZ\Publish\API\Repository\Values\ValueObject;

class TrashItemDeleteResultList extends ValueObject implements \IteratorAggregate
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResult[]
     */
    public $items = [];

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }
}
