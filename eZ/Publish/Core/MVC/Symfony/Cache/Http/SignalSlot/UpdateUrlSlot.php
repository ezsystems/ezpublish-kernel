<?php

namespace eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot;

use eZ\Publish\Core\MVC\Symfony\Cache\GatewayCachePurger;
use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\SignalSlot\Slot;
use eZ\Publish\Core\SignalSlot\Signal\URLService\UpdateUrlSignal;

/**
 * A slot handling UpdateUrlSignal.
 *
 * @deprecated since 6.8. The platform-http-cache package defines slots for http-cache multi-tagging.
 */
class UpdateUrlSlot extends Slot
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Cache\GatewayCachePurger
     */
    protected $httpCacheClearer;

    /**
     * UpdateUrlSlot constructor.
     *
     * @param GatewayCachePurger $httpCacheClearer
     */
    public function __construct(GatewayCachePurger $httpCacheClearer)
    {
        $this->httpCacheClearer = $httpCacheClearer;
    }

    /**
     * {@inheritdoc}
     */
    public function receive(Signal $signal)
    {
        if ($signal instanceof UpdateUrlSignal) {
            $this->httpCacheClearer->purgeAll();
        }
    }
}
