<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\API\Repository\Values\URL;

use ArrayIterator;
use eZ\Publish\API\Repository\Values\ValueObject;

class SearchResult extends ValueObject implements \IteratorAggregate
{
    /**
     * The total number of URLs.
     *
     * @var int
     */
    public $totalCount = 0;

    /**
     * The value objects found for the query.
     *
     * @var \eZ\Publish\API\Repository\Values\URL\URL[]
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
