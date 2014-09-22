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

class AwsS3 extends Prefix
{
    public function __construct( $region, $bucket )
    {
        parent::__construct( sprintf( 'http://s3-%s.amazonaws.com/%s/', $region, $bucket ) );
    }
}
