<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Search\Legacy\Content;

use Doctrine\DBAL\Connection;
use Exception;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\Core\Search\Common\IncrementalIndexer;
use eZ\Publish\Core\Search\Legacy\Content\Handler as LegacySearchHandler;
use eZ\Publish\SPI\Persistence\Content\ContentInfo;
use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;
use Psr\Log\LoggerInterface;

class Indexer extends IncrementalIndexer
{
    public function __construct(
        LoggerInterface $logger,
        PersistenceHandler $persistenceHandler,
        Connection $connection,
        LegacySearchHandler $searchHandler
    ) {
        parent::__construct($logger, $persistenceHandler, $connection, $searchHandler);
    }

    public function getName()
    {
        return 'eZ Platform Legacy (SQL) Search Engine';
    }

    public function updateSearchIndex(array $contentIds, $commit)
    {
        $contentHandler = $this->persistenceHandler->contentHandler();
        foreach ($contentIds as $contentId) {
            try {
                $info = $contentHandler->loadContentInfo($contentId);
                if ($info->status === ContentInfo::STATUS_PUBLISHED) {
                    $this->searchHandler->indexContent(
                        $contentHandler->load($info->id, $info->currentVersionNo)
                    );
                } else {
                    $this->searchHandler->deleteContent($contentId);
                }
            } catch (NotFoundException $e) {
                $this->searchHandler->deleteContent($contentId);
            } catch (Exception $e) {
                $context = [
                    'contentId' => $contentId,
                    'error' => $e->getMessage(),
                ];
                $this->logger->error('Unable to index the content', $context);
            }
        }
    }

    public function purge()
    {
        $this->searchHandler->purgeIndex();
    }
}
