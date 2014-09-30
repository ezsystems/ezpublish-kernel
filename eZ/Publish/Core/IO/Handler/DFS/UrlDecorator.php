<?php
/**
 * This file is part of the eZ Publish Legacy package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 * @version //autogentag//
 */
namespace eZ\Publish\Core\IO\Handler\DFS;


interface UrlDecorator
{
    /**
     * @param string $url
     * @return string
     */
    public function decorate( $url );
}
