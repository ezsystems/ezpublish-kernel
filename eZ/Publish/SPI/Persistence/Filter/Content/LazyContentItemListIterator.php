<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Persistence\Filter\Content;

use eZ\Publish\SPI\Persistence\Filter\LazyListIterator;

/**
 * SPI Persistence Content Item list iterator.
 *
 * @internal for internal use by Repository Filtering
 *
 * @see \eZ\Publish\SPI\Persistence\Content\ContentItem
 */
class LazyContentItemListIterator extends LazyListIterator
{
    /**
     * @return \eZ\Publish\SPI\Persistence\Content\ContentItem[]
     *
     * @throws \Exception
     */
    public function getIterator(): iterable
    {
        yield from parent::getIterator();
    }
}
