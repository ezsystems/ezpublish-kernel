<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
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
