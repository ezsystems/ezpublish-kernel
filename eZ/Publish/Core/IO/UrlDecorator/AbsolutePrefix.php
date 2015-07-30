<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\IO\UrlDecorator;

use eZ\Publish\Core\IO\UrlDecorator;

/**
 * Prefixes the URI with a string, and makes it absolute.
 */
class AbsolutePrefix extends Prefix implements UrlDecorator
{
    /**
     * Makes the prefix absolute.
     *
     * @param $prefix
     */
    public function setPrefix($prefix)
    {
        if ($prefix != '') {
            $urlParts = parse_url($prefix);
            if (isset($urlParts['scheme'])) {
                $prefix = rtrim($prefix, '/') . '/';
            } else {
                $prefix = '/' . trim($prefix, '/') . '/';
            }
        }

        $this->prefix = $prefix;
    }
}
