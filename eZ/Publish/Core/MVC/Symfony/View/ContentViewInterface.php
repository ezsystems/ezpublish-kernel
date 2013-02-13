<?php
/**
 * File containing the ContentViewInterface interface.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\View;

interface ContentViewInterface
{
    /**
     * Sets $templateIdentifier to the content view.
     * Can be either a valid template identifier such as "MyBundle:subfolder:my_template.html.twig" or a closure.
     * In the case of a closure, it will receive an array of parameters as an argument and must return the result to display.
     *
     * Must throw a \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType exception if $templateIdentifier is invalid.
     *
     * @param string|\Closure $templateIdentifier
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType
     */
    public function setTemplateIdentifier( $templateIdentifier );

    /**
     * Returns the registered template identifier.
     *
     * @return string|\Closure
     */
    public function getTemplateIdentifier();

    /**
     * Sets $parameters that will later be injected to the template/closure.
     * If some parameters were already present, $parameters will replace them.
     *
     * @param array $parameters Hash of parameters
     */
    public function setParameters( array $parameters );

    /**
     * Adds a hash of parameters to the existing parameters.
     *
     * @param array $parameters
     */
    public function addParameters( array $parameters );

    /**
     * Returns registered parameters.
     *
     * @return array
     */
    public function getParameters();

    /**
     * Checks if $parameterName exists.
     *
     * @param string $parameterName
     *
     * @return boolean
     */
    public function hasParameter( $parameterName );

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
    public function getParameter( $parameterName );
}
