<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
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
            'count' => $this->logger->getCount(),
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
     * @return int
     */
    public function getCount()
    {
        return $this->data['count'];
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
    public function getCalls()
    {
        $calls = [];
        foreach ($this->data['calls'] as $call) {
            list($class, $method) = explode('::', $call['method']);
            $namespace = explode('\\', $class);
            $class = array_pop($namespace);
            $calls[] = array(
                'namespace' => $namespace,
                'class' => $class,
                'method' => $method,
                'arguments' => empty($call['arguments']) ?
                    '' :
                    preg_replace(array('/^array\s\(\s/', '/,\s\)$/'), '', var_export($call['arguments'], true)),
            );
        }

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
