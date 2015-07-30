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
 * A slot handling SwapLocationSignal.
 */
class SwapLocationSlot extends HttpCacheSlot
{
    /**
     * Not required by this implementation.
     */
    protected function extractContentId(Signal $signal)
    {
        return null;
    }

    protected function supports(Signal $signal)
    {
        return $signal instanceof Signal\LocationService\SwapLocationSignal;
    }

    /**
     * @param \eZ\Publish\Core\SignalSlot\Signal\LocationService\SwapLocationSignal $signal
     */
    protected function purgeHttpCache(Signal $signal)
    {
        $this->httpCacheClearer->purgeForContent($signal->content1Id);
        $this->httpCacheClearer->purgeForContent($signal->content2Id);
    }
}
