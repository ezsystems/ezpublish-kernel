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

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\Core\SignalSlot\Signal;

/**
 * A slot handling DeleteLocationSignal.
 */
class DeleteLocationSlot extends PurgeForContentHttpCacheSlot
{
    protected function supports(Signal $signal)
    {
        return $signal instanceof Signal\LocationService\DeleteLocationSignal;
    }

    /**
     * Purges relevant location cache.
     *
     * @todo Change to be able to clear relations, siblings, ... cache, even when content is already deleted.
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal\LocationService\DeleteLocationSignal $signal
     *
     * @return mixed
     */
    protected function purgeHttpCache(Signal $signal)
    {
        try {
            $locationIds = $this->extractLocationIds($signal);

            return $this->httpCacheClearer->purgeForContent($this->extractContentId($signal), $locationIds);
        } catch (NotFoundException $e) {
            // if content was deleted as well by this operation then fall back to clear location and parent location cache.
            $this->httpCacheClearer->purge($locationIds);
        }
    }
}
