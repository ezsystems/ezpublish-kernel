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

class LocalErrorCollector implements IndexerErrorCollector
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
     * {@inheritdoc}
     */
    public function collect(ContentInfo $contentInfo, $errorMessage)
    {
        $this->indexerErrors[$contentInfo->id] = $errorMessage;

        return $this->continueOnError;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrors()
    {
        return $this->indexerErrors;
    }

    /**
     * {@inheritdoc}
     */
    public function hasErrors()
    {
        return !empty($this->indexerErrors);
    }
}
