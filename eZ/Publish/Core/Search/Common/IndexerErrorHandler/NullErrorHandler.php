<?php

namespace eZ\Publish\Core\Search\Common\IndexerErrorHandler;

use eZ\Publish\Core\Search\Common\IndexerErrorHandler;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;

class NullErrorHandler implements IndexerErrorHandler
{
    public function handle(ContentInfo $contentInfo, $errorMessage)
    {
        return false;
    }
}
