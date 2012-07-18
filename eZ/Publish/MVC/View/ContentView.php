<?php
/**
 * File containing the ContentView class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\MVC\View;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;

/**
 * Main object to be rendered by the View Manager when viewing a content.
 * Holds the path to the template to be rendered by the view manager and the parameters to inject in it.
 *
 * The template path can be a closure. In that case, the view manager will invoke it instead of loading a template.
 * $parameters will be passed to the callable in addition to the Content or Location object (depending on the context).
 * The prototype of the closure must be :
 * <code>
 * namespace Foo;
 * use eZ\Publish\API\Repository\Values\Content\ContentInfo,
 *     eZ\Publish\API\Repository\Values\Content\Location;
 *
 * // For a content
 * function ( ContentInfo $contentInfo, array $parameters = array() )
 * {
 *     // Do something to render
 *     // Must return a string to display
 * }
 *
 * // For a location
 * function ( Location $location, array $parameters = array() )
 * {
 *     // Do something to render
 *     // Must return a string to display
 * }
 * </code>
 */
class ContentView
{
    /**
     * @var string|\Closure
     */
    protected $templateIdentifier;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @param string|\Closure $templateIdentifier Valid path to the template. Can also be a closure.
     * @param array $parameters Hash of parameters to pass to the template/closure.
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType
     */
    public function __construct( $templateIdentifier, array $parameters = array() )
    {
        if ( !is_string( $templateIdentifier ) || !$templateIdentifier instanceof \Closure )
            throw new InvalidArgumentType( 'templateIdentifier', 'string or \Closure', $templateIdentifier );

        $this->templateIdentifier = $templateIdentifier;
        $this->parameters = array();
    }

    /**
     * @param array $parameters Hash of parameters to pass to the template/closure
     */
    public function setParameters( array $parameters )
    {
        $this->parameters = $parameters;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param $templateIdentifier
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType
     */
    public function setTemplateIdentifier( $templateIdentifier )
    {
        if ( !is_string( $templateIdentifier ) || !$templateIdentifier instanceof \Closure )
            throw new InvalidArgumentType( 'templateIdentifier', 'string or \Closure', $templateIdentifier );

        $this->templateIdentifier = $templateIdentifier;
    }

    /**
     * @return string|\Closure
     */
    public function getTemplateIdentifier()
    {
        return $this->templateIdentifier;
    }
}
