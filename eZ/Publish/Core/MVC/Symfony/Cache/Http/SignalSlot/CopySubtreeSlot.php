<?php

namespace eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal;

/**
 * A slot handling CopySubtreeSignal.
 *
 * @deprecated since 6.8. The platform-http-cache package defines slots for http-cache multi-tagging.
 */
class CopySubtreeSlot extends HttpCacheSlot
{
    protected function supports(Signal $signal)
    {
        return $signal instanceof Signal\LocationService\CopySubtreeSignal;
    }

    protected function purgeHttpCache(Signal $signal)
    {
        /** @var Signal\LocationService\CopySubtreeSignal $signal */
        $this->httpCacheClearer->purge([
            $signal->targetParentLocationId,
        ]);
    }
}
