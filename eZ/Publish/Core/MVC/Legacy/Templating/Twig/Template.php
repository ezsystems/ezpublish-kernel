<?php
/**
 * File containing the Template class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Templating\Twig;

use Twig_Environment;
use Twig_TemplateInterface;
use eZ\Publish\Core\MVC\Legacy\Templating\LegacyEngine;

/**
 * Twig Template class representation for a legacy template.
 */
class Template implements Twig_TemplateInterface
{
    private $templateName;

    /**
     * @var \Twig_Environment
     */
    private $env;

    /**
     * @var \eZ\Publish\Core\MVC\Legacy\Templating\LegacyEngine
     */
    private $legacyEngine;

    public function __construct( $templateName, Twig_Environment $env, LegacyEngine $legacyEngine )
    {
        $this->templateName = $templateName;
        $this->env = $env;
        $this->legacyEngine = $legacyEngine;
    }

    /**
     * Renders the template with the given context and returns it as string.
     *
     * @param array $context An array of parameters to pass to the template
     *
     * @return string The rendered template
     */
    public function render( array $context )
    {
        return $this->legacyEngine->render( $this->templateName, $context );
    }

    /**
     * Displays the template with the given context.
     *
     * @param array $context An array of parameters to pass to the template
     * @param array $blocks  An array of blocks to pass to the template
     */
    public function display( array $context, array $blocks = array() )
    {
        echo $this->render( $context );
    }

    /**
     * Returns the bound environment for this template.
     *
     * @return Twig_Environment The current environment
     */
    public function getEnvironment()
    {
        return $this->env;
    }

    /**
     * @return string
     */
    public function getTemplateName()
    {
        return $this->templateName;
    }
}
