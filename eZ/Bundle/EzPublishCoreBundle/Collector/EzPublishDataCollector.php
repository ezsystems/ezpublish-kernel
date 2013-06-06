<?php
/**
 * File containing the EzPublishDataCollector class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Collector;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use eZ\Publish\Core\Persistence\Cache\PersistenceLogger;

/**
 * Collects list of templates from eZ 5 stack or Legacy Stack
 * Collects number of calls made to SPI Persistence as logged by eZ\Publish\Core\Persistence\Cache\*.
 */
class EzPublishDataCollector extends DataCollector
{
    /**
     * @var \eZ\Publish\Core\Persistence\Cache\PersistenceLogger
     */
    protected $logger;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @param \eZ\Publish\Core\Persistence\Cache\PersistenceLogger $logger
     */
    public function __construct( PersistenceLogger $logger, ContainerInterface $container )
    {
        $this->logger = $logger;
        $this->container = $container;
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
        $currentSA = $request->attributes->get( 'siteaccess' )->name;

        $this->data = array(
            'count' => $this->logger->getCount(),
            'calls' => $this->logger->getCalls(),
            'handlers' => $this->logger->getLoadedUnCachedHandlers(),
            'templates' => $this->getTemplateList( $currentSA ),
            'legacyMode' => $this->getIsLegacyMode( $currentSA )
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
        return 'ezpublish.debug.toolbar';
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

    /**
     * Returns templates list
     *
     * @return array
     */
    public function getTemplates()
    {
        return $this->data['templates'];
    }

    /**
     * Returns legacy mode boolean
     *
     * @return boolean
     */
    public function getLegacyMode()
    {
        return $this->data['legacyMode'];
    }

    /**
     * Returns a boolean
     *
     * @param string $currentSA get current siteaccess name
     *
     * @return boolean
     */
    public function getIsLegacyMode( $currentSA )
    {
        return $this->container->getParameter( "ezsettings.$currentSA.legacy_mode" );
    }

    /**
     * Returns all templates loaded via eZ 5 stack or Legacy stack
     *
     * @param String  $currentSA get current siteaccess name
     *
     * @return array
     */
    public function getTemplateList( $currentSA )
    {
        $isLegacyMode = $this->getIsLegacyMode( $currentSA );
        if ( $isLegacyMode )
        {
            $templateList = DebugKernel::getLegacyTemplatesList( $this->container );
        }
        else
        {
            $templateList = DebugKernel::getTemplatesList();
        }
        return $templateList;
    }
}
