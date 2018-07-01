<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Notification;

use ArrayIterator;
use IteratorAggregate;
use eZ\Publish\API\Repository\Values\ValueObject;

class NotificationList extends ValueObject implements IteratorAggregate
{
    /** @var int */
    public $totalCount = 0;

    /** @var \eZ\Publish\API\Repository\Values\Notification\Notification[] */
    public $items = [];

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }
}
