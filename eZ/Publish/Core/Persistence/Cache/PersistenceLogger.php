<?php

/**
 * File containing the Persistence Cache SPI logger class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Cache;

/**
 * Log un-cached & cached use of SPI Persistence.
 */
class PersistenceLogger
{
    const NAME = 'PersistenceLogger';

    /** @var int[] */
    protected $stats = [
        'uncached' => 0,
        'miss' => 0,
        'hit' => 0,
        'memory' => 0,
    ];

    /** @var bool */
    protected $logCalls = true;

    /** @var array */
    protected $calls = [];

    /** @var array */
    protected $unCachedHandlers = [];

    /**
     * @param bool $logCalls Flag to enable logging of calls or not, provides extra debug info about calls made to SPI
     *                       level, including where they come form. However this uses quite a bit of memory.
     */
    public function __construct(bool $logCalls = true)
    {
        $this->logCalls = $logCalls;
    }

    /**
     * Log uncached SPI calls with method name and arguments.
     *
     * NOTE: As of 7.5 this method is meant for logging calls to uncached spi method calls,
     *       for cache miss calls to cached SPI methods migrate to use {@see logCacheMiss()}.
     *
     * @param string $method
     * @param array $arguments
     */
    public function logCall(string $method, array $arguments = []): void
    {
        ++$this->stats['uncached'];
        if (!$this->logCalls) {
            return;
        }

        $this->collectCacheCallData(
            $method,
            $arguments,
            \array_slice(
                \debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 9),
                2
            ),
            'uncached'
        );
    }

    /**
     * Log Cache miss, gets info it needs by backtrace if needed.
     *
     * @since 7.5
     *
     * @param array $arguments
     * @param int $traceOffset
     */
    public function logCacheMiss(array $arguments = [], int $traceOffset = 2): void
    {
        ++$this->stats['miss'];
        if (!$this->logCalls) {
            return;
        }

        $trace = \debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 8 + $traceOffset);
        $this->collectCacheCallData(
            $trace[$traceOffset - 1]['class'] . '::' . $trace[$traceOffset - 1]['function'],
            $arguments,
            \array_slice($trace, $traceOffset),
            'miss'
        );
    }

    /**
     * Log a Cache hit, gets info it needs by backtrace if needed.
     *
     * @since 7.5
     *
     * @param array $arguments
     * @param int $traceOffset
     * @param bool $inMemory Denotes is cache hit was from memory (php variable), as opposed to from cache pool which
     *                       is usually disk or remote cache service.
     */
    public function logCacheHit(array $arguments = [], int $traceOffset = 2, bool $inMemory = false): void
    {
        if ($inMemory) {
            ++$this->stats['memory'];
        } else {
            ++$this->stats['hit'];
        }

        if (!$this->logCalls) {
            return;
        }

        $trace = \debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 8 + $traceOffset);
        $this->collectCacheCallData(
            $trace[$traceOffset - 1]['class'] . '::' . $trace[$traceOffset - 1]['function'],
            $arguments,
            \array_slice($trace, $traceOffset),
            $inMemory ? 'memory' : 'hit'
        );
    }

    /**
     * Collection  method for {@see logCacheHit()}, {@see logCacheMiss()} & {@see logCall()}.
     *
     * @param $method
     * @param array $arguments
     * @param array $trimmedBacktrace
     * @param string $type
     */
    private function collectCacheCallData($method, array $arguments, array $trimmedBacktrace, string $type): void
    {
        // simplest/fastests hash possible to identify if we have already collected this before to save on memory use
        $callHash = \hash('adler32', $method . \serialize($arguments));
        if (empty($this->calls[$callHash])) {
            $this->calls[$callHash] = [
                'method' => $method,
                'arguments' => $arguments,
                'stats' => [
                    'uncached' => 0,
                    'miss' => 0,
                    'hit' => 0,
                    'memory' => 0,
                ],
                'traces' => [],
            ];
        }
        ++$this->calls[$callHash]['stats'][$type];

        $trace = $this->getSimpleCallTrace($trimmedBacktrace);
        $traceHash = \hash('adler32', \implode('', $trace));
        if (empty($this->calls[$callHash]['traces'][$traceHash])) {
            $this->calls[$callHash]['traces'][$traceHash] = [
                'trace' => $trace,
                'count' => 0,
            ];
        }
        ++$this->calls[$callHash]['traces'][$traceHash]['count'];
    }

    /**
     * Simplify trace to an array of strings.
     *
     * Skipps any traces from Syfony proxies or closures to make trace as readable as possible in as few lines as
     * possible. And point is to identify which code outside kernel is triggering the SPI call, so trace stops one
     * call after namespace is no longer in eZ\Publish\Core\.
     *
     * @param array $backtrace Partial backtrace from |debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) or similar.
     *
     * @return string[]
     */
    private function getSimpleCallTrace(array $backtrace): array
    {
        $calls = [];
        $exitOnNext = false;
        foreach ($backtrace as $call) {
            if (!isset($call['class'][2]) || ($call['class'][2] !== '\\' && \strpos($call['class'], '\\') === false)) {
                // skip if class has no namespace (Symfony lazy proxy or plain function)
                continue;
            }

            $calls[] = $call['class'] . $call['type'] . $call['function'] . '()';

            if ($exitOnNext) {
                break;
            }

            // Break out as soon as we have listed 2 classes outside of kernel
            if ($call['class'][0] !== 'e' && \strpos($call['class'], 'eZ\\Publish\\Core\\') !== 0) {
                $exitOnNext = true;
            }
        }

        return $calls;
    }

    /**
     * Log un-cached handler being loaded.
     *
     * @param string $handler
     */
    public function logUnCachedHandler(string $handler): void
    {
        if (!isset($this->unCachedHandlers[$handler])) {
            $this->unCachedHandlers[$handler] = 0;
        }
        ++$this->unCachedHandlers[$handler];
    }

    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * Counts the total of spi uncached call (cache miss and uncached calls).
     *
     * @deprecated Since 7.5, use getStats().
     */
    public function getCount(): int
    {
        return $this->stats['uncached'] + $this->stats['miss'];
    }

    /**
     * Get stats (call/miss/hit/memory).
     *
     * @since 7.5
     */
    public function getStats(): array
    {
        return $this->stats;
    }

    public function isCallsLoggingEnabled(): bool
    {
        return $this->logCalls;
    }

    public function getCalls(): array
    {
        return $this->calls;
    }

    public function getLoadedUnCachedHandlers(): array
    {
        return $this->unCachedHandlers;
    }
}
