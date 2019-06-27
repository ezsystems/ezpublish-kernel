<?php

/**
 * File is part of the eZ Publish package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot\SignalDispatcher;

use eZ\Publish\Core\SignalSlot\SignalDispatcher;
use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\SPI\Persistence\TransactionHandler;

/**
 * Dispatches Signals to their assigned Slots, after transactions have successfully completed.
 *
 * Wraps around a SignalDispatcher to add knowledge of transactions to be able to queue signals
 * if there is currently a transaction in progress. Signals which where part of transaction that
 * are rolled back are deleted. Signals on successful
 *
 * @internal
 */
class SignalDispatcherTransactionWrapper extends SignalDispatcher implements TransactionHandler
{
    /** @var \eZ\Publish\Core\SignalSlot\SignalDispatcher */
    private $signalDispatcher;

    /**
     * Array of arrays of signals indexed by the transaction count.
     *
     * @var \eZ\Publish\Core\SignalSlot\Signal[][]
     */
    private $signalsQueue = [];

    /** @var int Used to keep track of depth of current transaction */
    private $transactionDepth = 0;

    /** @var int Used to be able to unset affected signals on rollback */
    private $transactionCount = 0;

    /**
     * Construct from factory.
     *
     * @param SignalDispatcher $signalDispatcher
     */
    public function __construct(SignalDispatcher $signalDispatcher)
    {
        $this->signalDispatcher = $signalDispatcher;
    }

    /**
     * Emits the given $signal or queue if in transaction.
     *
     * All assigned slots will eventually receive the $signal
     *
     * @param Signal $signal
     */
    public function emit(Signal $signal)
    {
        if ($this->transactionDepth === 0) {
            $this->signalDispatcher->emit($signal);
        } else {
            $this->signalsQueue[$this->transactionCount][] = $signal;
        }
    }

    /**
     * Captures Begin transaction call.
     */
    public function beginTransaction()
    {
        ++$this->transactionDepth;
        $this->signalsQueue[++$this->transactionCount] = [];
    }

    /**
     * Captures Commit transaction call and emits queued signals.
     */
    public function commit()
    {
        // Ignore if no transaction
        if ($this->transactionDepth === 0) {
            return;
        }

        --$this->transactionDepth;
        if ($this->transactionDepth === 0) {
            foreach ($this->signalsQueue as $signalsQueue) {
                foreach ($signalsQueue as $signal) {
                    $this->signalDispatcher->emit($signal);
                }
            }

            // To avoid possible int overflow on long running processes
            $this->transactionCount = 0;
            $this->signalsQueue = [];
        }
    }

    /**
     * Captures Rollback transaction call.
     */
    public function rollback()
    {
        // Ignore if no transaction
        if ($this->transactionDepth === 0) {
            return;
        }

        --$this->transactionDepth;
        unset($this->signalsQueue[$this->transactionCount]);
        --$this->transactionCount; // In case several rollbacks will happen on hierarchical transactions.
    }
}
