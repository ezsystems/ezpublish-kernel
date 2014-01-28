<?php
/**
 * File containing the DebugTemplate class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Bundle\EzPublishDebugBundle\Twig;

use eZ\Bundle\EzPublishDebugBundle\Collector\TemplateDebugInfo;
use Twig_Template;

/**
 * Meant to be used as a Twig base template class.
 *
 * Wraps the display method to:
 * - Record display() execution time for each template
 * - Log the execution and its time using eZ\Bundle\EzPublishDebugBundle
 */
abstract class DebugTemplate extends Twig_Template
{
    public function display( array $context, array $blocks = array() )
    {
        $startTime = microtime( true );

        $templateListBefore = TemplateDebugInfo::getTemplatesList();

        echo "<!-- " . $this->getTemplateName() . " START -->";
        parent::display( $context, $blocks );
        echo "<!-- " . $this->getTemplateName() . " END -->";

        $endTime = microtime( true );

        $templateListAfter = TemplateDebugInfo::getTemplatesList();

        TemplateDebugInfo::addTemplate(
            $this->getTemplateName(),
            $this->computeExecutionTime(
                round( ( $endTime - $startTime ) * 1000 ),
                $templateListBefore['full'],
                $templateListAfter['full']
            )
        );
    }

    /**
     * Given a raw $executionTime, and list of templates before and after display, computes the *real* execution time
     * by substracting the time taken to display nested templates
     *
     * @param int $executionTime milliseconds
     * @param array $templateListBefore templateName => executionTime
     * @param array $templateListAfter templateName => executionTime
     *
     * @return int Computed execution time in milliseconds
     */
    protected function computeExecutionTime( $executionTime, $templateListBefore, $templateListAfter )
    {
        foreach ( $templateListAfter as $templateName => $nestedExecutionTime )
        {
            if ( isset( $templateListBefore[$templateName] ) )
            {
                if ( $templateListBefore[$templateName] == $templateListAfter[$templateName] )
                {
                    continue;
                }
                $executionTime -= ( $nestedExecutionTime - $templateListBefore[$templateName] );
            }
            else
            {
                $executionTime -= $nestedExecutionTime;
            }
        }

        return $executionTime;
    }
}
