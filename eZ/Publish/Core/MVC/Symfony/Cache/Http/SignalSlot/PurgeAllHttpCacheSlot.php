<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\Core\MVC\Symfony\Cache\GatewayCachePurger;
use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\SignalSlot\Slot;
use Psr\Log\LoggerInterface;

/**
 * An abstract slot for clearing all http caches
 */
abstract class PurgeAllHttpCacheSlot extends HttpCacheSlot
{
    /**
     * Purges all caches
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     *
     * @return mixed
     */
    protected function purgeHttpCache( Signal $signal )
    {
        return $this->httpCacheClearer->purgeAll();
    }
}
