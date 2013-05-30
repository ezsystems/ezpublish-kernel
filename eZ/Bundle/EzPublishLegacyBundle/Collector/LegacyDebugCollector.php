<?php

/**
 * File containing the LegacyDebugCollector class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Collector;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use \eZDebug;
use Symfony\Component\EventDispatcher\EventDispatcher;
use eZ\Bundle\EzPublishCoreBundle\Controller;

class LegacyDebugCollector extends DataCollector
{
    /**
     * The eZ 5 container 
     * 
     * @var Symfony\Component\DependencyInjection\ContainerInterface; 
     */
    private $container;

    public function __construct( ContainerInterface $container )
    {
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
            'legacymode' => $this->container->getParameter( "ezsettings.$currentSA.legacy_mode" ),
            'info' => array(
                'error' => $this->getInfoCount( $request->get( 'ContentId' ), "Error" ),
                'warning' => $this->getInfoCount( $request->get( 'ContentId' ), "Warning" ),
                'debug' => $this->getInfoCount( $request->get( 'ContentId' ), "Debug" )
            ),
        );
    }

    /**
     * Returns the name of the collector parameter.
     *
     * @return string The collector name parameter
     *
     * @api
     */
    public function getInfo()
    {
        return $this->data['info'];
    }

    /**
     * Returns the name of the collector parameter.
     *
     * @return string The collector name parameter
     *
     * @api
     */
    public function getLegacyMode()
    {
        return $this->data['legacymode'];
    }

    /**
     * Returns the name of the collector.
     *
     * @return string The collector name
     *
     */
    public function getName()
    {
        return 'legacy_data';
    }

    /**
     * Returns the debug output from Legacy.
     *
     * @return string The debug output (html)
     *
     */
    public function getLegacyDebug( $contentId )
    {
        $serviceLegacy = $this->container->get( 'ezpublish_legacy.kernel' );

        $debugOutput = $serviceLegacy()->runCallback(

            function () use ( $contentId )
            {
                $ezDebugInstance = eZDebug::instance();
                //($as_html, $returnReport, $allowedDebugLevels, $useAccumulators, $useTiming, $useIncludedFiles)
                $report = $ezDebugInstance->printReportInternal( true, true, false, false, false, false );

                return $report;
            }
        );

        return $debugOutput;
    }

    /**
     * Returns number of matches found on debug output.
     *
     * @return integer The number of error / warning / debug found
     *
     */
    public function getInfoCount( $contentId, $type )
    {
        $html = $this->getLegacyDebug( $contentId );

        $out = array();
        preg_match_all( "/(". $type . "\:)/", $html, $out );

        return count( $out[0] );
    }
}
