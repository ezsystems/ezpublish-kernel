<?php

namespace eZ\Publish\Core\Search\Common;

use eZ\Publish\SPI\Persistence\Content\ContentInfo;

/**
 * Interface for handling errors during indexing.
 */
interface IndexerErrorCollector
{
    /**
     * Collects indexer error.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ContentInfo $contentInfo
     * @param string $errorMessage
     *
     * @return bool
     */
    public function collect(ContentInfo $contentInfo, $errorMessage);

    /**
     * @return bool
     */
    public function hasErrors();

    /**
     * @return array
     */
    public function getErrors();
}
