<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
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
