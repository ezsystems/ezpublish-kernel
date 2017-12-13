<?php

namespace eZ\Publish\API\Repository\Values\Content\Trash;

use ArrayIterator;
use eZ\Publish\API\Repository\Values\ValueObject;

class SearchResult extends ValueObject implements \IteratorAggregate
{
    public function __construct(array $properties = [])
    {
        if (isset($properties['totalCount'])) {
            $this->count = $properties['totalCount'];
        }

        parent::__construct($properties);
    }

    /**
     * The total number of Trash items.
     *
     * @var int
     */
    public $totalCount = 0;

    /**
     * The total number of Trash items.
     *
     * @deprecated Property is here purely for BC with 5.x/6.x.
     * @var int
     */
    public $count = 0;

    /**
     * The Trash items found for the query.
     *
     * @var \eZ\Publish\API\Repository\Values\Content\TrashItem[]
     */
    public $items = [];

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }
}