<?php
/**
 * This file is part of the eZ Publish Legacy package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 * @version //autogentag//
 */
namespace eZ\Publish\Core\IO\UrlDecorator;

use eZ\Publish\Core\IO\UrlDecorator;

/**
 * Prefixes the URI with a string, and makes it absolute
 */
class AbsolutePrefix implements UrlDecorator
{
    /**
     * @var
     */
    private $prefix;

    /**
     * @param string $prefix uri prefix, without leading or trailing slashes
     */
    public function __construct( $prefix )
    {
        // @todo cleanup at compilation time, and assume string okay
        $this->prefix = '/' . trim( $prefix, '/' ) . '/';
    }

    public function decorate( $url )
    {
        if ( empty( $this->prefix ) )
        {
            return $url;
        }

        return $this->prefix . ltrim( $url, '/' );
    }
}
