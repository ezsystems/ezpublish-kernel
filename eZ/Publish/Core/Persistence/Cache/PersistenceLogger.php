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

    /**
     * @var int
     */
    protected $count = 0;

    /**
     * @var bool
     */
    protected $logCalls = true;

    /**
     * @var array
     */
    protected $calls = array();

    /**
     * @var array
     */
    protected $unCachedHandlers = array();

    /**
     * @param bool $logCalls Flag to enable logging of calls or not, should be disabled in prod
     */
    public function __construct($logCalls = true)
    {
        $this->logCalls = $logCalls;
    }

    /**
     * Log SPI calls with method name and arguments until $maxLogCalls is reached.
     *
     * @param string $method
     * @param array $arguments
     */
    public function logCall($method, array $arguments = array())
    {
        ++$this->count;
        if ($this->logCalls) {
            $this->calls[] = array(
                'method' => $method,
                'arguments' => $arguments,
                'trace' => $this->getSimpleCallTrace(
                    array_slice(
                        debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 7),
                        2
                    )
                ),
            );
        }
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
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @return bool
     */
    public function isCallsLoggingEnabled()
    {
        return $this->logCalls;
    }

    /**
     * @return array
     */
    public function getCalls()
    {
        return $this->calls;
    }

    /**
     * @return array
     */
    public function getLoadedUnCachedHandlers()
    {
        return $this->unCachedHandlers;
    }
}
