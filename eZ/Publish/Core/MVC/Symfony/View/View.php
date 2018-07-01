<?php

/**
 * File containing the ContentViewInterface interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerReference;

/**
 * Main interface for Views of Value objects (content, block...).
 */
interface View
{
    /**
     * Sets $templateIdentifier to the content view.
     * Can be either a valid template identifier such as "MyBundle:subfolder:my_template.html.twig" or a closure.
     * In the case of a closure, it will receive an array of parameters as an argument and must return the result to display.
     *
     * The prototype of the closure must be :
     * <code>
     * function (array $params = [])
     * {
     *     // Do something to render
     *     // Must return a string to display
     * }
     * </code>
     * Must throw a \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType exception if $templateIdentifier is invalid.
     *
     * @param string|\Closure $templateIdentifier
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentType
     */
    public function setTemplateIdentifier($templateIdentifier);

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
    public function setParameters(array $parameters);

    /**
     * Adds a hash of parameters to the existing parameters.
     *
     * @param array $parameters
     */
    public function addParameters(array $parameters);

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
     * @return bool
     */
    public function hasParameter($parameterName);

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
    public function getParameter($parameterName);

    /**
     * Injects the config hash that was used to match and generate the current view.
     * Typically, the hash would have as keys:
     *  - template : The template that has been matched
     *  - match : The matching configuration, including the matcher "identifier" and what has been passed to it.
     *  - matcher : The matcher object.
     *
     * @param array $config
     */
    public function setConfigHash(array $config);

    /**
     * Returns the config hash.
     *
     * @return array|null
     */
    public function getConfigHash();

    public function setViewType($viewType);

    public function getViewType();

    public function setControllerReference(ControllerReference $controllerReference);

    /**
     * @return ControllerReference
     */
    public function getControllerReference();

    /**
     * Sets a pre-configured Response that will be used to render the View.
     *
     * @param Response $response
     */
    public function setResponse(Response $response);

    /**
     * Returns the pre-configured Response.
     *
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    public function getResponse();
}
