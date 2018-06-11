<?php

namespace eZ\Publish\Core\Search\Common\IndexerErrorHandler;

use eZ\Publish\Core\Search\Common\IndexerErrorHandler;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;

class LocalErrorHandler implements IndexerErrorHandler
{
    /**
     * @var array
     */
    private $indexerErrors;

    /**
     * @var bool
     */
    private $continueOnError;

    public function __construct($continueOnError = false)
    {
        $this->continueOnError = $continueOnError;
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\ContentInfo $contentInfo
     * @param string $errorMessage
     *
     * @return bool
     */
    public function handle(ContentInfo $contentInfo, $errorMessage)
    {
        $this->indexerErrors[$contentInfo->id] = $errorMessage;

        return $this->continueOnError;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->indexerErrors;
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->indexerErrors);
    }
}
