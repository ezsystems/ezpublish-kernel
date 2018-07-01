<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Common\BackgroundIndexer;

use eZ\Publish\Core\Search\Common\BackgroundIndexer as BackgroundIndexerInterface;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content\Location;

/**
 * Null indexer, does nothing, for default use when non has been configured.
 */
class NullIndexer implements BackgroundIndexerInterface
{
    public function registerContent(ContentInfo $contentInfo)
    {
    }

    public function registerLocation(Location $location)
    {
    }
}
