<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\Core\Search\Legacy;

use eZ\Publish\SPI\Search\Handler as HandlerInterface;
use eZ\Publish\SPI\Search\Content\Handler as ContentSearchHandler;

/**
 * The main handler for the Legacy Search Engine.
 */
class Handler implements HandlerInterface
{
    /**
     * @var \eZ\Publish\SPI\Search\Content\Handler
     */
    protected $contentSearchHandler;

    public function __construct(ContentSearchHandler $contentSearchHandler)
    {
        $this->contentSearchHandler = $contentSearchHandler;
    }

    public function contentSearchHandler()
    {
        return $this->contentSearchHandler;
    }
}
