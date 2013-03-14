<?php

/**
 * File containing the ContentHandler implementation.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
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
 * @see eZ\Publish\SPI\Persistence\Content\Handler
 */
class ContentHandler extends AbstractHandler implements ContentHandlerInterface
{
    const ALL_TRANSLATIONS_KEY = '0';

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::create
     */
    public function create(CreateStruct $struct)
    {
        // Cached on demand when published or loaded
        $this->logger->logCall(__METHOD__, array('struct' => $struct));

        return $this->persistenceHandler->contentHandler()->create($struct);
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::createDraftFromVersion
     */
    public function createDraftFromVersion($contentId, $srcVersion, $userId)
    {
        $this->logger->logCall(__METHOD__, array('content' => $contentId, 'version' => $srcVersion, 'user' => $userId));

        return $this->persistenceHandler->contentHandler()->createDraftFromVersion($contentId, $srcVersion, $userId);
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::copy
     */
    public function copy($contentId, $versionNo = null)
    {
        $this->logger->logCall(__METHOD__, array('content' => $contentId, 'version' => $versionNo));

        return $this->persistenceHandler->contentHandler()->copy($contentId, $versionNo);
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::load
     */
    public function load($contentId, $version, array $translations = null)
    {
        $translationsKey = empty($translations) ? self::ALL_TRANSLATIONS_KEY : implode('|', $translations);
        $cache = $this->cache->getItem('content', $contentId, $version, $translationsKey);
        $content = $cache->get();
        if ($cache->isMiss()) {
            $this->logger->logCall(__METHOD__, array('content' => $contentId, 'version' => $version, 'translations' => $translations));
            $content = $this->persistenceHandler->contentHandler()->load($contentId, $version, $translations);
            $cache->set($content);
        }

        return $content;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::loadContentInfo
     */
    public function loadContentInfo($contentId)
    {
        $cache = $this->cache->getItem('content', 'info', $contentId);
        $contentInfo = $cache->get();
        if ($cache->isMiss()) {
            $this->logger->logCall(__METHOD__, array('content' => $contentId));
            $cache->set($contentInfo = $this->persistenceHandler->contentHandler()->loadContentInfo($contentId));
        }

        return $contentInfo;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::loadContentInfoByRemoteId
     */
    public function loadContentInfoByRemoteId($remoteId)
    {
        $cache = $this->cache->getItem('content', 'info', 'remoteId', $remoteId);
        $contentInfo = $cache->get();
        if ($cache->isMiss()) {
            $this->logger->logCall(__METHOD__, array('content' => $remoteId));
            $cache->set($contentInfo = $this->persistenceHandler->contentHandler()->loadContentInfoByRemoteId($remoteId));
        }

        return $contentInfo;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::loadVersionInfo
     */
    public function loadVersionInfo($contentId, $versionNo)
    {
        $this->logger->logCall(__METHOD__, array('content' => $contentId, 'version' => $versionNo));

        return $this->persistenceHandler->contentHandler()->loadVersionInfo($contentId, $versionNo);
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::loadDraftsForUser
     */
    public function loadDraftsForUser($userId)
    {
        $this->logger->logCall(__METHOD__, array('user' => $userId));

        return $this->persistenceHandler->contentHandler()->loadDraftsForUser($userId);
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::setStatus
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
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::updateMetadata
     */
    public function updateMetadata($contentId, MetadataUpdateStruct $struct)
    {
        $this->logger->logCall(__METHOD__, array('content' => $contentId, 'struct' => $struct));

        $this->cache
            ->getItem('content', 'info', $contentId)
            ->set($contentInfo = $this->persistenceHandler->contentHandler()->updateMetadata($contentId, $struct));

        $this->cache->clear('content', $contentId, $contentInfo->currentVersionNo);

        return $contentInfo;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::updateContent
     */
    public function updateContent($contentId, $versionNo, UpdateStruct $struct)
    {
        $this->logger->logCall(__METHOD__, array('content' => $contentId, 'version' => $versionNo, 'struct' => $struct));
        $content = $this->persistenceHandler->contentHandler()->updateContent($contentId, $versionNo, $struct);
        $this->cache->clear('content', $contentId, $versionNo);
        $this->cache
            ->getItem('content', $contentId, $versionNo, self::ALL_TRANSLATIONS_KEY)
            ->set($content);

        return $content;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::deleteContent
     */
    public function deleteContent($contentId)
    {
        $this->logger->logCall(__METHOD__, array('content' => $contentId));

        // Load reverse field relations first
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

        return $return;
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::deleteVersion
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
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::listVersions
     */
    public function listVersions($contentId)
    {
        $this->logger->logCall(__METHOD__, array('content' => $contentId));

        return $this->persistenceHandler->contentHandler()->listVersions($contentId);
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::addRelation
     */
    public function addRelation(RelationCreateStruct $relation)
    {
        $this->logger->logCall(__METHOD__, array('struct' => $relation));

        return $this->persistenceHandler->contentHandler()->addRelation($relation);
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::removeRelation
     */
    public function removeRelation($relationId, $type)
    {
        $this->logger->logCall(__METHOD__, array('relation' => $relationId, 'type' => $type));
        $this->persistenceHandler->contentHandler()->removeRelation($relationId, $type);
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::loadRelations
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
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::loadReverseRelations
     */
    public function loadReverseRelations($destinationContentId, $type = null)
    {
        $this->logger->logCall(__METHOD__, array('content' => $destinationContentId, 'type' => $type));

        return $this->persistenceHandler->contentHandler()->loadReverseRelations($destinationContentId, $type);
    }

    /**
     * @see \eZ\Publish\SPI\Persistence\Content\Handler::publish
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
            ->set($content);
        $this->cache->getItem('content', 'info', $contentInfo->id)->set($contentInfo);

        return $content;
    }
}
