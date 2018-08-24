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
    /**
     * @var PersistenceLogger
     */
    private $logger;

    public function __construct(PersistenceLogger $logger)
    {
        $this->logger = $logger;
    }

    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = [
            'calls_logging_enabled' => $this->logger->isCallsLoggingEnabled(),
            'misses' => $this->logger->getCacheMisses(),
            'hits' => $this->logger->getCacheHits(),
            'handlers' => $this->logger->getLoadedUnCachedHandlers(),
        ];
    }

    public function getName()
    {
        return 'ezpublish.debug.persistence';
    }

    /**
     * @return int
     */
    public function getCountMisses()
    {
        return count($this->data['misses']);
    }

    /**
     * @return int
     */
    public function getCountHits()
    {
        return count($this->data['hits']);
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
     * Returns calls.
     *
     * @return array
     */
    public function getMisses()
    {
        return $this->getCallData($this->data['misses']);
    }

    /**
     * Returns hits.
     *
     * @return array
     */
    public function getHits()
    {
        return $this->getCallData($this->data['hits']);
    }


    private function getCallData(array $data): array
    {
        if (empty($data)) {
            return [];
        }

        $calls = $count = [];
        foreach ($data as $call) {
            $hash = md5(json_encode($call));
            if (isset($calls[$hash])) {
                $calls[$hash]['count']++;
                $count[$hash]++;

                continue;
            }

            list($class, $method) = explode('::', $call['method']);
            $namespace = explode('\\', $class);
            $class = array_pop($namespace);
            $calls[$hash] = array(
                'namespace' => $namespace,
                'class' => $class,
                'method' => $method,
                'arguments' => $this->simplifyCallArguments($call['arguments']),
                'trace' => implode(', ', $call['trace']),
                'count' => 1,
            );
            $count[$hash] = 1;
        }

        array_multisort($count, SORT_DESC, $calls);

        return $calls;
    }

    private function simplifyCallArguments(array $arguments): string
    {
        $string = '';
        foreach ($arguments as $key => $value) {
            if (empty($string)) {
                $string = $key . ':';
            } else {
                $string .= ', '. $key . ':';
            }

            if (is_array($value)) {
                $string .= '[' . implode(',', $value) . ']';
            } else {
                $string .= $value;
            }
        }

        return $string;
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
