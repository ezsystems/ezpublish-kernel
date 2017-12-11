<?php

namespace eZ\Publish\API\Repository\Values\URL;

use ArrayIterator;
use eZ\Publish\API\Repository\Values\ValueObject;

class UsageSearchResult extends ValueObject implements \IteratorAggregate
{
    /**
     * The total number of content objects using URL.
     *
     * @var int
     */
    public $totalCount = 0;

    /**
     * The value objects found for the query.
     *
     * @var \eZ\Publish\API\Repository\Values\Content\ContentInfo[]
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
