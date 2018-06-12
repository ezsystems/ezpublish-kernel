<?php

namespace eZ\Publish\Core\Search\Common\IndexerErrorCollector;

use eZ\Publish\Core\Search\Common\IndexerErrorCollector;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;

class NullErrorCollector implements IndexerErrorCollector
{
    /**
     * {@inheritdoc}
     */
    public function collect(ContentInfo $contentInfo, $errorMessage)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function hasErrors()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrors()
    {
        return [];
    }
}
