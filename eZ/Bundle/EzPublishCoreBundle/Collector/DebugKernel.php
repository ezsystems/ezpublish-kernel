<?php
/**
 * File containing the DebugKernel class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishCoreBundle\Collector;

use \eZTemplate;

class DebugKernel
{
    /**
     * @var list of loaded templates
     **/
    protected static $templateList = array( "compact", "full" );

    /**
     * Add templates when loading a page
     **/
    public static function addTemplate( $templateName, $executeTime )
    {
        // Remove information about TwigLayoutDecorator
        if ( substr( $templateName, -5 ) === '.twig' )
        {
            self::$templateList["full"][$templateName] = $executeTime;
            if ( !isset( self::$templateList[$templateName] ) )
            {
                self::$templateList["compact"][$templateName] = 1;
            }
            else
            {
                self::$templateList["compact"][$templateName]++;
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
     * @return array
     **/
    public static function getLegacyTemplatesList( $container )
    {
        $legacyKernel = $container->get( 'ezpublish_legacy.kernel' );
        $templateStats = $legacyKernel()->runCallback(
            function ()
            {
                return \eZTemplate::templatesUsageStatistics();
            }
        );

        foreach ( $templateStats as $tplInfo )
        {
            $requestedTpl = $tplInfo["requested-template-name"];
            $actualTpl    = $tplInfo["actual-template-name"];
            $fullPath     = $tplInfo["template-filename"];

            self::$templateList["full"][$actualTpl] = array(
                "loaded" => $requestedTpl,
                "fullPath" => $fullPath
            );
            if ( !isset( self::$templateList["compact"][$requestedTpl] ) )
            {
                self::$templateList["compact"][$requestedTpl] = 1;
            }
            else
            {
                self::$templateList["compact"][$requestedTpl]++;
            }
        }
        return self::$templateList;
    }
}
