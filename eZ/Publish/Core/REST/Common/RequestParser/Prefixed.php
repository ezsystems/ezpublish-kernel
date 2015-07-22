<?php

/**
 * File containing the Prefixed class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\RequestParser;

use eZ\Publish\Core\REST\Common\RequestParser\EzPublish as EzPublishRequestParser;

class Prefixed extends EzPublishRequestParser
{
    /**
     * @var string
     */
    protected $prefix;

    public function __construct($prefix = '', array $map = array())
    {
        $this->prefix = $prefix;
        parent::__construct($map);
    }

    public function generate($type, array $values = array())
    {
        return $this->prefix . parent::generate($type, $values);
    }

    public function parse($type, $url)
    {
        if (strpos($url, $this->prefix) === 0) {
            $url = substr($url, strlen($this->prefix));
        }

        return parent::parse($type, $url);
    }
}
