<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\SignalDispatcher;

use eZ\Publish\Core\SignalSlot\Slot;

class SignalDispatcherFactory
{
    /**
     * @var string
     */
    private $signalDispatcherClass;

    /**
     * @var array
     */
    private $repositorySettings;

    /**
     * @var array
     */
    private $signalSlotMap;

    /**
     * SignalDispatcherFactory constructor.
     * @param string $signalDispatcherClass
     * @param string $repositoryAlias
     * @param array $repositoriesSettings
     */
    public function __construct(
        $signalDispatcherClass,
        $repositoryAlias,
        array $repositoriesSettings
    ) {
        $this->signalDispatcherClass = $signalDispatcherClass;

        if ($repositoryAlias === null) {
            $aliases = array_keys($repositoriesSettings);
            $repositoryAlias = array_shift($aliases);
        }
        $this->repositorySettings = isset($repositoriesSettings[$repositoryAlias]) ? $repositoriesSettings[$repositoryAlias] : [];
        $this->signalSlotMap = [];
    }

    /**
     * Add a signal slot to be attached to a dispatcher.
     *
     * @param $searchEngineAlias
     * @param string $signal
     * @param Slot $slot
     */
    public function addSlot($searchEngineAlias, $signal, Slot $slot)
    {
        $currentSearchEngineAlias = !empty($this->repositorySettings['search']['engine']) ? $this->repositorySettings['search']['engine'] : null;
        if ($currentSearchEngineAlias !== $searchEngineAlias) {
            return;
        }
        $this->signalSlotMap[] = new SignalSlotMap([
            'signalIdentifier' => $signal,
            'slot' => $slot,
        ]);
    }

    /**
     * Build SignalDispatcher for SignalSlots.
     *
     * @return \eZ\Publish\Core\SignalSlot\SignalDispatcher
     */
    public function buildSignalDispatcher()
    {
        /** @var $dispatcher \eZ\Publish\Core\SignalSlot\SignalDispatcher */
        $dispatcher = new $this->signalDispatcherClass([]);
        if ($dispatcher instanceof DefaultSignalDispatcher) {
            foreach ($this->signalSlotMap as $map) {
                $dispatcher->attach($map->signalIdentifier, $map->slot);
            }
        }

        return $dispatcher;
    }
}
