<?php

/**
 * ContentService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\SiteAccessAware;

use eZ\Publish\API\Repository\ContentService as ContentServiceInterface;
use eZ\Publish\API\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\LanguageResolver;

/**
 * SiteAccess aware implementation of ContentService injecting languages where needed.
 */
class ContentService implements ContentServiceInterface
{
    /** @var \eZ\Publish\API\Repository\ContentService */
    protected $service;

    /** @var \eZ\Publish\API\Repository\LanguageResolver */
    protected $languageResolver;

    /**
     * Construct service object from aggregated service and LanguageResolver.
     *
     * @param \eZ\Publish\API\Repository\ContentService $service
     * @param \eZ\Publish\API\Repository\LanguageResolver $languageResolver
     */
    public function __construct(
        ContentServiceInterface $service,
        LanguageResolver $languageResolver
    ) {
        $this->service = $service;
        $this->languageResolver = $languageResolver;
    }

    public function loadContentInfo($contentId)
    {
        return $this->service->loadContentInfo($contentId);
    }

    /**
     * {@inheritdoc}
     */
    public function loadContentInfoList(array $contentIds): iterable
    {
        return $this->service->loadContentInfoList($contentIds);
    }

    public function loadContentInfoByRemoteId($remoteId)
    {
        return $this->service->loadContentInfoByRemoteId($remoteId);
    }

    public function loadVersionInfo(ContentInfo $contentInfo, $versionNo = null)
    {
        return $this->service->loadVersionInfo($contentInfo, $versionNo);
    }

    public function loadVersionInfoById($contentId, $versionNo = null)
    {
        return $this->service->loadVersionInfoById($contentId, $versionNo);
    }

    public function loadContentByContentInfo(ContentInfo $contentInfo, array $languages = null, $versionNo = null, $useAlwaysAvailable = null)
    {
        return $this->service->loadContentByContentInfo(
            $contentInfo,
            $this->languageResolver->getPrioritizedLanguages($languages),
            $versionNo,
            $this->languageResolver->getUseAlwaysAvailable($useAlwaysAvailable)
        );
    }

    public function loadContentByVersionInfo(VersionInfo $versionInfo, array $languages = null, $useAlwaysAvailable = null)
    {
        return $this->service->loadContentByVersionInfo(
            $versionInfo,
            $this->languageResolver->getPrioritizedLanguages($languages),
            $this->languageResolver->getUseAlwaysAvailable($useAlwaysAvailable)
        );
    }

    public function loadContent($contentId, array $languages = null, $versionNo = null, $useAlwaysAvailable = null)
    {
        return $this->service->loadContent(
            $contentId,
            $this->languageResolver->getPrioritizedLanguages($languages),
            $versionNo,
            $this->languageResolver->getUseAlwaysAvailable($useAlwaysAvailable)
        );
    }

    public function loadContentByRemoteId($remoteId, array $languages = null, $versionNo = null, $useAlwaysAvailable = null)
    {
        return $this->service->loadContentByRemoteId(
            $remoteId,
            $this->languageResolver->getPrioritizedLanguages($languages),
            $versionNo,
            $this->languageResolver->getUseAlwaysAvailable($useAlwaysAvailable)
        );
    }

    public function createContent(ContentCreateStruct $contentCreateStruct, array $locationCreateStructs = [])
    {
        return $this->service->createContent($contentCreateStruct, $locationCreateStructs);
    }

    public function updateContentMetadata(ContentInfo $contentInfo, ContentMetadataUpdateStruct $contentMetadataUpdateStruct)
    {
        return $this->service->updateContentMetadata($contentInfo, $contentMetadataUpdateStruct);
    }

    public function deleteContent(ContentInfo $contentInfo)
    {
        return $this->service->deleteContent($contentInfo);
    }

    public function createContentDraft(ContentInfo $contentInfo, VersionInfo $versionInfo = null, User $user = null)
    {
        return $this->service->createContentDraft($contentInfo, $versionInfo, $user);
    }

    public function loadContentDrafts(User $user = null)
    {
        return $this->service->loadContentDrafts($user);
    }

    public function updateContent(VersionInfo $versionInfo, ContentUpdateStruct $contentUpdateStruct)
    {
        return $this->service->updateContent($versionInfo, $contentUpdateStruct);
    }

    public function publishVersion(VersionInfo $versionInfo, array $translations = Language::ALL)
    {
        return $this->service->publishVersion($versionInfo, $translations);
    }

    public function deleteVersion(VersionInfo $versionInfo)
    {
        return $this->service->deleteVersion($versionInfo);
    }

    public function loadVersions(ContentInfo $contentInfo, ?int $status = null)
    {
        return $this->service->loadVersions($contentInfo, $status);
    }

    public function copyContent(ContentInfo $contentInfo, LocationCreateStruct $destinationLocationCreateStruct, VersionInfo $versionInfo = null)
    {
        return $this->service->copyContent($contentInfo, $destinationLocationCreateStruct, $versionInfo);
    }

    public function loadRelations(VersionInfo $versionInfo)
    {
        return $this->service->loadRelations($versionInfo);
    }

    public function loadReverseRelations(ContentInfo $contentInfo)
    {
        return $this->service->loadReverseRelations($contentInfo);
    }

    public function addRelation(VersionInfo $sourceVersion, ContentInfo $destinationContent)
    {
        return $this->service->addRelation($sourceVersion, $destinationContent);
    }

    public function deleteRelation(VersionInfo $sourceVersion, ContentInfo $destinationContent)
    {
        return $this->service->deleteRelation($sourceVersion, $destinationContent);
    }

    public function removeTranslation(ContentInfo $contentInfo, $languageCode)
    {
        return $this->service->removeTranslation($contentInfo, $languageCode);
    }

    public function deleteTranslation(ContentInfo $contentInfo, $languageCode)
    {
        return $this->service->deleteTranslation($contentInfo, $languageCode);
    }

    public function deleteTranslationFromDraft(VersionInfo $versionInfo, $languageCode)
    {
        return $this->service->deleteTranslationFromDraft($versionInfo, $languageCode);
    }

    public function loadContentListByContentInfo(array $contentInfoList, array $languages = [], $useAlwaysAvailable = true)
    {
        return $this->service->loadContentListByContentInfo($contentInfoList, $languages, $useAlwaysAvailable);
    }

    public function hideContent(ContentInfo $contentInfo): void
    {
        $this->service->hideContent($contentInfo);
    }

    public function revealContent(ContentInfo $contentInfo): void
    {
        $this->service->revealContent($contentInfo);
    }

    public function newContentCreateStruct(ContentType $contentType, $mainLanguageCode)
    {
        return $this->service->newContentCreateStruct($contentType, $mainLanguageCode);
    }

    public function newContentMetadataUpdateStruct()
    {
        return $this->service->newContentMetadataUpdateStruct();
    }

    public function newContentUpdateStruct()
    {
        return $this->service->newContentUpdateStruct();
    }
}
