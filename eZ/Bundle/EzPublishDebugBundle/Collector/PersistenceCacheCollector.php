<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishDebugBundle\Collector;

use eZ\Publish\Core\Persistence\Cache\PersistenceLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Data collector listing SPI cache calls.
 */
class PersistenceCacheCollector extends DataCollector
{
    /** @var PersistenceLogger */
    private $logger;

    public function __construct(PersistenceLogger $logger)
    {
        $this->logger = $logger;
    }

    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = [
            'stats' => $this->logger->getStats(),
            'calls_logging_enabled' => $this->logger->isCallsLoggingEnabled(),
            'calls' => $this->logger->getCalls(),
            'handlers' => $this->logger->getLoadedUnCachedHandlers(),
        ];
    }

    public function getName()
    {
        return 'ezpublish.debug.persistence';
    }

    /**
     * Returns call count.
     *
     * @deprecated since 7.5, use getStats().
     *
     * @return int
     */
    public function getCount()
    {
        return $this->data['stats']['call'] + $this->data['stats']['miss'];
    }

    /**
     * Returns stats on Persistance cache usage.
     *
     * @since 7.5
     *
     * @return int[<string>]
     */
    public function getStats()
    {
        return $this->data['stats'];
    }

    /**
     * Returns flag to indicate if logging of calls is enabled or not.
     *
     * Typically not enabled in prod.
     *
     * @return bool
     */
    public function getCallsLoggingEnabled()
    {
        return $this->data['calls_logging_enabled'];
    }

    /**
     * Returns all calls.
     *
     * @return array
     */
    public function getCalls()
    {
        if (empty($this->data['calls'])) {
            return [];
        }

        $calls = $count = [];
        foreach ($this->data['calls'] as $hash => $call) {
            list($class, $method) = \explode('::', $call['method']);
            $namespace = \explode('\\', $class);
            $class = \array_pop($namespace);
            $calls[$hash] = [
                'namespace' => $namespace,
                'class' => $class,
                'method' => $method,
                'arguments' => $call['arguments'],
                'stats' => $call['stats'],
            ];
            // Get traces, and order them to have the most called first
            $calls[$hash]['traces'] = $call['traces'];
            $traceCount = [];
            foreach ($call['traces'] as $traceHash => $traceData) {
                $traceCount[$traceHash] = $traceData['count'];
            }
            \array_multisort($traceCount, SORT_DESC, SORT_NUMERIC, $calls[$hash]['traces']);

            // For call sorting count all calls, but weight in-memory lookups lower
            $count[$hash] = $call['stats']['uncached'] + $call['stats']['miss'] + $call['stats']['hit'] + ($call['stats']['memory'] * 0.001);
        }

        // Order calls
        \array_multisort($count, SORT_DESC, SORT_NUMERIC, $calls);

        return $calls;
    }

    /**
     * Returns un cached handlers being loaded.
     *
     * @return array
     */
    public function getHandlers()
    {
        $handlers = [];
        foreach ($this->data['handlers'] as $handler => $count) {
            list($class, $method) = explode('::', $handler);
            unset($class);
            $handlers[$method] = $method . '(' . $count . ')';
        }

        return $handlers;
    }

    /**
     * Returns un cached handlers being loaded.
     *
     * @return array
     */
    public function getHandlersCount()
    {
        return array_sum($this->data['handlers']);
    }
}
