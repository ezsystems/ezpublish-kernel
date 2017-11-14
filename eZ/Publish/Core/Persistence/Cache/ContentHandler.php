<?php

/**
 * File containing the ContentHandler implementation.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Cache;

use eZ\Publish\API\Repository\Values\Content\Relation as APIRelation;
use eZ\Publish\SPI\Persistence\Content\Handler as ContentHandlerInterface;
use eZ\Publish\SPI\Persistence\Content;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\SPI\Persistence\Content\CreateStruct;
use eZ\Publish\SPI\Persistence\Content\UpdateStruct;
use eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct as RelationCreateStruct;

/**
 * @see \eZ\Publish\SPI\Persistence\Content\Handler
 */
class ContentHandler extends AbstractHandler implements ContentHandlerInterface
{
    const ALL_TRANSLATIONS_KEY = '0';

    /**
     * {@inheritdoc}
     */
    public function create(CreateStruct $struct)
    {
        // Cached on demand when published or loaded
        $this->logger->logCall(__METHOD__, array('struct' => $struct));

        return $this->persistenceHandler->contentHandler()->create($struct);
    }

    /**
     * {@inheritdoc}
     */
    public function createDraftFromVersion($contentId, $srcVersion, $userId)
    {
        $this->logger->logCall(__METHOD__, array('content' => $contentId, 'version' => $srcVersion, 'user' => $userId));

        return $this->persistenceHandler->contentHandler()->createDraftFromVersion($contentId, $srcVersion, $userId);
    }

    /**
     * {@inheritdoc}
     */
    public function copy($contentId, $versionNo = null)
    {
        $this->logger->logCall(__METHOD__, array('content' => $contentId, 'version' => $versionNo));

        return $this->persistenceHandler->contentHandler()->copy($contentId, $versionNo);
    }

