<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\Trash;

use ArrayIterator;
use eZ\Publish\API\Repository\Values\ValueObject;
use Traversable;

class TrashItemDeleteResultList extends ValueObject implements \IteratorAggregate
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Trash\TrashItemDeleteResult[] */
    public $items = [];

    /**
     * @return \ArrayIterator
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }
}
