<?php
/**
 * File containing the DebugKernel class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishDebugBundle\Collector;

use Symfony\Component\DependencyInjection\ContainerInterface;
use \eZTemplate;

/**
 * Class TemplateDebugInfo
 * @package eZ\Bundle\EzPublishCoreBundle\Collector
 *
 *
 * Holds debug info about twig templates and exposes function to get legacy template info
 * @todo Move legacy code to LegacyBundle, and then consider moving left overs to DebugTemplate class (rename TwigDebugTemplate?)
 */
class TemplateDebugInfo
{
    /**
     * @var array List of loaded templates
     **/
    protected static $templateList = array( "compact" => array(), "full" => array() );

    /**
     * Add templates when loading a page
     *
     * @param string $templateName
     * @param int $executionTime in milliseconds
     */
    public static function addTemplate( $templateName, $executionTime )
    {
        // Remove information about TwigLayoutDecorator
        if ( substr( $templateName, -5 ) === '.twig' )
        {
            if ( !isset( self::$templateList["compact"][$templateName] ) )
            {
                self::$templateList["compact"][$templateName] = 1;
                self::$templateList["full"][$templateName] = $executionTime;
            }
            else
            {
                self::$templateList["compact"][$templateName]++;
                self::$templateList["full"][$templateName] += $executionTime;
            }
        }
    }

    /**
     * Returns array of loaded templates
     *
     * @return array
     **/
    public static function getTemplatesList()
    {
        return self::$templateList;
    }

    /**
     * Returns array of loaded legacy templates
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @return array
     */
    public static function getLegacyTemplatesList( \Closure $legacyKernel )
    {
        $templateStats = $legacyKernel()->runCallback(
            function ()
            {
                return \eZTemplate::templatesUsageStatistics();
            }
        );

        $templateList = array( 'compact' => array(), 'full' => array() );
        foreach ( $templateStats as $tplInfo )
        {
            $requestedTpl = $tplInfo["requested-template-name"];
            $actualTpl    = $tplInfo["actual-template-name"];
            $fullPath     = $tplInfo["template-filename"];

            $templateList["full"][$actualTpl] = array(
                "loaded" => $requestedTpl,
                "fullPath" => $fullPath
            );
            if ( !isset( $templateList["compact"][$requestedTpl] ) )
            {
                $templateList["compact"][$requestedTpl] = 1;
            }
            else
            {
                $templateList["compact"][$requestedTpl]++;
            }
        }
        return $templateList;
    }
}