    /**
     * {@inheritdoc}
     */
    public function load($contentId, $version, array $translations = null)
    {
        $translationsKey = empty($translations) ? self::ALL_TRANSLATIONS_KEY : implode('|', $translations);
        $cache = $this->cache->getItem('content', $contentId, $version, $translationsKey);
        $content = $cache->get();
        if ($cache->isMiss()) {
            $this->logger->logCall(__METHOD__, array('content' => $contentId, 'version' => $version, 'translations' => $translations));
            $content = $this->persistenceHandler->contentHandler()->load($contentId, $version, $translations);
            $cache->set($content)->save();
        }

        return $content;
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentInfo($contentId)
    {
        $cache = $this->cache->getItem('content', 'info', $contentId);
        $contentInfo = $cache->get();
        if ($cache->isMiss()) {
            $this->logger->logCall(__METHOD__, array('content' => $contentId));
            $cache->set($contentInfo = $this->persistenceHandler->contentHandler()->loadContentInfo($contentId))->save();
        }

        return $contentInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentInfoByRemoteId($remoteId)
    {
        $cache = $this->cache->getItem('content', 'info', 'remoteId', $remoteId);
        $contentInfo = $cache->get();
        if ($cache->isMiss()) {
            $this->logger->logCall(__METHOD__, array('content' => $remoteId));
            $cache->set($contentInfo = $this->persistenceHandler->contentHandler()->loadContentInfoByRemoteId($remoteId))->save();
        }

        return $contentInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function loadVersionInfo($contentId, $versionNo)
    {
        $this->logger->logCall(__METHOD__, array('content' => $contentId, 'version' => $versionNo));

        return $this->persistenceHandler->contentHandler()->loadVersionInfo($contentId, $versionNo);
    }

    /**
     * {@inheritdoc}
     */
    public function loadDraftsForUser($userId)
    {
        $this->logger->logCall(__METHOD__, array('user' => $userId));

        return $this->persistenceHandler->contentHandler()->loadDraftsForUser($userId);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($contentId, $status, $version)
    {
        $this->logger->logCall(__METHOD__, array('content' => $contentId, 'status' => $status, 'version' => $version));
        $return = $this->persistenceHandler->contentHandler()->setStatus($contentId, $status, $version);

        $this->cache->clear('content', $contentId, $version);
        if ($status === VersionInfo::STATUS_PUBLISHED) {
            $this->cache->clear('content', 'info', $contentId);
            $this->cache->clear('content', 'info', 'remoteId');
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function updateMetadata($contentId, MetadataUpdateStruct $struct)
    {
        $this->logger->logCall(__METHOD__, array('content' => $contentId, 'struct' => $struct));

        $contentInfo = $this->persistenceHandler->contentHandler()->updateMetadata($contentId, $struct);

        $this->cache->clear('content', $contentId, $contentInfo->currentVersionNo);
        $this->cache->clear('content', 'info', $contentId);

        if ($struct->remoteId) {
            // remote id changed
            $this->cache->clear('content', 'info', 'remoteId');
        } else {
            $this->cache->clear('content', 'info', 'remoteId', $contentInfo->remoteId);
        }

        return $contentInfo;
    }

    /**
     * {@inheritdoc}
     */
    public function updateContent($contentId, $versionNo, UpdateStruct $struct)
    {
        $this->logger->logCall(__METHOD__, array('content' => $contentId, 'version' => $versionNo, 'struct' => $struct));
        $content = $this->persistenceHandler->contentHandler()->updateContent($contentId, $versionNo, $struct);
        $this->cache->clear('content', $contentId, $versionNo);

        return $content;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteContent($contentId)
    {
        $this->logger->logCall(__METHOD__, array('content' => $contentId));

        // Load locations and reverse field relations first
        $locations = $this->persistenceHandler->locationHandler()->loadLocationsByContent($contentId);
        $reverseRelations = $this->persistenceHandler->contentHandler()->loadReverseRelations(
            $contentId,
            APIRelation::FIELD
        );

        $return = $this->persistenceHandler->contentHandler()->deleteContent($contentId);

        // Clear cache of the reversely related Content after main action has executed
        foreach ($reverseRelations as $relation) {
            $this->cache->clear('content', $relation->sourceContentId);
        }

        $this->cache->clear('content', $contentId);
        $this->cache->clear('content', 'info', $contentId);
        $this->cache->clear('content', 'info', 'remoteId');
        $this->cache->clear('location', 'subtree');

        foreach ($locations as $location) {
            $this->cache->clear('location', $location->id);
        }

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteVersion($contentId, $versionNo)
    {
        $this->logger->logCall(__METHOD__, array('content' => $contentId, 'version' => $versionNo));
        $return = $this->persistenceHandler->contentHandler()->deleteVersion($contentId, $versionNo);

        $this->cache->clear('content', $contentId, $versionNo);
        $this->cache->clear('content', 'info', $contentId);
        $this->cache->clear('content', 'info', 'remoteId');
        $this->cache->clear('location', 'subtree');

        return $return;
    }

    /**
     * {@inheritdoc}
     */
    public function listVersions($contentId, $status = null, $limit = -1)
    {
        $this->logger->logCall(__METHOD__, array('content' => $contentId, 'status' => $status));

        return $this->persistenceHandler->contentHandler()->listVersions($contentId, $status, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function addRelation(RelationCreateStruct $relation)
    {
        $this->logger->logCall(__METHOD__, array('struct' => $relation));

        return $this->persistenceHandler->contentHandler()->addRelation($relation);
    }

    /**
     * {@inheritdoc}
     */
    public function removeRelation($relationId, $type)
    {
        $this->logger->logCall(__METHOD__, array('relation' => $relationId, 'type' => $type));
        $this->persistenceHandler->contentHandler()->removeRelation($relationId, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function loadRelations($sourceContentId, $sourceContentVersionNo = null, $type = null)
    {
        $this->logger->logCall(
            __METHOD__,
            array(
                'content' => $sourceContentId,
                'version' => $sourceContentVersionNo,
                'type' => $type,
            )
        );

        return $this->persistenceHandler->contentHandler()->loadRelations($sourceContentId, $sourceContentVersionNo, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function loadReverseRelations($destinationContentId, $type = null)
    {
        $this->logger->logCall(__METHOD__, array('content' => $destinationContentId, 'type' => $type));

        return $this->persistenceHandler->contentHandler()->loadReverseRelations($destinationContentId, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function publish($contentId, $versionNo, MetadataUpdateStruct $struct)
    {
        $this->logger->logCall(__METHOD__, array('content' => $contentId, 'version' => $versionNo, 'struct' => $struct));
        $content = $this->persistenceHandler->contentHandler()->publish($contentId, $versionNo, $struct);

        $this->cache->clear('content', $contentId);
        $this->cache->clear('content', 'info', 'remoteId');
        $this->cache->clear('location', 'subtree');

        // warm up cache
        $contentInfo = $content->versionInfo->contentInfo;
        $this->cache
            ->getItem('content', $contentInfo->id, $content->versionInfo->versionNo, self::ALL_TRANSLATIONS_KEY)
            ->set($content)
            ->save();
        $this->cache->getItem('content', 'info', $contentInfo->id)->set($contentInfo)->save();

        return $content;
    }

    /**
     * {@inheritdoc}
     */
    public function removeTranslationFromContent($contentId, $languageCode)
    {
        $this->logger->logCall(
            __METHOD__,
            [
                'contentId' => $contentId,
                'languageCode' => $languageCode,
            ]
        );

        $this->persistenceHandler->contentHandler()->removeTranslationFromContent($contentId, $languageCode);

        $this->cache->clear('content', $contentId);
        $this->cache->clear('content', 'info', $contentId);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteTranslationFromDraft($contentId, $versionNo, $languageCode)
    {
        $this->logger->logCall(
            __METHOD__,
            ['content' => $contentId, 'version' => $versionNo, 'languageCode' => $languageCode]
        );
        $content = $this->persistenceHandler->contentHandler()->deleteTranslationFromDraft(
            $contentId,
            $versionNo,
            $languageCode
        );
        $this->cache->clear('content', $contentId, $versionNo);

        return $content;
    }
}
