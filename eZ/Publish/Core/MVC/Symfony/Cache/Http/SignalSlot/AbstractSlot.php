<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Http\SignalSlot;

use eZ\Publish\Core\MVC\Symfony\Cache\PurgeClientInterface;
use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\SignalSlot\Slot;

/**
 * A abstract legacy slot covering common functions needed for legacy slots.
 */
abstract class AbstractSlot extends Slot
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Cache\PurgeClientInterface
     */
    protected $purgeClient;

    /**
     * @param \eZ\Publish\Core\MVC\Symfony\Cache\PurgeClientInterface $purgeClient
     */
    public function __construct(PurgeClientInterface $purgeClient)
    {
        $this->purgeClient = $purgeClient;
    }

    public function receive(Signal $signal)
    {
        if (!$this->supports($signal)) {
            return;
        }

        $this->purgeHttpCache($signal);
    }

    /**
     * Checks if $signal is supported by this handler.
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     *
     * @return bool
     */
    abstract protected function supports(Signal $signal);

    /**
     * Purges relevant HTTP cache for $signal.
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     *
     * @return mixed
     */
    abstract protected function purgeHttpCache(Signal $signal);
}
