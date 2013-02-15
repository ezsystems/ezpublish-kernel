<?php
/**
 * File containing the LegacyExtension class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Templating\Twig\Extension;

use eZ\Publish\Core\MVC\Legacy\Templating\Twig\TokenParser\LegacyIncludeParser;
use eZ\Publish\Core\MVC\Legacy\Templating\LegacyEngine;
use eZTemplate;
use Twig_Extension;

/**
 * Twig extension for eZ Publish legacy
 */
class LegacyExtension extends Twig_Extension
{
    /**
     * @var \eZ\Publish\Core\MVC\Legacy\Templating\LegacyEngine
     */
    private $legacyEngine;

    public function __construct( LegacyEngine $legacyEngine )
    {
        $this->legacyEngine = $legacyEngine;
    }

    /**
     * Renders a legacy template.
     *
     * @param string $tplPath Path to template (i.e. "design:setup/info.tpl")
     * @param array $params Parameters to pass to template.
     *                      Consists of a hash with key as the variable name available in the template.
     *
     * @return string The legacy template result
     *
     * @deprecated since 5.1
     */
    public function renderTemplate( $tplPath, array $params = array() )
    {
        return $this->legacyEngine->render( $tplPath, $params );
    }

    public function getTokenParsers()
    {
        return array(
            new LegacyIncludeParser()
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'ezpublish.legacy';
    }
}
