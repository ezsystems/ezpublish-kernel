<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\Core\Search\Legacy\Content\Handler as LegacySearchHandler;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Content\Handler as ContentHandler;
use eZ\Publish\SPI\Search\IncrementalIndexer;
use Psr\Log\LoggerInterface;

class Indexer implements IncrementalIndexer
{
    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /** @var \eZ\Publish\SPI\Persistence\Content\Handler */
    private $contentHandler;

    /** @var \eZ\Publish\Core\Search\Legacy\Content\Handler */
    private $searchHandler;

    public function __construct(
        LoggerInterface $logger,
        ContentHandler $contentHandler,
        LegacySearchHandler $searchHandler
    ) {
        $this->logger = $logger;
        $this->contentHandler = $contentHandler;
        $this->searchHandler = $searchHandler;
    }

    public function getName(): string
    {
        return 'eZ Platform Legacy (SQL) Search Engine';
    }

    public function updateSearchIndex(array $contentIds, $commit)
    {
        foreach ($contentIds as $contentId) {
            try {
                $info = $this->contentHandler->loadContentInfo($contentId);
                if ($info->status === ContentInfo::STATUS_PUBLISHED) {
                    $this->searchHandler->indexContent(
                        $this->contentHandler->load($info->id, $info->currentVersionNo)
                    );
                } else {
                    $this->searchHandler->deleteContent($contentId);
                }
            } catch (NotFoundException $e) {
                $this->searchHandler->deleteContent($contentId);
            }
        }
    }

    public function purge()
    {
        $this->searchHandler->purgeIndex();
    }
}
