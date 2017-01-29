<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger;

use eZ\Publish\Core\MVC\Symfony\Cache\Http\ConfigurableResponseCacheConfigurator;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseConfigurator\ResponseCacheConfigurator;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tags a Response based on data from a value.
 */
interface ResponseTagger
{
    /**
     * Extracts tags from a value, and adds them using the Configurator.
     *
     * @param ResponseCacheConfigurator $configurator
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @param mixed $value
     *
     * @return \eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\ResponseTagger
     */
    public function tag(ResponseCacheConfigurator $configurator, Response $response, $value);
}
