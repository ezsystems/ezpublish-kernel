<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\IO\UrlDecorator;

use eZ\Publish\Core\IO\Exception\InvalidBinaryPrefixException;
use eZ\Publish\Core\IO\UrlDecorator;

/**
 * Prefixes the URI with a string. Ensures an initial / in the parameter.
 */
class Prefix implements UrlDecorator
{
    /**
     * The URI prefix.
     *
     * @var string
     */
    protected $prefix;

    public function __construct($prefix = null)
    {
        if ($prefix !== null) {
            $this->setPrefix($prefix);
        }
    }

    public function setPrefix($prefix)
    {
        $this->prefix = trim($prefix, '/') . '/';
    }

    public function decorate($id)
    {
        if (empty($this->prefix)) {
            return $id;
        }

        return $this->prefix . trim($id, '/');
    }

    public function undecorate($url)
    {
        if (empty($this->prefix)) {
            return $url;
        }

        if (strpos($url, $this->prefix) !== 0) {
            throw new InvalidBinaryPrefixException($url, $this->prefix);
        }

        return trim(substr($url, strlen($this->prefix)), '/');
    }
}
