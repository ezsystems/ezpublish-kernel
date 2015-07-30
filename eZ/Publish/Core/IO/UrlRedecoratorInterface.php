<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Publish\Core\IO;

use eZ\Publish\Core\IO\Exception\InvalidBinaryFileIdException;

/**
 * Converts an URL from one decorator to another.
 *
 * ```php
 * $redecorator = new UrlRedecorator(
 *   new Prefix( 'a' ),
 *   new Prefix( 'b' )
 * );
 *
 * $redecorator->redecorateFromSource( 'a/url' );
 * // 'b/url'
 *
 * $redecorator->redecorateFromTarget( 'b/url' );
 * // 'a/url'
 * ```
 */
interface UrlRedecoratorInterface
{
    /**
     * Redecorates $uri from source to target.
     *
     * @param string $uri
     *
     * @return string
     *
     * @throws InvalidBinaryFileIdException If $uri couldn't be interpreted b y the target decorator
     */
    public function redecorateFromSource($uri);

    /**
     * Redecorates $uri from source to target.
     *
     * @param string $uri
     *
     * @return string
     *
     * @throws InvalidBinaryFileIdException If $uri couldn't be interpreted b y the target decorator
     */
    public function redecorateFromTarget($uri);
}
