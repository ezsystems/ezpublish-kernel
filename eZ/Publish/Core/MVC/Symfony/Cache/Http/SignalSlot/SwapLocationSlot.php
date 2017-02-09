<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * A slot handling SwapLocationSignal.
 *
 * @deprecated since 6.8. The platform-http-cache package defines slots for http-cache multi-tagging.
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
