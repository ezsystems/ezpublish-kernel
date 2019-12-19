<?php

declare(strict_types=1);

namespace eZ\Publish\Core\Repository\TreeAccessor;

use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use IteratorIterator;

final class SearchHitIterator extends IteratorIterator
{
    public function __construct(SearchResult $iterator)
    {
        parent::__construct($iterator);
    }

    public function current()
    {
        return parent::current()->valueObject;
    }
}


