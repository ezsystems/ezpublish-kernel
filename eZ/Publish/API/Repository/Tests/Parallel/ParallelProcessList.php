<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Tests\Parallel;

use Jenner\SimpleFork\Process;

class ParallelProcessList implements \IteratorAggregate
{
    /** @var \Jenner\SimpleFork\Process[] */
    private $pool = [];

    public function addProcess(Process $callback): void
    {
        $this->pool[] = $callback;
    }

    public function getIterator(): \Iterator
    {
        return new \ArrayIterator($this->pool);
    }
}
