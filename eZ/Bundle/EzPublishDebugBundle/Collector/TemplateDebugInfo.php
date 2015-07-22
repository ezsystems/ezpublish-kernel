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

/**
 * Holds debug info about twig templates and exposes function to get legacy template info.
 */
class TemplateDebugInfo
{
    /**
     * @var array List of loaded templates
     **/
    protected static $templateList = array('compact' => array(), 'full' => array());

    /**
     * Add templates when loading a page.
     *
     * @param string $templateName
     * @param int $executionTime in milliseconds
     */
    public static function addTemplate($templateName, $executionTime)
    {
        // Remove information about TwigLayoutDecorator
        if (substr($templateName, -5) === '.twig') {
            if (!isset(self::$templateList['compact'][$templateName])) {
                self::$templateList['compact'][$templateName] = 1;
                self::$templateList['full'][$templateName] = $executionTime;
            } else {
                ++self::$templateList['compact'][$templateName];
                self::$templateList['full'][$templateName] += $executionTime;
            }
        }
    }

    /**
     * Returns array of loaded templates.
     *
     * @return array
     **/
    public static function getTemplatesList()
    {
        return self::$templateList;
    }
}
