<?php
/**
 * File containing the Twig Environment class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Templating\Twig;

use Twig_Environment;
use eZ\Publish\Core\MVC\Legacy\Templating\LegacyEngine;
use eZ\Publish\Core\MVC\Legacy\Templating\Twig\Template;

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
            $this->legacyTemplatesCache[$name] = new Template( $name, $this, $this->legacyEngine );
            return $this->legacyTemplatesCache[$name];
        }

        return parent::loadTemplate( $name, $index );
    }
}
