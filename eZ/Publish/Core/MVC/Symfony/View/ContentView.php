<?php
/**
 * File containing the ContentView class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View;

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
class ContentView implements ContentViewInterface
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
    public function __construct( $templateIdentifier = null, array $parameters = array() )
    {
        if ( isset( $templateIdentifier ) && !is_string( $templateIdentifier ) && !$templateIdentifier instanceof \Closure )
            throw new InvalidArgumentType( 'templateIdentifier', 'string or \Closure', $templateIdentifier );

        $this->templateIdentifier = $templateIdentifier;
        $this->parameters = $parameters;
    }

    /**
     * @param array $parameters Hash of parameters to pass to the template/closure
     */
    public function setParameters( array $parameters )
    {
        $this->parameters = $parameters;
    }

    /**
     * Adds a hash of parameters to the existing parameters
     *
     * @param array $parameters
     */
    public function addParameters( array $parameters )
    {
        $this->parameters += $parameters;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Checks if $parameterName exists
     *
     * @param string $parameterName
     *
     * @return boolean
     */
    public function hasParameter( $parameterName )
    {
        return isset( $this->parameters[$parameterName] );
    }

    /**
     * Returns parameter value by $parameterName.
     * Throws an \InvalidArgumentException if $parameterName is not set.
     *
     * @param string $parameterName
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    public function getParameter( $parameterName )
    {
        if ( $this->hasParameter( $parameterName ) )
            return $this->parameters[$parameterName];

        throw new \InvalidArgumentException( "Parameter '$parameterName' is not set." );
    }

    /**
     * @param string|\Closure $templateIdentifier
     *
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
