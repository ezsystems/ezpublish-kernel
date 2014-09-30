<?php
/**
 * This file is part of the eZ Publish Legacy package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 * @version //autogentag//
 */
namespace eZ\Publish\Core\IO\Handler\DFS\UrlDecorator;

use eZ\Publish\Core\IO\Handler\DFS\UrlDecorator;

class Prefix implements UrlDecorator
{
    /**
     * @var
     */
    private $prefix;

    public function __construct( $prefix )
    {
        $this->prefix = $prefix;
    }

    public function decorate( $url )
    {
        return $this->prefix . $url;
    }
}
