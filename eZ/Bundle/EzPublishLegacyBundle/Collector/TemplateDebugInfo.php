<?php
/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishLegacyBundle\Collector;

use eZ\Publish\Core\MVC\Legacy\Kernel as LegacyKernel;
use eZTemplate;
use ezxFormToken;
use RuntimeException;

class TemplateDebugInfo
{
    /**
     * Returns array of loaded legacy templates
     *
     * @param \Closure $legacyKernel
     *
     * @return array
     */
    public static function getLegacyTemplatesList( \Closure $legacyKernel )
    {
        $templateList = array( 'compact' => array(), 'full' => array() );
        // Only retrieve legacy templates list if the kernel has been booted at least once.
        if ( !LegacyKernel::hasInstance() )
        {
            return $templateList;
        }

        try
        {
            $templateStats = $legacyKernel()->runCallback(
                function ()
                {
                    if ( eZTemplate::isTemplatesUsageStatisticsEnabled() )
                    {
                        return eZTemplate::templatesUsageStatistics();
                    }
                    else
                    {
                        $stats = [];
                        foreach ( eZTemplate::instance()->templateFetchList() as $tpl )
                        {
                            $stats[] = [
                                'requested-template-name' => $tpl,
                                'actual-template-name' => $tpl,
                                'template-filename' => $tpl
                            ];
                        }

                        return $stats;
                    }
                },
                false,
                false
            );
        }
        catch ( RuntimeException $e )
        {
            // Ignore the exception thrown by legacy kernel as this would break debug toolbar (and thus debug info display).
            // Furthermore, some legacy kernel handlers don't support runCallback (e.g. ezpKernelTreeMenu)
            $templateStats = array();
        }

        foreach ( $templateStats as $tplInfo )
        {
            $requestedTpl = $tplInfo["requested-template-name"];
            $actualTpl    = $tplInfo["actual-template-name"];
            $fullPath     = $tplInfo["template-filename"];

            $templateList["full"][$actualTpl] = array(
                "loaded" => $requestedTpl,
                "fullPath" => $fullPath
            );
            if ( !isset( $templateList["compact"][$actualTpl] ) )
            {
                $templateList["compact"][$actualTpl] = 1;
            }
            else
            {
                $templateList["compact"][$actualTpl]++;
            }
        }

        return $templateList;
    }
}
