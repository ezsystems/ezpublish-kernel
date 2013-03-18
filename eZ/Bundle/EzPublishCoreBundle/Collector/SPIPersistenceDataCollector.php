<?php
/**
 * File containing the SPIPersistenceDataCollector class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Collector;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use eZ\Publish\Core\Persistence\Cache\PersistenceLogger;

/**
 * Collects number of calls made to SPI Persistence as logged by eZ\Publish\Core\Persistence\Cache\*.
 */
class SPIPersistenceDataCollector extends DataCollector
{
    /**
     * @var \eZ\Publish\Core\Persistence\Cache\PersistenceLogger
     */
    protected $logger;

    /**
     * @param \eZ\Publish\Core\Persistence\Cache\PersistenceLogger $logger
     */
    public function __construct( PersistenceLogger $logger )
    {
        $this->logger = $logger;
    }

    /**
     * Collects data for the given Request and Response.
     *
     * @param Request    $request   A Request instance
     * @param Response   $response  A Response instance
     * @param \Exception $exception An Exception instance
     *
     * @api
     */
    public function collect( Request $request, Response $response, \Exception $exception = null )
    {
        $this->data = array(
            'count' => $this->logger->getCount(),
            'calls' => $this->logger->getCalls(),
            'handlers' => $this->logger->getLoadedUnCachedHandlers()
        );
    }

    /**
     * Returns the name of the collector.
     *
     * @return string The collector name
     *
     * @api
     */
    public function getName()
    {
        return 'ezpublish.spi.persistence';
    }

    /**
     * Returns call count
     *
     * @return int
     */
    public function getCount()
    {
        return $this->data['count'];
    }

    /**
     * Returns calls
     *
     * @return array
     */
    public function getCalls()
    {
        $calls = array();
        foreach ( $this->data['calls'] as $call )
        {
            list( $class, $method ) = explode( '::', $call['method'] );
            $namespace = explode( '\\', $class );
            $class = array_pop( $namespace );
            $calls[] = array(
                'namespace' => $namespace,
                'class' => $class,
                'method' => $method,
                'arguments' => empty( $call['arguments'] ) ?
                    '' :
                    preg_replace( array( '/^array\s\(\s/', '/,\s\)$/' ), '', var_export( $call['arguments'], true ) )
            );
        }
        return $calls;
    }

    /**
     * Returns un cached handlers being loaded
     *
     * @return array
     */
    public function getHandlers()
    {
        $count = array();
        $handlers = array();
        foreach ( $this->data['handlers'] as $handler )
        {
            list( $class, $method ) = explode( '::', $handler );

            $count[$method] = ( isset($count[$method]) ? $count[$method] : 0 ) + 1;
            $handlers[$method] = $method . '(' . $count[$method] . ')';
        }
        return $handlers;
    }

    /**
     * Returns un cached handlers being loaded
     *
     * @return array
     */
    public function getHandlersCount()
    {
        $count = array();
        foreach ( $this->data['handlers'] as $handler )
        {
            $count[$handler] = ( isset($count[$handler]) ? $count[$handler] : 0 ) + 1;
        }
        return count( $count );
    }
}
