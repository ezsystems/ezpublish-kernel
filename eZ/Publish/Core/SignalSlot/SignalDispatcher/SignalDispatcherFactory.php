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
     * Relative namespace for internal signals.
     */
    const RELATIVE_SIGNAL_NAMESPACE = 'eZ\\Publish\\Core\\SignalSlot\\Signal';

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
     * Bulk add all signal slots if needed for a search engine.
     *
     * @param array $signalDispatcherSlots
     */
    public function addSlots(array $signalDispatcherSlots)
    {
        $currentSearchEngineAlias = !empty($this->repositorySettings['search']['engine']) ? $this->repositorySettings['search']['engine'] : null;
        foreach ($signalDispatcherSlots as $signalDispatcherSlot) {
            if ($currentSearchEngineAlias !== $signalDispatcherSlot['searchEngineAlias']) {
                continue;
            }

            $signalIdentifier = $signalDispatcherSlot['signalIdentifier'];
            if ($signalIdentifier[0] === '\\') {
                $signalIdentifier = substr($signalIdentifier, 1);
            } elseif ($signalIdentifier !== '*') {
                $signalIdentifier = static::RELATIVE_SIGNAL_NAMESPACE . "\\$signalIdentifier";
            }
            $this->signalSlotMap[$signalIdentifier][] = $signalDispatcherSlot['slot'];
        }
    }

    /**
     * Build SignalDispatcher for SignalSlots.
     *
     * @return \eZ\Publish\Core\SignalSlot\SignalDispatcher
     */
    public function buildSignalDispatcher()
    {
        return new $this->signalDispatcherClass($this->signalSlotMap);
    }
}
