<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

/**
 * A filtered Content items list iterator.
 */
final class ContentList implements IteratorAggregate
{
    /** @var int */
    private $totalCount;

    /** @var \eZ\Publish\API\Repository\Values\Content\Content[] */
    private $contentItems;

    /**
     * @internal for internal use by Repository
     */
    public function __construct(int $totalCount, array $contentItems)
    {
        $this->totalCount = $totalCount;
        $this->contentItems = $contentItems;
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\Content[]|\Traversable
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->contentItems);
    }
}
