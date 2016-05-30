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

/**
 * An abstract HTTP Cache purging Slot that purges cache for a Content.
 *
 * Will by default use the contentId property of the signal object, as it is the most common. Set generateTags()
 * method in case of different signals or need to clear more then the defaults.
 */
abstract class AbstractContentSlot extends AbstractSlot
{
    /**
     * Purges relevant HTTP cache for $signal.
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     *
     * @return mixed
     */
    protected function purgeHttpCache(Signal $signal)
    {
        return $this->purgeClient->purgeByTags($this->generateTags($signal));
    }

    /**
     * Default provides tags to clear content, relation, location, parent and sibling cache.
     *
     * Overload for tree operations where you also need to clear whole path.
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     *
     * @return array
     */
    protected function generateTags(Signal $signal)
    {
        $tags = [];

        if (isset($signal->contentId)) {
            $tags = ['content-'.$signal->contentId, 'relation-'.$signal->contentId];
        }

        if (isset($signal->locationId)) {
            $tags[] = 'location-'.$signal->locationId;
        }

        if (isset($signal->parentLocationId)) {
            $tags[] = 'location-'.$signal->parentLocationId;
            $tags[] = 'parent-'.$signal->parentLocationId;
        }

        return $tags;
    }
}
