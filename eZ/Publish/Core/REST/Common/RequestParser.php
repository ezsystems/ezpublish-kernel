<?php

/**
 * File containing the RequestParser interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common;

/**
 * Interface for Request parsers.
 */
interface RequestParser
{
    /**
     * Parse URL and return the IDs contained in the URL.
     *
     * @param string $url
     *
     * @return array
     */
    public function parse($url);

    /**
     * Generate a URL of the given type from the specified values.
     *
     * @param string $type
     * @param array $values
     *
     * @return string
     */
    public function generate($type, array $values = []);

    /**
     * Tries to match $href as a route, and returns the value of $attribute from the result.
     *
     * @param string $href
     * @param string $attribute
     *
     * @return mixed|false
     */
    public function parseHref($href, $attribute);
}
