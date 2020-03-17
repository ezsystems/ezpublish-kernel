<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\URL;

use ArrayIterator;
use eZ\Publish\API\Repository\Values\ValueObject;
use Traversable;

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

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }
}
