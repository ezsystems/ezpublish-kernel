<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\Decorator;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\User\User;

abstract class ContentServiceDecorator implements ContentService
{
    /**
     * @var \eZ\Publish\API\Repository\ContentService
     */
    protected $service;

    /**
     * @param \eZ\Publish\API\Repository\ContentService $service
     */
    public function __construct(ContentService $service)
    {
        $this->service = $service;
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentInfo($contentId)
    {
        return $this->service->loadContentInfo($contentId);
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentInfoByRemoteId($remoteId)
    {
        return $this->service->loadContentInfoByRemoteId($remoteId);
    }

    /**
     * {@inheritdoc}
     */
    public function loadVersionInfo(ContentInfo $contentInfo, $versionNo = null)
    {
        return $this->service->loadVersionInfo($contentInfo, $versionNo);
    }

    /**
     * {@inheritdoc}
     */
    public function loadVersionInfoById($contentId, $versionNo = null)
    {
        return $this->service->loadVersionInfoById($contentId, $versionNo);
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentByContentInfo(ContentInfo $contentInfo, array $languages = null, $versionNo = null, $useAlwaysAvailable = true)
    {
        return $this->service->loadContentByContentInfo($contentInfo, $languages, $versionNo, $useAlwaysAvailable);
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentByVersionInfo(VersionInfo $versionInfo, array $languages = null, $useAlwaysAvailable = true)
    {
        return $this->service->loadContentByVersionInfo($versionInfo, $languages, $useAlwaysAvailable);
    }

    /**
     * {@inheritdoc}
     */
    public function loadContent($contentId, array $languages = null, $versionNo = null, $useAlwaysAvailable = true)
    {
        return $this->service->loadContent($contentId, $languages, $versionNo, $useAlwaysAvailable);
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentByRemoteId($remoteId, array $languages = null, $versionNo = null, $useAlwaysAvailable = true)
    {
        return $this->service->loadContentByRemoteId($remoteId, $languages, $versionNo, $useAlwaysAvailable);
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentListByContentInfo(array $contentInfoList, array $languages = [], $useAlwaysAvailable = true)
    {
        return $this->service->loadContentListByContentInfo($contentInfoList, $languages, $useAlwaysAvailable);
    }

    /**
     * {@inheritdoc}
     */
    public function createContent(ContentCreateStruct $contentCreateStruct, array $locationCreateStructs = [])
    {
        return $this->service->createContent($contentCreateStruct, $locationCreateStructs);
    }

    /**
     * {@inheritdoc}
     */
    public function updateContentMetadata(ContentInfo $contentInfo, ContentMetadataUpdateStruct $contentMetadataUpdateStruct)
    {
        return $this->service->updateContentMetadata($contentInfo, $contentMetadataUpdateStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteContent(ContentInfo $contentInfo)
    {
        return $this->service->deleteContent($contentInfo);
    }

    /**
     * {@inheritdoc}
     */
    public function createContentDraft(ContentInfo $contentInfo, VersionInfo $versionInfo = null, User $creator = null)
    {
        return $this->service->createContentDraft($contentInfo, $versionInfo, $creator);
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentDrafts(User $user = null)
    {
        return $this->service->loadContentDrafts($user);
    }

    /**
     * {@inheritdoc}
     */
    public function updateContent(VersionInfo $versionInfo, ContentUpdateStruct $contentUpdateStruct)
    {
        return $this->service->updateContent($versionInfo, $contentUpdateStruct);
    }

    /**
     * {@inheritdoc}
     */
    public function publishVersion(VersionInfo $versionInfo)
    {
        return $this->service->publishVersion($versionInfo);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteVersion(VersionInfo $versionInfo)
    {
        return $this->service->deleteVersion($versionInfo);
    }

    /**
     * {@inheritdoc}
     */
    public function loadVersions(ContentInfo $contentInfo)
    {
        return $this->service->loadVersions($contentInfo);
    }

    /**
     * {@inheritdoc}
     */
    public function copyContent(
        ContentInfo $contentInfo,
        LocationCreateStruct $destinationLocationCreateStruct,
        VersionInfo $versionInfo = null
    ) {
        return $this->service->copyContent($contentInfo, $destinationLocationCreateStruct, $versionInfo);
    }

    /**
     * {@inheritdoc}
     */
    public function loadRelations(VersionInfo $versionInfo)
    {
        return $this->service->loadRelations($versionInfo);
    }

    /**
     * {@inheritdoc}
     */
    public function loadReverseRelations(ContentInfo $contentInfo)
    {
        return $this->service->loadReverseRelations($contentInfo);
    }

    /**
     * {@inheritdoc}
     */
    public function addRelation(VersionInfo $sourceVersion, ContentInfo $destinationContent)
    {
        return $this->service->addRelation($sourceVersion, $destinationContent);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteRelation(VersionInfo $sourceVersion, ContentInfo $destinationContent)
    {
        return $this->service->deleteRelation($sourceVersion, $destinationContent);
    }

    /**
     * {@inheritdoc}
     */
    public function removeTranslation(ContentInfo $contentInfo, $languageCode)
    {
        return $this->service->removeTranslation($contentInfo, $languageCode);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteTranslation(ContentInfo $contentInfo, $languageCode)
    {
        return $this->service->deleteTranslation($contentInfo, $languageCode);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteTranslationFromDraft(VersionInfo $versionInfo, $languageCode)
    {
        return $this->service->deleteTranslationFromDraft($versionInfo, $languageCode);
    }

    /**
     * {@inheritdoc}
     */
    public function newContentCreateStruct(ContentType $contentType, $mainLanguageCode)
    {
        return $this->service->newContentCreateStruct($contentType, $mainLanguageCode);
    }

    /**
     * {@inheritdoc}
     */
    public function newContentMetadataUpdateStruct()
    {
        return $this->service->newContentMetadataUpdateStruct();
    }

    /**
     * {@inheritdoc}
     */
    public function newContentUpdateStruct()
    {
        return $this->service->newContentUpdateStruct();
    }
}
