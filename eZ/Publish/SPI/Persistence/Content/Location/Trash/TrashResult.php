<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Persistence\Content\Location\Trash;

use ArrayIterator;
use eZ\Publish\API\Repository\Values\ValueObject;

class TrashResult extends ValueObject implements \IteratorAggregate
{
    /**
     * The total number of Trash items matching criteria (ignores offset & limit arguments).
     *
     * @var int
     */
    public $totalCount = 0;

    /**
     * The value objects found for the query.
     *
     * @var \eZ\Publish\SPI\Persistence\Content\Location\Trashed[]
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
