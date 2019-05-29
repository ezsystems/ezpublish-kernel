<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\SPI\Repository\Decorator;

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
    protected $innerService;

    public function __construct(ContentService $innerService)
    {
        $this->innerService = $innerService;
    }

    public function loadContentInfo($contentId)
    {
        $this->innerService->loadContentInfo($contentId);
    }

    public function loadContentInfoList(array $contentIds): iterable
    {
        return $this->innerService->loadContentInfoList($contentIds);
    }

    public function loadContentInfoByRemoteId($remoteId)
    {
        $this->innerService->loadContentInfoByRemoteId($remoteId);
    }

    public function loadVersionInfo(
        ContentInfo $contentInfo,
        $versionNo = null
    ) {
        $this->innerService->loadVersionInfo($contentInfo, $versionNo);
    }

    public function loadVersionInfoById(
        $contentId,
        $versionNo = null
    ) {
        $this->innerService->loadVersionInfoById($contentId, $versionNo);
    }

    public function loadContentByContentInfo(
        ContentInfo $contentInfo,
        array $languages = null,
        $versionNo = null,
        $useAlwaysAvailable = true
    ) {
        $this->innerService->loadContentByContentInfo($contentInfo, $languages, $versionNo, $useAlwaysAvailable);
    }

    public function loadContentByVersionInfo(
        VersionInfo $versionInfo,
        array $languages = null,
        $useAlwaysAvailable = true
    ) {
        $this->innerService->loadContentByVersionInfo($versionInfo, $languages, $useAlwaysAvailable);
    }

    public function loadContent(
        $contentId,
        array $languages = null,
        $versionNo = null,
        $useAlwaysAvailable = true
    ) {
        $this->innerService->loadContent($contentId, $languages, $versionNo, $useAlwaysAvailable);
    }

    public function loadContentByRemoteId(
        $remoteId,
        array $languages = null,
        $versionNo = null,
        $useAlwaysAvailable = true
    ) {
        $this->innerService->loadContentByRemoteId($remoteId, $languages, $versionNo, $useAlwaysAvailable);
    }

    public function loadContentListByContentInfo(
        array $contentInfoList,
        array $languages = [],
        $useAlwaysAvailable = true
    ) {
        $this->innerService->loadContentListByContentInfo($contentInfoList, $languages, $useAlwaysAvailable);
    }

    public function createContent(
        ContentCreateStruct $contentCreateStruct,
        array $locationCreateStructs = []
    ) {
        $this->innerService->createContent($contentCreateStruct, $locationCreateStructs);
    }

    public function updateContentMetadata(
        ContentInfo $contentInfo,
        ContentMetadataUpdateStruct $contentMetadataUpdateStruct
    ) {
        $this->innerService->updateContentMetadata($contentInfo, $contentMetadataUpdateStruct);
    }

    public function deleteContent(ContentInfo $contentInfo)
    {
        $this->innerService->deleteContent($contentInfo);
    }

    public function createContentDraft(
        ContentInfo $contentInfo,
        VersionInfo $versionInfo = null,
        User $creator = null
    ) {
        $this->innerService->createContentDraft($contentInfo, $versionInfo, $creator);
    }

    public function loadContentDrafts(User $user = null)
    {
        $this->innerService->loadContentDrafts($user);
    }

    public function updateContent(
        VersionInfo $versionInfo,
        ContentUpdateStruct $contentUpdateStruct
    ) {
        $this->innerService->updateContent($versionInfo, $contentUpdateStruct);
    }

    public function publishVersion(VersionInfo $versionInfo)
    {
        $this->innerService->publishVersion($versionInfo);
    }

    public function deleteVersion(VersionInfo $versionInfo)
    {
        $this->innerService->deleteVersion($versionInfo);
    }

    public function loadVersions(ContentInfo $contentInfo)
    {
        $this->innerService->loadVersions($contentInfo);
    }

    public function copyContent(
        ContentInfo $contentInfo,
        LocationCreateStruct $destinationLocationCreateStruct,
        VersionInfo $versionInfo = null
    ) {
        $this->innerService->copyContent($contentInfo, $destinationLocationCreateStruct, $versionInfo);
    }

    public function loadRelations(VersionInfo $versionInfo)
    {
        $this->innerService->loadRelations($versionInfo);
    }

    public function loadReverseRelations(ContentInfo $contentInfo)
    {
        $this->innerService->loadReverseRelations($contentInfo);
    }

    public function addRelation(
        VersionInfo $sourceVersion,
        ContentInfo $destinationContent
    ) {
        $this->innerService->addRelation($sourceVersion, $destinationContent);
    }

    public function deleteRelation(
        VersionInfo $sourceVersion,
        ContentInfo $destinationContent
    ) {
        $this->innerService->deleteRelation($sourceVersion, $destinationContent);
    }

    public function removeTranslation(
        ContentInfo $contentInfo,
        $languageCode
    ) {
        $this->innerService->removeTranslation($contentInfo, $languageCode);
    }

    public function deleteTranslation(
        ContentInfo $contentInfo,
        $languageCode
    ) {
        $this->innerService->deleteTranslation($contentInfo, $languageCode);
    }

    public function deleteTranslationFromDraft(
        VersionInfo $versionInfo,
        $languageCode
    ) {
        $this->innerService->deleteTranslationFromDraft($versionInfo, $languageCode);
    }

    public function hideContent(ContentInfo $contentInfo): void
    {
        $this->innerService->hideContent($contentInfo);
    }

    public function revealContent(ContentInfo $contentInfo): void
    {
        $this->innerService->revealContent($contentInfo);
    }

    public function newContentCreateStruct(
        ContentType $contentType,
        $mainLanguageCode
    ) {
        $this->innerService->newContentCreateStruct($contentType, $mainLanguageCode);
    }

    public function newContentMetadataUpdateStruct()
    {
        $this->innerService->newContentMetadataUpdateStruct();
    }

    public function newContentUpdateStruct()
    {
        $this->innerService->newContentUpdateStruct();
    }
}
