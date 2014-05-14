<?php
/**
 * File containing the DebugTemplate class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
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
        $templateName = $this->getTemplateName();
        // Check if template name ends with "html.twig", indicating this is an HTML template.
        $isHtmlTemplate = substr( $templateName, -strlen( 'html.twig' ) ) === 'html.twig';

        if ( $isHtmlTemplate )
        {
            echo '<!-- START ' . $this->getTemplateName() . ' -->';
        }

        parent::display( $context, $blocks );

        if ( $isHtmlTemplate )
        {
            echo '<!-- STOP ' . $this->getTemplateName() . ' -->';
        }

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
     * by subtracting the time taken to display nested templates
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
