<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\SiteAccessAware;

use eZ\Publish\API\Repository\ContentService as ContentServiceInterface;
use eZ\Publish\API\Repository\Values\Content\ContentDraftList;
use eZ\Publish\API\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Relation;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\RelationList;
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

    public function loadContentInfo(int $contentId): ContentInfo
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

    public function loadContentInfoByRemoteId(string $remoteId): ContentInfo
    {
        return $this->service->loadContentInfoByRemoteId($remoteId);
    }

    public function loadVersionInfo(ContentInfo $contentInfo, ?int $versionNo = null): VersionInfo
    {
        return $this->service->loadVersionInfo($contentInfo, $versionNo);
    }

    public function loadVersionInfoById(int $contentId, ?int $versionNo = null): VersionInfo
    {
        return $this->service->loadVersionInfoById($contentId, $versionNo);
    }

    public function loadContentByContentInfo(ContentInfo $contentInfo, array $languages = null, ?int $versionNo = null, bool $useAlwaysAvailable = true): Content
    {
        return $this->service->loadContentByContentInfo(
            $contentInfo,
            $this->languageResolver->getPrioritizedLanguages($languages),
            $versionNo,
            $this->languageResolver->getUseAlwaysAvailable($useAlwaysAvailable)
        );
    }

    public function loadContentByVersionInfo(VersionInfo $versionInfo, array $languages = null, bool $useAlwaysAvailable = true): Content
    {
        return $this->service->loadContentByVersionInfo(
            $versionInfo,
            $this->languageResolver->getPrioritizedLanguages($languages),
            $this->languageResolver->getUseAlwaysAvailable($useAlwaysAvailable)
        );
    }

    public function loadContent(int $contentId, array $languages = null, ?int $versionNo = null, bool $useAlwaysAvailable = true): Content
    {
        return $this->service->loadContent(
            $contentId,
            $this->languageResolver->getPrioritizedLanguages($languages),
            $versionNo,
            $this->languageResolver->getUseAlwaysAvailable($useAlwaysAvailable)
        );
    }

    public function loadContentByRemoteId(string $remoteId, array $languages = null, ?int $versionNo = null, bool $useAlwaysAvailable = true): Content
    {
        return $this->service->loadContentByRemoteId(
            $remoteId,
            $this->languageResolver->getPrioritizedLanguages($languages),
            $versionNo,
            $this->languageResolver->getUseAlwaysAvailable($useAlwaysAvailable)
        );
    }

    public function createContent(ContentCreateStruct $contentCreateStruct, array $locationCreateStructs = []): Content
    {
        return $this->service->createContent($contentCreateStruct, $locationCreateStructs);
    }

    public function updateContentMetadata(ContentInfo $contentInfo, ContentMetadataUpdateStruct $contentMetadataUpdateStruct): Content
    {
        return $this->service->updateContentMetadata($contentInfo, $contentMetadataUpdateStruct);
    }

    public function deleteContent(ContentInfo $contentInfo): iterable
    {
        return $this->service->deleteContent($contentInfo);
    }

    public function createContentDraft(
        ContentInfo $contentInfo,
        ?VersionInfo $versionInfo = null,
        ?User $creator = null,
        ?Language $language = null
    ): Content {
        return $this->service->createContentDraft($contentInfo, $versionInfo, $creator, $language);
    }

    public function countContentDrafts(?User $user = null): int
    {
        return $this->service->countContentDrafts($user);
    }

    public function loadContentDrafts(?User $user = null): iterable
    {
        return $this->service->loadContentDrafts($user);
    }

    public function loadContentDraftList(?User $user = null, int $offset = 0, int $limit = -1): ContentDraftList
    {
        return $this->service->loadContentDraftList($user, $offset, $limit);
    }

    public function updateContent(VersionInfo $versionInfo, ContentUpdateStruct $contentUpdateStruct): Content
    {
        return $this->service->updateContent($versionInfo, $contentUpdateStruct);
    }

    public function publishVersion(VersionInfo $versionInfo, array $translations = Language::ALL): Content
    {
        return $this->service->publishVersion($versionInfo, $translations);
    }

    public function deleteVersion(VersionInfo $versionInfo): void
    {
        $this->service->deleteVersion($versionInfo);
    }

    public function loadVersions(ContentInfo $contentInfo, ?int $status = null): iterable
    {
        return $this->service->loadVersions($contentInfo, $status);
    }

    public function copyContent(ContentInfo $contentInfo, LocationCreateStruct $destinationLocationCreateStruct, ?VersionInfo $versionInfo = null): Content
    {
        return $this->service->copyContent($contentInfo, $destinationLocationCreateStruct, $versionInfo);
    }

    public function loadRelations(VersionInfo $versionInfo): iterable
    {
        return $this->service->loadRelations($versionInfo);
    }

    /**
     * {@inheritdoc}
     */
    public function countReverseRelations(ContentInfo $contentInfo): int
    {
        return $this->service->countReverseRelations($contentInfo);
    }

    public function loadReverseRelations(ContentInfo $contentInfo): iterable
    {
        return $this->service->loadReverseRelations($contentInfo);
    }

    public function loadReverseRelationList(ContentInfo $contentInfo, int $offset = 0, int $limit = -1): RelationList
    {
        return $this->service->loadReverseRelationList($contentInfo, $offset, $limit);
    }

    public function addRelation(VersionInfo $sourceVersion, ContentInfo $destinationContent): Relation
    {
        return $this->service->addRelation($sourceVersion, $destinationContent);
    }

    public function deleteRelation(VersionInfo $sourceVersion, ContentInfo $destinationContent): void
    {
        $this->service->deleteRelation($sourceVersion, $destinationContent);
    }

    public function removeTranslation(ContentInfo $contentInfo, string $languageCode): void
    {
        $this->service->removeTranslation($contentInfo, $languageCode);
    }

    public function deleteTranslation(ContentInfo $contentInfo, string $languageCode): void
    {
        $this->service->deleteTranslation($contentInfo, $languageCode);
    }

    public function deleteTranslationFromDraft(VersionInfo $versionInfo, string $languageCode): Content
    {
        return $this->service->deleteTranslationFromDraft($versionInfo, $languageCode);
    }

    public function loadContentListByContentInfo(array $contentInfoList, array $languages = null, bool $useAlwaysAvailable = true): iterable
    {
        return $this->service->loadContentListByContentInfo(
            $contentInfoList,
            $this->languageResolver->getPrioritizedLanguages($languages),
            $this->languageResolver->getUseAlwaysAvailable($useAlwaysAvailable)
        );
    }

    public function hideContent(ContentInfo $contentInfo): void
    {
        $this->service->hideContent($contentInfo);
    }

    public function revealContent(ContentInfo $contentInfo): void
    {
        $this->service->revealContent($contentInfo);
    }

    public function newContentCreateStruct(ContentType $contentType, string $mainLanguageCode): ContentCreateStruct
    {
        return $this->service->newContentCreateStruct($contentType, $mainLanguageCode);
    }

    public function newContentMetadataUpdateStruct(): ContentMetadataUpdateStruct
    {
        return $this->service->newContentMetadataUpdateStruct();
    }

    public function newContentUpdateStruct(): ContentUpdateStruct
    {
        return $this->service->newContentUpdateStruct();
    }
}
