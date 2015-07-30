<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace eZ\Publish\SPI\Search;

/**
 * The interface for the main Search Engine handlers.
 */
interface Handler
{
    /**
     * @return \eZ\Publish\SPI\Search\Content\Handler
     */
    public function contentSearchHandler();

    /**
     * @return \eZ\Publish\SPI\Search\Content\Location\Handler
     */
    public function locationSearchHandler();
}
