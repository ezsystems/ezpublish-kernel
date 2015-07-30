<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\SignalSlot\Slot;

/**
 * An abstract HTTP Cache purging Slot that purges cache for a Content.
 *
 * Will by default use the contentId property of the signal object, as it is the most common. Override the
 * extractContentId() method in your own signal to use a different property.
 */
abstract class PurgeForContentHttpCacheSlot extends HttpCacheSlot
{
    /**
     * Purges all caches.
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     *
     * @return mixed
     */
    protected function purgeHttpCache(Signal $signal)
    {
        return $this->httpCacheClearer->purgeForContent($this->extractContentId($signal));
    }

    /**
     * Default implementation that returns the contentId property's value.
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal\SectionService\AssignSectionSignal $signal
     */
    protected function extractContentId(Signal $signal)
    {
        return $signal->contentId;
    }
}
