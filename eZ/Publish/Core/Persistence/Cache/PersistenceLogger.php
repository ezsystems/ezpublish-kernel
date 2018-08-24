<?php

/**
 * File containing the Persistence Cache SPI logger class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache;

/**
 * Log un-cached use of SPI Persistence.
 *
 * Stops logging details when reaching $maxLogCalls to conserve memory use
 */
class PersistenceLogger
{
    const NAME = 'PersistenceLogger';

    /** @var bool */
    protected $logCalls = true;

    /** @var array */
    protected $misses = [];

    /** @var array */
    protected $hits = [];

    /** @var array */
    protected $unCachedHandlers = array();

    /**
     * @param bool $logCalls Flag to enable logging of calls or not, should be disabled in prod
     */
    public function __construct($logCalls = true)
    {
        $this->logCalls = $logCalls;
    }

    /**
     * Log cache misses and SPI calls with method name and arguments.
     *
     * @param string $method
     * @param array $arguments
     */
    public function logCall($method, array $arguments = [])
    {
        if ($this->logCalls) {
            $this->misses[] = $this->getCacheCallData(
                $method,
                $arguments,
                array_slice(
                    debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 7),
                    2
                )
            );
        }
    }

    /**
     * Log a Cache hit, which means further SPI calls are not needed.
     *
     * @param string $method
     * @param array $arguments
     */
    public function logCacheHit($method, array $arguments = [])
    {
        if ($this->logCalls) {
            $this->hits[] = $this->getCacheCallData(
                $method,
                $arguments,
                array_slice(
                    debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 7),
                    2
                )
            );
        }
    }

    private function getCacheCallData($method, array $arguments, array $trimmedBacktrace)
    {
        return [
            'method' => $method,
            'arguments' => $arguments,
            'trace' => $this->getSimpleCallTrace($trimmedBacktrace),
        ];
    }

    private function getSimpleCallTrace(array $backtrace): array
    {
        $calls = [];
        foreach ($backtrace as $call) {
            if (!isset($call['class']) || strpos($call['class'], '\\') === false) {
                // skip if class has no namspace (Symfony lazy proxy) or plain function
                continue;
            }

            $calls[] = $call['class'] . $call['type'] . $call['function'] . '()';

            // Break out as soon as we have listed 1 class outside of kernel
            if (strpos($call['class'], 'eZ\\Publish\\Core\\') !== 0) {
                break;
            }
        }

        return $calls;
    }

    /**
     * Log uncached handler being loaded.
     *
     * @param string $handler
     */
    public function logUnCachedHandler($handler)
    {
        if (!isset($this->unCachedHandlers[$handler])) {
            $this->unCachedHandlers[$handler] = 0;
        }
        ++$this->unCachedHandlers[$handler];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * @deprecated since 7.3, count the hits or misses instead.
     * @return int
     */
    public function getCount()
    {
        return count($this->misses);
    }

    /**
     * @return bool
     */
    public function isCallsLoggingEnabled()
    {
        return $this->logCalls;
    }

    /**
     * @deprecated Since 7.3, use getCacheMisses()
     * @return array
     */
    public function getCalls()
    {
        return $this->misses;
    }

    /**
     * @return array
     */
    public function getCacheMisses()
    {
        return $this->misses;
    }

    /**
     * @return array
     */
    public function getCacheHits()
    {
        return $this->hits;
    }

    /**
     * @return array
     */
    public function getLoadedUnCachedHandlers()
    {
        return $this->unCachedHandlers;
    }
}
