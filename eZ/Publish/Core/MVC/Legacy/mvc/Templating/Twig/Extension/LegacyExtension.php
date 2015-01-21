<?php
/**
 * File containing the LegacyExtension class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Templating\Twig\Extension;

use eZ\Publish\Core\MVC\Legacy\Templating\LegacyHelper;
use eZ\Publish\Core\MVC\Legacy\Templating\Twig\TokenParser\LegacyIncludeParser;
use eZ\Publish\Core\MVC\Legacy\Templating\LegacyEngine;
use Twig_Extension;
use Twig_SimpleFunction;
use Twig_Environment;

/**
 * Twig extension for eZ Publish legacy
 */
class LegacyExtension extends Twig_Extension
{
    /**
     * @var \eZ\Publish\Core\MVC\Legacy\Templating\LegacyEngine
     */
    private $legacyEngine;

    /** @var  \eZ\Publish\Core\MVC\Legacy\Templating\LegacyHelper */
    private $legacyHelper;

    /** @var  string */
    private $jsTemplate;

    /** @var  string */
    private $cssTemplate;

    /**
     * The Twig environment
     *
     * @var \Twig_Environment
     */
    protected $environment;

    public function __construct(
        LegacyEngine $legacyEngine,
        LegacyHelper $legacyHelper,
        $jsTemplate,
        $cssTemplate
    )
    {
        $this->legacyEngine = $legacyEngine;
        $this->legacyHelper = $legacyHelper;
        $this->jsTemplate = $jsTemplate;
        $this->cssTemplate = $cssTemplate;
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

    /**
     * Initializes the template runtime (aka Twig environment).
     *
     * @param \Twig_Environment $environment
     */
    public function initRuntime( Twig_Environment $environment )
    {
        $this->environment = $environment;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction(
                'ez_legacy_render_js',
                array( $this, 'renderLegacyJs' ),
                array( 'is_safe' => array( 'html' ) )
            ),
            new Twig_SimpleFunction(
                'ez_legacy_render_css',
                array( $this, 'renderLegacyCss' ),
                array( 'is_safe' => array( 'html' ) )
            ),
        );
    }

    /**
     * Generates style tags to be embedded in the page
     *
     * @return string html script and style tags
     */
    public function renderLegacyJs()
    {
        $jsFiles = array();
        $jsCodeLines = array();

        foreach ( $this->legacyHelper->get( 'js_files', array() ) as $jsItem )
        {
            // List of items can contain empty elements, path to files or code
            if ( !empty( $jsItem ) )
            {
                if ( isset( $jsItem[4] ) && $this->isFile( $jsItem, '.js' ) )
                {
                    $jsFiles[] = $jsItem;
                }
                else
                {
                    $jsCodeLines[] = $jsItem;
                }
            }
        }

        return $this->environment->render(
            $this->jsTemplate,
            array(
                'js_files' => $jsFiles,
                'js_code_lines' => $jsCodeLines
            )
        );
    }

    /**
     * Generates script tags to be embedded in the page
     *
     * @return string html script and style tags
     */
    public function renderLegacyCss( )
    {
        $cssFiles = array();
        $cssCodeLines = array();

        foreach ( $this->legacyHelper->get( 'css_files', array() ) as $cssItem )
        {
            // List of items can contain empty elements, path to files or code
            if ( !empty( $cssItem ) )
            {
                if ( isset( $cssItem[5] ) && $this->isFile( $cssItem, '.css' ) )
                {
                    $cssFiles[] = $cssItem;
                }
                else
                {
                    $cssCodeLines[] = $cssItem;
                }
            }
        }

        return $this->environment->render(
            $this->cssTemplate,
            array(
                'css_files' => $cssFiles,
                'css_code_lines' => $cssCodeLines
            )
        );
    }

    /**
     * Is the provided item (path or link) a file or code. Based on legacy's rules (ezjscpacker.php)
     *
     * @param $item string to be tested
     * @param $extension string extension of the file
     *
     * @return bool true if item is a file
     */
    private function isFile( $item, $extension )
    {
        return
            strpos( $item, 'http://' ) === 0
            || strpos( $item, 'https://' ) === 0
            || strripos( $item, $extension ) === ( strlen( $item ) - strlen( $extension ) );
    }

}
