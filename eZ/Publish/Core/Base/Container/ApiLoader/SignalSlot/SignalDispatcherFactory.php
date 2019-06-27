<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Base\Container\ApiLoader\SignalSlot;

class SignalDispatcherFactory
{
    /**
     * Relative namespace for internal signals.
     */
    const RELATIVE_SIGNAL_NAMESPACE = 'eZ\\Publish\\Core\\SignalSlot\\Signal';

    /** @var string */
    private $signalDispatcherClass;

    /** @var array */
    private $signalSlotMap = [];

    /** @var string */
    private $searchEngineAlias;

    /**
     * SignalDispatcherFactory constructor.
     *
     * @param string $signalDispatcherClass
     * @param $searchEngineAlias
     */
    public function __construct(
        $signalDispatcherClass,
        $searchEngineAlias
    ) {
        $this->signalDispatcherClass = $signalDispatcherClass;
        $this->searchEngineAlias = $searchEngineAlias;
    }

    /**
     * Get current search engine alias.
     *
     * @return string
     */
    public function getSearchEngineAlias()
    {
        return $this->searchEngineAlias;
    }

    /**
     * Bulk add all signal slots if needed for a search engine.
     *
     * @param string $searchEngineAlias
     * @param array $searchEngineSignalSlots [signal => array(slot1, slot2, ...)]
     */
    public function addSlotsForSearchEngine($searchEngineAlias, array $searchEngineSignalSlots)
    {
        if ($this->getSearchEngineAlias() !== $searchEngineAlias) {
            return;
        }

        foreach ($searchEngineSignalSlots as $signalIdentifier => $slots) {
            if ($signalIdentifier[0] === '\\') {
                $signalIdentifier = substr($signalIdentifier, 1);
            } elseif ($signalIdentifier !== '*') {
                $signalIdentifier = static::RELATIVE_SIGNAL_NAMESPACE . "\\$signalIdentifier";
            }
            $this->signalSlotMap[$signalIdentifier] = $slots;
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
