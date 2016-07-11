<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Search\Indexing;

use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Search\Indexing;

/**
 * Interface for indexing Content items in the search backend.
 */
interface ContentIndexing extends Indexing
{
    /**
     * Indexes a content object.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     */
    public function indexContent(Content $content);
}
