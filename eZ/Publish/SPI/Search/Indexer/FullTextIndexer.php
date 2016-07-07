<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Search\Indexer;

use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Search\Indexer;

/**
 * Indexer for handlers that index full text of Content.
 */
interface FullTextIndexer extends Indexer
{
    /**
     * Indexes a content object.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     */
    public function indexContent(Content $content);
}
