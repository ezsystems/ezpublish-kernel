<?php
/**
 * This file is part of the eZ Publish Legacy package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 * @version //autogentag//
 */
namespace eZ\Publish\Core\IO;

interface UrlDecorator
{
    /**
     * Modifies the id into an uri
     *
     * @param string $url
     * @return string
     */
    public function decorate( $id );

    /**
     * Unmodifies an uri into an id
     * @param $url
     *
     * @return mixed
     */
    public function undecorate( $url );
}
