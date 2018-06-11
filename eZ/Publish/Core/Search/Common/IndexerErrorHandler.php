<?php

namespace eZ\Publish\Core\Search\Common;

use eZ\Publish\SPI\Persistence\Content\ContentInfo;

/**
 * Interface for handling errors during indexing.
 */
interface IndexerErrorHandler
{
    /**
     * Handles indexer error.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ContentInfo $contentInfo
     * @param string $errorMessage
     *
     * @return bool
     */
    public function handle(ContentInfo $contentInfo, $errorMessage);
}
