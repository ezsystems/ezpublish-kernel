<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\SPI\Search\Indexer;

use eZ\Publish\SPI\Persistence\Content;

/**
 * Indexer for handlers that index Content.
 */
interface ContentIndexer
{
    /**
     * Indexes a content object.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $content
     */
    public function indexContent(Content $content);

    /**
     * Deletes a content object from the index.
     *
     * @param string|int $contentId
     * @param int|null $versionId
     */
    public function deleteContent($contentId, $versionId = null);
}
