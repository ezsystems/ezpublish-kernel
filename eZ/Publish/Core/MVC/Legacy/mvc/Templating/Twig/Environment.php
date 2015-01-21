<?php
/**
 * File containing the Twig Environment class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Templating\Twig;

use Twig_Environment;
use Twig_Error_Loader;
use eZ\Publish\Core\MVC\Legacy\Templating\LegacyEngine;

class Environment extends Twig_Environment
{
    /**
     * @var \eZ\Publish\Core\MVC\Legacy\Templating\LegacyEngine
     */
    private $legacyEngine;

    /**
     * Template objects indexed by their identifier.
     *
     * @var \eZ\Publish\Core\MVC\Legacy\Templating\Twig\Template[]
     */
    protected $legacyTemplatesCache = array();

    public function setEzLegacyEngine( LegacyEngine $legacyEngine )
    {
        $this->legacyEngine = $legacyEngine;
    }

    public function loadTemplate( $name, $index = null )
    {
        // If legacy engine supports given template, delegate it.
        if ( is_string( $name ) && isset( $this->legacyTemplatesCache[$name] ) )
            return $this->legacyTemplatesCache[$name];

        if ( is_string( $name ) && $this->legacyEngine->supports( $name ) )
        {
            if ( !$this->legacyEngine->exists( $name ) )
            {
                throw new Twig_Error_Loader( "Unable to find the template \"$name\"" );
            }

            $this->legacyTemplatesCache[$name] = new Template( $name, $this, $this->legacyEngine );
            return $this->legacyTemplatesCache[$name];
        }

        return parent::loadTemplate( $name, $index );
    }
}
