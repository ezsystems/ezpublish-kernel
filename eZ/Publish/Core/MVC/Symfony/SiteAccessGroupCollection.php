<?php

declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony;

use Traversable;

class SiteAccessGroupCollection implements \IteratorAggregate
{
    /** @var SiteAccessGroup[] */
    private $groups;

    public function __construct(array $groups = [])
    {
        $this->groups = $groups;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->groups);
    }
}
