<?php
/**
 * File containing the TwigBaseTemplateResolver class.
 *
 * @copyright Copyright (C) 2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishDebugBundle\DependencyInjection;

class TwigBaseTemplateResolver
{
    /**
     * Sets the base_template_class twig option to TemplateDebugInfo if $debug is enabled
     * @param bool $debug
     * @param array $twigOptions
     * @return array
     */
    public function resolve( $debug, $twigOptions )
    {
        if ( $debug && !isset( $twigOptions['base_template_class'] ) )
        {
            $twigOptions['base_template_class'] = 'eZ\Bundle\EzPublishCoreBundle\Twig\DebugTemplate';
        }

        return $twigOptions;
    }
}
