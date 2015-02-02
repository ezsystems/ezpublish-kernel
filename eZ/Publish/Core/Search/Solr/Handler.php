<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Persistence\Solr;

use eZ\Publish\SPI\Search\Handler as HandlerInterface;
use eZ\Publish\SPI\Search\Content\Handler as ContentSearchHandler;
use eZ\Publish\SPI\Search\Content\Location\Handler as LocationSearchHandler;

/**
 * The main handler for the Solr Search Engine
 */
class Handler implements HandlerInterface
{
    /**
     * @var \eZ\Publish\SPI\Search\Content\Handler
     */
    protected $contentSearchHandler;

    /**
     * @var \eZ\Publish\SPI\Search\Content\Location\Handler
     */
    protected $locationSearchHandler;

    public function __construct(
        ContentSearchHandler $contentSearchHandler,
        LocationSearchHandler $locationSearchHandler
    )
    {
        $this->contentSearchHandler = $contentSearchHandler;
        $this->locationSearchHandler = $locationSearchHandler;
    }

    public function contentSearchHandler()
    {
        return $this->contentSearchHandler;
    }

    public function locationSearchHandler()
    {
        return $this->locationSearchHandler;
    }
}
