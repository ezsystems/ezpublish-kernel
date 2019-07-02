<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Common\EventSubscriber;

use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;
use eZ\Publish\SPI\Search\Handler as SearchHandler;

/**
 * @internal
 */
abstract class AbstractSearchEventSubscriber
{
    /** @var \eZ\Publish\SPI\Search\Handler */
    protected $searchHandler;

    /** @var \eZ\Publish\SPI\Persistence\Handler */
    protected $persistenceHandler;

    public function __construct(
        SearchHandler $searchHandler,
        PersistenceHandler $persistenceHandler
    ) {
        $this->searchHandler = $searchHandler;
        $this->persistenceHandler = $persistenceHandler;
    }

    public function indexSubtree(int $locationId): void
    {
        $contentHandler = $this->persistenceHandler->contentHandler();
        $locationHandler = $this->persistenceHandler->locationHandler();

        $processedContentIdSet = [];
        $subtreeIds = $locationHandler->loadSubtreeIds($locationId);
        $contentInfoList = $contentHandler->loadContentInfoList(array_values($subtreeIds));

        foreach ($subtreeIds as $locationId => $contentId) {
            $this->searchHandler->indexLocation(
                $locationHandler->load($locationId)
            );

            if (isset($processedContentIdSet[$contentId])) {
                continue;
            }

            $this->searchHandler->indexContent(
                $contentHandler->load(
                    $contentId,
                    $contentInfoList[$contentId]->currentVersionNo
                )
            );

            // Content could be found in multiple Locations of the subtree,
            // but we need to (re)index it only once
            $processedContentIdSet[$contentId] = true;
        }
    }
}
