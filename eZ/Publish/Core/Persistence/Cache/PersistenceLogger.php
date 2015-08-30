<?php

/**
 * File containing the Persistence Cache SPI logger class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Persistence\Cache;

use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Log un-cached use of SPI Persistence.
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
     * @var null|Stopwatch
     */
    protected $stopwatch;

    /**
     * @param bool $logCalls Flag to enable logging of calls or not, should be disabled in prod
     * @param Stopwatch|null $stopwatch A Stopwatch instance
     */
    public function __construct($logCalls = true, Stopwatch $stopwatch = null)
    {
        $this->logCalls = $logCalls;
        $this->stopwatch = $stopwatch;
    }

    /**
     * Start Log SPI calls with method name and arguments.
     *
     * Starts stopWatch, making it important {@see stopLogCall()} is called as well, and logs the call with arguments.
     *
     * @param string $method
     * @param array $arguments
     */
    public function startLogCall($method, array $arguments = array())
    {
        if ($this->stopwatch !== null) {
            $this->stopwatch->start($method, 'ez.spi.persistence');
        }

        if ($this->logCalls) {
            $this->logCall($method, $arguments);
        }
    }

    /**
     * Log SPI calls with method name and arguments.
     *
     * This should only be used when not using {@see startLogCall}
     *
     * @param string $method
     * @param array $arguments
     */
    public function logCall($method, array $arguments = array())
    {
        if (!$this->logCalls) {
            return;
        }

        ++$this->count;
        $this->calls[] = array(
            'method' => $method,
            'arguments' => $arguments,
        );
    }

    /**
     * Lap log SPI calls with method name.
     *
     * Adds a checkpoint, can be used between persistence call and cache clearing, and between cache clearing and cache warming.
     *
     * @param string $method
     */
    public function lapLogCall($method)
    {
        if ($this->stopwatch !== null) {
            $this->stopwatch->lap($method);
        }
    }

    /**
     * Stop log SPI calls with method name.
     *
     * @param string $method
     */
    public function stopLogCall($method)
    {
        if ($this->stopwatch !== null) {
            $this->stopwatch->stop($method);
        }
    }

    /**
     * Log uncached handler being loaded.
     *
     * @param string $handler
     */
    public function logUnCachedHandler($handler)
    {
        if (!$this->logCalls) {
            return;
        }

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
