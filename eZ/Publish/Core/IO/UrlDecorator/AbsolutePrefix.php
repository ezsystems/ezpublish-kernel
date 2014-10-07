<?php
/**
 * This file is part of the eZ Publish Legacy package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 * @version //autogentag//
 */
namespace eZ\Publish\Core\IO\UrlDecorator;

use eZ\Publish\Core\IO\Exception\InvalidBinaryFileIdException;
use eZ\Publish\Core\IO\UrlDecorator;

/**
 * Prefixes the URI with a string, and makes it absolute
 */
class AbsolutePrefix implements UrlDecorator
{
    /**
     * The URI absolute prefix.
     * @var string
     */
    private $prefix;

    /**
     * @param string $prefix uri prefix. Will be nested within '/' on both ends.
     */
    public function __construct( $prefix = null )
    {
        if ( $prefix !== null )
        {
            $this->setPrefix( $prefix );
        }
    }

    public function setPrefix( $prefix )
    {
        if ( $prefix != '' )
            $prefix = '/' . trim( $prefix, '/' ) . '/';

        $this->prefix = $prefix;
    }

    public function decorate( $id )
    {
        if ( empty( $this->prefix ) )
        {
            return $id;
        }

        return $this->prefix . ltrim( $id, '/' );
    }

    public function undecorate( $url )
    {
        if ( empty( $this->prefix ) )
        {
            return $url;
        }

        if ( strpos( $url, $this->prefix ) !== 0 )
        {
            throw new InvalidBinaryFileIdException( $url );
        }

        return substr( $url, strlen( $this->prefix ) );
    }
}
