<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\IO;

/**
 * Modifies, both way, and URI.
 */
interface UrlDecorator
{
    /**
     * Decorates $uri.
     *
     * @param string $uri
     *
     * @return string Decorated string
     */
    public function decorate($uri);

    /**
     * Un-decorates decorated $uri.
     *
     * @param $uri
     *
     * @return string Un-decorated string
     */
    public function undecorate($uri);
}
