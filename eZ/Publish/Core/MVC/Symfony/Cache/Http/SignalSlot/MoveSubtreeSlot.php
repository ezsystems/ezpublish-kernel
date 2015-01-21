<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * A slot handling MoveSubtreeSignal.
 *
 * @todo FIXME what is this one supposed to clear ?
 */
class MoveSubtreeSlot extends HttpCacheSlot
{
    protected function purgeHttpCache( Signal $signal )
    {
        return $this->httpCacheClearer->purgeAll();
    }

    /**
     * @param \eZ\Publish\Core\SignalSlot\Signal\LocationService\MoveSubtreeSignal $signal
     */
    protected function extractContentId( Signal $signal )
    {
        return null;
    }

    protected function supports( Signal $signal )
    {
        return $signal instanceof Signal\LocationService\MoveSubtreeSignal;
    }
}
