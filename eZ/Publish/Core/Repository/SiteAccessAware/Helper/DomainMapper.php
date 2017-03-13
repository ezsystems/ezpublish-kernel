<?php

namespace eZ\Publish\Core\Repository\SiteAccessAware\Helper;

use eZ\Publish\SPI\Persistence\Content\Handler as ContentHandler;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as ContentLanguageHandler;

class DomainMapper
{
    /**
     * @var ContentHandler
     */
    protected $contentHandler;

    /**
     * @var ContentLanguageHandler
     */
    protected $contentLanguageHandler;

    public function __construct(ContentHandler $contentHandler, ContentLanguageHandler $contentLanguageHandler)
    {
        $this->contentHandler = $contentHandler;
        $this->contentLanguageHandler = $contentLanguageHandler;
    }
}