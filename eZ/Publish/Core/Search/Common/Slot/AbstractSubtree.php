<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Common\Slot;

use eZ\Publish\Core\Search\Common\Slot;

/**
 * A base Search Engine slot providing indexing of the subtree.
 */
abstract class AbstractSubtree extends Slot
{
    protected function indexSubtree($locationId)
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
