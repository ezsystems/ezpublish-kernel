<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\Core\Search\Common\IncrementalIndexer;
use eZ\Publish\Core\Search\Legacy\Content\Handler as LegacySearchHandler;
use RuntimeException;

class Indexer extends IncrementalIndexer
{
    public function getName()
    {
        return 'eZ Platform Legacy (SQL) Search Engine';
    }

    public function updateSearchIndex(array $contentIds, $commit)
    {
        $this->checkSearchEngine();
        $contentHandler = $this->persistenceHandler->contentHandler();
        foreach ($contentIds as $contentId) {
            try {
                $info = $contentHandler->loadContentInfo($contentId);
                if ($info->isPublished) {
                    $this->searchHandler->indexContent(
                        $contentHandler->load($info->id, $info->currentVersionNo)
                    );
                    continue;
                }
            } catch (NotFoundException $e) {
                // Catch this so we delete the index for this content below
            }

            $this->searchHandler->deleteContent($contentId);
        }
    }

    public function purge()
    {
        $this->checkSearchEngine();
        $this->searchHandler->purgeIndex();
    }

    private function checkSearchEngine()
    {
        if (!$this->searchHandler instanceof LegacySearchHandler) {
            throw new RuntimeException(
                sprintf(
                    'Expected to find an instance of %s, but found %s',
                    LegacySearchHandler::class,
                    get_class($this->searchHandler)
                )
            );
        }
    }
}
