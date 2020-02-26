<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Mapper;

use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Content as APIContent;
use eZ\Publish\Core\FieldType\FieldTypeRegistry;
use eZ\Publish\Core\Repository\ProxyFactory\ProxyDomainMapperInterface;
use eZ\Publish\SPI\Persistence\Content\Handler as ContentHandler;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as LocationHandler;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as TypeHandler;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\VersionInfo as APIVersionInfo;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\Repository\Values\Content\Relation;
use eZ\Publish\API\Repository\Values\Content\Location as APILocation;
use eZ\Publish\Core\Repository\Values\Content\Location;
use eZ\Publish\SPI\Persistence\Content as SPIContent;
use eZ\Publish\SPI\Persistence\Content\Location as SPILocation;
use eZ\Publish\SPI\Persistence\Content\VersionInfo as SPIVersionInfo;
use eZ\Publish\SPI\Persistence\Content\ContentInfo as SPIContentInfo;
use eZ\Publish\SPI\Persistence\Content\Relation as SPIRelation;
use eZ\Publish\SPI\Persistence\Content\Type as SPIContentType;
use eZ\Publish\SPI\Persistence\Content\Location\CreateStruct as SPILocationCreateStruct;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use DateTime;
use eZ\Publish\SPI\Repository\Strategy\ContentThumbnail\ThumbnailStrategy;

/**
 * ContentDomainMapper is an internal service.
 *
 * @internal Meant for internal use by Repository.
 */
class ContentDomainMapper
{
    const MAX_LOCATION_PRIORITY = 2147483647;
    const MIN_LOCATION_PRIORITY = -2147483648;

    /** @var \eZ\Publish\SPI\Persistence\Content\Handler */
    protected $contentHandler;

    /** @var \eZ\Publish\SPI\Persistence\Content\Location\Handler */
    protected $locationHandler;

    /** @var \eZ\Publish\SPI\Persistence\Content\Type\Handler */
    protected $contentTypeHandler;

    /** @var \eZ\Publish\Core\Repository\Mapper\ContentTypeDomainMapper */
    protected $contentTypeDomainMapper;

    /** @var \eZ\Publish\SPI\Persistence\Content\Language\Handler */
    protected $contentLanguageHandler;

    /** @var \eZ\Publish\Core\Repository\Helper\FieldTypeRegistry */
    protected $fieldTypeRegistry;

    /** @var \eZ\Publish\SPI\Repository\Strategy\ContentThumbnail\ThumbnailStrategy */
    private $thumbnailStrategy;

    /** @var \eZ\Publish\Core\Repository\ProxyFactory\ProxyDomainMapperInterface */
    private $proxyFactory;

    /**
     * Setups service with reference to repository.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Handler $contentHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Location\Handler $locationHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $contentTypeHandler
     * @param \eZ\Publish\Core\Repository\Mapper\ContentTypeDomainMapper $contentTypeDomainMapper
     * @param \eZ\Publish\SPI\Persistence\Content\Language\Handler $contentLanguageHandler
     * @param \eZ\Publish\Core\FieldType\FieldTypeRegistry $fieldTypeRegistry
     * @param \eZ\Publish\SPI\Repository\Strategy\ContentThumbnail\ThumbnailStrategy $thumbnailStrategy
     * @param \eZ\Publish\Core\Repository\ProxyFactory\ProxyDomainMapperInterface $proxyFactory
     */
    public function __construct(
        ContentHandler $contentHandler,
        LocationHandler $locationHandler,
        TypeHandler $contentTypeHandler,
        ContentTypeDomainMapper $contentTypeDomainMapper,
        LanguageHandler $contentLanguageHandler,
        FieldTypeRegistry $fieldTypeRegistry,
        ThumbnailStrategy $thumbnailStrategy,
        ProxyDomainMapperInterface $proxyFactory
    ) {
        $this->contentHandler = $contentHandler;
        $this->locationHandler = $locationHandler;
        $this->contentTypeHandler = $contentTypeHandler;
        $this->contentTypeDomainMapper = $contentTypeDomainMapper;
        $this->contentLanguageHandler = $contentLanguageHandler;
        $this->fieldTypeRegistry = $fieldTypeRegistry;
        $this->thumbnailStrategy = $thumbnailStrategy;
        $this->proxyFactory = $proxyFactory;
    }

    /**
     * Builds a Content domain object from value object.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $spiContent
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param array $prioritizedLanguages Prioritized language codes to filter fields on
     * @param string|null $fieldAlwaysAvailableLanguage Language code fallback if a given field is not found in $prioritizedLanguages
     *
     * @return \eZ\Publish\Core\Repository\Values\Content\Content
     */
    public function buildContentDomainObject(
        SPIContent $spiContent,
        ContentType $contentType,
        array $prioritizedLanguages = [],
        string $fieldAlwaysAvailableLanguage = null
    ) {
        $prioritizedFieldLanguageCode = null;
        if (!empty($prioritizedLanguages)) {
            $availableFieldLanguageMap = array_fill_keys($spiContent->versionInfo->languageCodes, true);
            foreach ($prioritizedLanguages as $prioritizedLanguage) {
                if (isset($availableFieldLanguageMap[$prioritizedLanguage])) {
                    $prioritizedFieldLanguageCode = $prioritizedLanguage;
                    break;
                }
            }
        }

        $internalFields = $this->buildDomainFields($spiContent->fields, $contentType, $prioritizedLanguages, $fieldAlwaysAvailableLanguage);

        return new Content(
            [
                'thumbnail' => $this->thumbnailStrategy->getThumbnail($contentType, $internalFields),
                'internalFields' => $internalFields,
                'versionInfo' => $this->buildVersionInfoDomainObject($spiContent->versionInfo, $prioritizedLanguages),
                'contentType' => $contentType,
                'prioritizedFieldLanguageCode' => $prioritizedFieldLanguageCode,
            ]
        );
    }

    /**
     * Builds a Content domain object from value object returned from persistence.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $spiContent
     * @param \eZ\Publish\SPI\Persistence\Content\Type $spiContentType
     * @param string[] $prioritizedLanguages Prioritized language codes to filter fields on
     * @param string|null $fieldAlwaysAvailableLanguage Language code fallback if a given field is not found in $prioritizedLanguages
     *
     * @return \eZ\Publish\Core\Repository\Values\Content\Content
     */
    public function buildContentDomainObjectFromPersistence(
        SPIContent $spiContent,
        SPIContentType $spiContentType,
        array $prioritizedLanguages = [],
        ?string $fieldAlwaysAvailableLanguage = null
    ): APIContent {
        $contentType = $this->contentTypeDomainMapper->buildContentTypeDomainObject($spiContentType, $prioritizedLanguages);

        return $this->buildContentDomainObject($spiContent, $contentType, $prioritizedLanguages, $fieldAlwaysAvailableLanguage);
    }

    /**
     * Builds a Content proxy object (lazy loaded, loads as soon as used).
     */
    public function buildContentProxy(
        SPIContent\ContentInfo $info,
        array $prioritizedLanguages = [],
        bool $useAlwaysAvailable = true
    ): APIContent {
        return $this->proxyFactory->createContentProxy(
            $info->id,
            $prioritizedLanguages,
            $useAlwaysAvailable
        );
    }

    /**
     * Builds a list of Content proxy objects (lazy loaded, loads all as soon as one of them loads).
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ContentInfo[] $infoList
     * @param string[] $prioritizedLanguages
     * @param bool $useAlwaysAvailable
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content[<int>]
     */
    public function buildContentProxyList(
        array $infoList,
        array $prioritizedLanguages = [],
        bool $useAlwaysAvailable = true
    ): array {
        $list = [];
        foreach ($infoList as $info) {
            $list[$info->id] = $this->proxyFactory->createContentProxy(
                $info->id,
                $prioritizedLanguages,
                $useAlwaysAvailable
            );
        }

        return $list;
    }

    /**
     * Returns an array of domain fields created from given array of SPI fields.
     *
     * @throws InvalidArgumentType On invalid $contentType
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field[] $spiFields
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType|\eZ\Publish\SPI\Persistence\Content\Type $contentType
     * @param array $prioritizedLanguages A language priority, filters returned fields and is used as prioritized language code on
     *                         returned value object. If not given all languages are returned.
     * @param string|null $alwaysAvailableLanguage Language code fallback if a given field is not found in $prioritizedLanguages
     *
     * @return array
     */
    public function buildDomainFields(
        array $spiFields,
        $contentType,
        array $prioritizedLanguages = [],
        string $alwaysAvailableLanguage = null
    ) {
        if (!$contentType instanceof SPIContentType && !$contentType instanceof ContentType) {
            throw new InvalidArgumentType('$contentType', 'SPI ContentType | API ContentType');
        }

        $fieldDefinitionsMap = [];
        foreach ($contentType->fieldDefinitions as $fieldDefinition) {
            $fieldDefinitionsMap[$fieldDefinition->id] = $fieldDefinition;
        }

        $fieldInFilterLanguagesMap = [];
        if (!empty($prioritizedLanguages) && $alwaysAvailableLanguage !== null) {
            foreach ($spiFields as $spiField) {
                if (in_array($spiField->languageCode, $prioritizedLanguages)) {
                    $fieldInFilterLanguagesMap[$spiField->fieldDefinitionId] = true;
                }
            }
        }

        $fields = [];
        foreach ($spiFields as $spiField) {
            // We ignore fields in content not part of the content type
            if (!isset($fieldDefinitionsMap[$spiField->fieldDefinitionId])) {
                continue;
            }

            $fieldDefinition = $fieldDefinitionsMap[$spiField->fieldDefinitionId];

            if (!empty($prioritizedLanguages) && !in_array($spiField->languageCode, $prioritizedLanguages)) {
                // If filtering is enabled we ignore fields in other languages then $prioritizedLanguages, if:
                if ($alwaysAvailableLanguage === null) {
                    // Ignore field if we don't have $alwaysAvailableLanguageCode fallback
                    continue;
                } elseif (!empty($fieldInFilterLanguagesMap[$spiField->fieldDefinitionId])) {
                    // Ignore field if it exists in one of the filtered languages
                    continue;
                } elseif ($spiField->languageCode !== $alwaysAvailableLanguage) {
                    // Also ignore if field is not in $alwaysAvailableLanguageCode
                    continue;
                }
            }

            $fields[$fieldDefinition->position][] = new Field(
                [
                    'id' => $spiField->id,
                    'value' => $this->fieldTypeRegistry->getFieldType($spiField->type)
                        ->fromPersistenceValue($spiField->value),
                    'languageCode' => $spiField->languageCode,
                    'fieldDefIdentifier' => $fieldDefinition->identifier,
                    'fieldTypeIdentifier' => $spiField->type,
                ]
            );
        }

        // Sort fields by content type field definition priority
        ksort($fields, SORT_NUMERIC);

        // Flatten array
        return array_merge(...$fields);
    }

    /**
     * Builds a VersionInfo domain object from value object returned from persistence.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $spiVersionInfo
     * @param array $prioritizedLanguages
     *
     * @return \eZ\Publish\Core\Repository\Values\Content\VersionInfo
     */
    public function buildVersionInfoDomainObject(SPIVersionInfo $spiVersionInfo, array $prioritizedLanguages = [])
    {
        // Map SPI statuses to API
        switch ($spiVersionInfo->status) {
            case SPIVersionInfo::STATUS_ARCHIVED:
                $status = APIVersionInfo::STATUS_ARCHIVED;
                break;

            case SPIVersionInfo::STATUS_PUBLISHED:
                $status = APIVersionInfo::STATUS_PUBLISHED;
                break;

            case SPIVersionInfo::STATUS_DRAFT:
            default:
                $status = APIVersionInfo::STATUS_DRAFT;
        }

        // Find prioritised language among names
        $prioritizedNameLanguageCode = null;
        foreach ($prioritizedLanguages as $prioritizedLanguage) {
            if (isset($spiVersionInfo->names[$prioritizedLanguage])) {
                $prioritizedNameLanguageCode = $prioritizedLanguage;
                break;
            }
        }

        return new VersionInfo(
            [
                'id' => $spiVersionInfo->id,
                'versionNo' => $spiVersionInfo->versionNo,
                'modificationDate' => $this->getDateTime($spiVersionInfo->modificationDate),
                'creatorId' => $spiVersionInfo->creatorId,
                'creationDate' => $this->getDateTime($spiVersionInfo->creationDate),
                'status' => $status,
                'initialLanguageCode' => $spiVersionInfo->initialLanguageCode,
                'languageCodes' => $spiVersionInfo->languageCodes,
                'names' => $spiVersionInfo->names,
                'contentInfo' => $this->buildContentInfoDomainObject($spiVersionInfo->contentInfo),
                'prioritizedNameLanguageCode' => $prioritizedNameLanguageCode,
                'creator' => $this->proxyFactory->createUserProxy($spiVersionInfo->creatorId, $prioritizedLanguages),
                'initialLanguage' => $this->proxyFactory->createLanguageProxy($spiVersionInfo->initialLanguageCode),
                'languages' => $this->proxyFactory->createLanguageProxyList($spiVersionInfo->languageCodes),
            ]
        );
    }

    /**
     * Builds a ContentInfo domain object from value object returned from persistence.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ContentInfo $spiContentInfo
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    public function buildContentInfoDomainObject(SPIContentInfo $spiContentInfo)
    {
        // Map SPI statuses to API
        switch ($spiContentInfo->status) {
            case SPIContentInfo::STATUS_TRASHED:
                $status = ContentInfo::STATUS_TRASHED;
                break;

            case SPIContentInfo::STATUS_PUBLISHED:
                $status = ContentInfo::STATUS_PUBLISHED;
                break;

            case SPIContentInfo::STATUS_DRAFT:
            default:
                $status = ContentInfo::STATUS_DRAFT;
        }

        return new ContentInfo(
            [
                'id' => $spiContentInfo->id,
                'contentTypeId' => $spiContentInfo->contentTypeId,
                'name' => $spiContentInfo->name,
                'sectionId' => $spiContentInfo->sectionId,
                'currentVersionNo' => $spiContentInfo->currentVersionNo,
                'published' => $spiContentInfo->isPublished,
                'ownerId' => $spiContentInfo->ownerId,
                'modificationDate' => $spiContentInfo->modificationDate == 0 ?
                    null :
                    $this->getDateTime($spiContentInfo->modificationDate),
                'publishedDate' => $spiContentInfo->publicationDate == 0 ?
                    null :
                    $this->getDateTime($spiContentInfo->publicationDate),
                'alwaysAvailable' => $spiContentInfo->alwaysAvailable,
                'remoteId' => $spiContentInfo->remoteId,
                'mainLanguageCode' => $spiContentInfo->mainLanguageCode,
                'mainLocationId' => $spiContentInfo->mainLocationId,
                'status' => $status,
                'isHidden' => $spiContentInfo->isHidden,
                'contentType' => $this->proxyFactory->createContentTypeProxy($spiContentInfo->contentTypeId),
                'section' => $this->proxyFactory->createSectionProxy($spiContentInfo->sectionId),
                'mainLocation' => $spiContentInfo->mainLocationId !== null ? $this->proxyFactory->createLocationProxy($spiContentInfo->mainLocationId) : null,
                'mainLanguage' => $this->proxyFactory->createLanguageProxy($spiContentInfo->mainLanguageCode),
                'owner' => $this->proxyFactory->createUserProxy($spiContentInfo->ownerId),
            ]
        );
    }

    /**
     * Builds API Relation object from provided SPI Relation object.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Relation $spiRelation
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $sourceContentInfo
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $destinationContentInfo
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Relation
     */
    public function buildRelationDomainObject(
        SPIRelation $spiRelation,
        ContentInfo $sourceContentInfo,
        ContentInfo $destinationContentInfo
    ) {
        $sourceFieldDefinitionIdentifier = null;
        if ($spiRelation->sourceFieldDefinitionId !== null) {
            $contentType = $this->contentTypeHandler->load($sourceContentInfo->contentTypeId);
            foreach ($contentType->fieldDefinitions as $fieldDefinition) {
                if ($fieldDefinition->id !== $spiRelation->sourceFieldDefinitionId) {
                    continue;
                }

                $sourceFieldDefinitionIdentifier = $fieldDefinition->identifier;
                break;
            }
        }

        return new Relation(
            [
                'id' => $spiRelation->id,
                'sourceFieldDefinitionIdentifier' => $sourceFieldDefinitionIdentifier,
                'type' => $spiRelation->type,
                'sourceContentInfo' => $sourceContentInfo,
                'destinationContentInfo' => $destinationContentInfo,
            ]
        );
    }

    /**
     * @deprecated Since 7.2, use buildLocationWithContent(), buildLocation() or (private) mapLocation() instead.
     */
    public function buildLocationDomainObject(
        SPILocation $spiLocation,
        SPIContentInfo $contentInfo = null
    ) {
        if ($contentInfo === null) {
            return $this->buildLocation($spiLocation);
        }

        return $this->mapLocation(
            $spiLocation,
            $this->buildContentInfoDomainObject($contentInfo),
            $this->buildContentProxy($contentInfo)
        );
    }

    public function buildLocation(
        SPILocation $spiLocation,
        array $prioritizedLanguages = [],
        bool $useAlwaysAvailable = true
    ): APILocation {
        if ($this->isRootLocation($spiLocation)) {
            return $this->buildRootLocation($spiLocation);
        }

        $spiContentInfo = $this->contentHandler->loadContentInfo($spiLocation->contentId);

        return $this->mapLocation(
            $spiLocation,
            $this->buildContentInfoDomainObject($spiContentInfo),
            $this->buildContentProxy($spiContentInfo, $prioritizedLanguages, $useAlwaysAvailable),
            $this->proxyFactory->createLocationProxy($spiLocation->parentId, $prioritizedLanguages)
        );
    }

    /**
     * @param \eZ\Publish\SPI\Persistence\Content\Location $spiLocation
     * @param \eZ\Publish\API\Repository\Values\Content\Content|null $content
     * @param \eZ\Publish\SPI\Persistence\Content\ContentInfo|null $spiContentInfo
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function buildLocationWithContent(
        SPILocation $spiLocation,
        ?APIContent $content,
        ?SPIContentInfo $spiContentInfo = null
    ): APILocation {
        if ($this->isRootLocation($spiLocation)) {
            return $this->buildRootLocation($spiLocation);
        }

        if ($content === null) {
            throw new InvalidArgumentException('$content', "Location {$spiLocation->id} has missing Content");
        }

        if ($spiContentInfo !== null) {
            $contentInfo = $this->buildContentInfoDomainObject($spiContentInfo);
        } else {
            $contentInfo = $content->contentInfo;
        }

        $parentLocation = $this->proxyFactory->createLocationProxy(
            $spiLocation->parentId,
        );

        return $this->mapLocation($spiLocation, $contentInfo, $content, $parentLocation);
    }

    /**
     * Builds API Location object for tree root.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location $spiLocation
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Location
     */
    private function buildRootLocation(SPILocation $spiLocation): APILocation
    {
        //  first known commit of eZ Publish 3.x
        $legacyDateTime = $this->getDateTime(1030968000);

        $contentInfo = new ContentInfo([
            'id' => 0,
            'name' => 'Top Level Nodes',
            'sectionId' => 1,
            'mainLocationId' => 1,
            'contentTypeId' => 1,
            'currentVersionNo' => 1,
            'published' => 1,
            'ownerId' => 14, // admin user
            'modificationDate' => $legacyDateTime,
            'publishedDate' => $legacyDateTime,
            'alwaysAvailable' => 1,
            'remoteId' => null,
            'mainLanguageCode' => 'eng-GB',
        ]);

        $content = new Content([
            'versionInfo' => new VersionInfo([
                'names' => [
                    $contentInfo->mainLanguageCode => $contentInfo->name,
                ],
                'contentInfo' => $contentInfo,
                'versionNo' => $contentInfo->currentVersionNo,
                'modificationDate' => $contentInfo->modificationDate,
                'creationDate' => $contentInfo->modificationDate,
                'creatorId' => $contentInfo->ownerId,
            ]),
        ]);

        // NOTE: this is hardcoded workaround for missing ContentInfo on root location
        return $this->mapLocation(
            $spiLocation,
            $contentInfo,
            $content
        );
    }

    private function mapLocation(
        SPILocation $spiLocation,
        ContentInfo $contentInfo,
        APIContent $content,
        ?APILocation $parentLocation = null
    ): APILocation {
        return new Location(
            [
                'content' => $content,
                'contentInfo' => $contentInfo,
                'id' => $spiLocation->id,
                'priority' => $spiLocation->priority,
                'hidden' => $spiLocation->hidden || $contentInfo->isHidden,
                'invisible' => $spiLocation->invisible,
                'explicitlyHidden' => $spiLocation->hidden,
                'remoteId' => $spiLocation->remoteId,
                'parentLocationId' => $spiLocation->parentId,
                'pathString' => $spiLocation->pathString,
                'depth' => $spiLocation->depth,
                'sortField' => $spiLocation->sortField,
                'sortOrder' => $spiLocation->sortOrder,
                'parentLocation' => $parentLocation,
            ]
        );
    }

    /**
     * Build API Content domain objects in bulk and apply to ContentSearchResult.
     *
     * Loading of Content objects are done in bulk.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Search\SearchResult $result SPI search result with SPI ContentInfo items as hits
     * @param array $languageFilter
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ContentInfo[] ContentInfo we did not find content for is returned.
     */
    public function buildContentDomainObjectsOnSearchResult(SearchResult $result, array $languageFilter)
    {
        if (empty($result->searchHits)) {
            return [];
        }

        $contentIds = [];
        $contentTypeIds = [];
        $translations = $languageFilter['languages'] ?? [];
        $useAlwaysAvailable = $languageFilter['useAlwaysAvailable'] ?? true;
        foreach ($result->searchHits as $hit) {
            /** @var \eZ\Publish\SPI\Persistence\Content\ContentInfo $info */
            $info = $hit->valueObject;
            $contentIds[] = $info->id;
            $contentTypeIds[] = $info->contentTypeId;
            // Unless we are told to load all languages, we add main language to translations so they are loaded too
            // Might in some case load more languages then intended, but prioritised handling will pick right one
            if (!empty($languageFilter['languages']) && $useAlwaysAvailable && $info->alwaysAvailable) {
                $translations[] = $info->mainLanguageCode;
            }
        }

        $missingContentList = [];
        $contentList = $this->contentHandler->loadContentList($contentIds, array_unique($translations));
        $contentTypeList = $this->contentTypeHandler->loadContentTypeList(array_unique($contentTypeIds));
        foreach ($result->searchHits as $key => $hit) {
            if (isset($contentList[$hit->valueObject->id])) {
                $hit->valueObject = $this->buildContentDomainObject(
                    $contentList[$hit->valueObject->id],
                    $this->contentTypeDomainMapper->buildContentTypeDomainObject(
                        $contentTypeList[$hit->valueObject->contentTypeId],
                        $languageFilter['languages'] ?? []
                    ),
                    $languageFilter['languages'] ?? [],
                    $useAlwaysAvailable ? $hit->valueObject->mainLanguageCode : null
                );
            } else {
                $missingContentList[] = $hit->valueObject;
                unset($result->searchHits[$key]);
                --$result->totalCount;
            }
        }

        return $missingContentList;
    }

    /**
     * Build API Location and corresponding ContentInfo domain objects and apply to LocationSearchResult.
     *
     * This is done in order to be able to:
     * Load ContentInfo objects in bulk, generate proxy objects for Content that will loaded in bulk on-demand (on use).
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Search\SearchResult $result SPI search result with SPI Location items as hits
     * @param array $languageFilter
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Location[] Locations we did not find content info for is returned.
     */
    public function buildLocationDomainObjectsOnSearchResult(SearchResult $result, array $languageFilter)
    {
        if (empty($result->searchHits)) {
            return [];
        }

        $contentIds = [];
        foreach ($result->searchHits as $hit) {
            $contentIds[] = $hit->valueObject->contentId;
        }

        $missingLocations = [];
        $contentInfoList = $this->contentHandler->loadContentInfoList($contentIds);
        $contentList = $this->buildContentProxyList(
            $contentInfoList,
            !empty($languageFilter['languages']) ? $languageFilter['languages'] : []
        );
        foreach ($result->searchHits as $key => $hit) {
            if (isset($contentInfoList[$hit->valueObject->contentId])) {
                $hit->valueObject = $this->buildLocationWithContent(
                    $hit->valueObject,
                    $contentList[$hit->valueObject->contentId],
                    $contentInfoList[$hit->valueObject->contentId]
                );
            } else {
                $missingLocations[] = $hit->valueObject;
                unset($result->searchHits[$key]);
                --$result->totalCount;
            }
        }

        return $missingLocations;
    }

    /**
     * Creates an array of SPI location create structs from given array of API location create structs.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct $locationCreateStruct
     * @param \eZ\Publish\API\Repository\Values\Content\Location $parentLocation
     * @param mixed $mainLocation
     * @param mixed $contentId
     * @param mixed $contentVersionNo
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Location\CreateStruct
     */
    public function buildSPILocationCreateStruct(
        $locationCreateStruct,
        APILocation $parentLocation,
        $mainLocation,
        $contentId,
        $contentVersionNo
    ) {
        if (!$this->isValidLocationPriority($locationCreateStruct->priority)) {
            throw new InvalidArgumentValue('priority', $locationCreateStruct->priority, 'LocationCreateStruct');
        }

        if (!is_bool($locationCreateStruct->hidden)) {
            throw new InvalidArgumentValue('hidden', $locationCreateStruct->hidden, 'LocationCreateStruct');
        }

        if ($locationCreateStruct->remoteId !== null && (!is_string($locationCreateStruct->remoteId) || empty($locationCreateStruct->remoteId))) {
            throw new InvalidArgumentValue('remoteId', $locationCreateStruct->remoteId, 'LocationCreateStruct');
        }

        if ($locationCreateStruct->sortField !== null && !$this->isValidLocationSortField($locationCreateStruct->sortField)) {
            throw new InvalidArgumentValue('sortField', $locationCreateStruct->sortField, 'LocationCreateStruct');
        }

        if ($locationCreateStruct->sortOrder !== null && !$this->isValidLocationSortOrder($locationCreateStruct->sortOrder)) {
            throw new InvalidArgumentValue('sortOrder', $locationCreateStruct->sortOrder, 'LocationCreateStruct');
        }

        $remoteId = $locationCreateStruct->remoteId;
        if (null === $remoteId) {
            $remoteId = $this->getUniqueHash($locationCreateStruct);
        } else {
            try {
                $this->locationHandler->loadByRemoteId($remoteId);
                throw new InvalidArgumentException(
                    '$locationCreateStructs',
                    "Another Location with remoteId '{$remoteId}' exists"
                );
            } catch (NotFoundException $e) {
                // Do nothing
            }
        }

        return new SPILocationCreateStruct(
            [
                'priority' => $locationCreateStruct->priority,
                'hidden' => $locationCreateStruct->hidden,
                // If we declare the new Location as hidden, it is automatically invisible
                // Otherwise it picks up visibility from parent Location
                // Note: There is no need to check for hidden status of parent, as hidden Location
                // is always invisible as well
                'invisible' => ($locationCreateStruct->hidden === true || $parentLocation->invisible),
                'remoteId' => $remoteId,
                'contentId' => $contentId,
                'contentVersion' => $contentVersionNo,
                // pathIdentificationString will be set in storage
                'pathIdentificationString' => null,
                'mainLocationId' => $mainLocation,
                'sortField' => $locationCreateStruct->sortField !== null ? $locationCreateStruct->sortField : Location::SORT_FIELD_NAME,
                'sortOrder' => $locationCreateStruct->sortOrder !== null ? $locationCreateStruct->sortOrder : Location::SORT_ORDER_ASC,
                'parentId' => $locationCreateStruct->parentLocationId,
            ]
        );
    }

    /**
     * Checks if given $sortField value is one of the defined sort field constants.
     *
     * @param mixed $sortField
     *
     * @return bool
     */
    public function isValidLocationSortField($sortField)
    {
        switch ($sortField) {
            case APILocation::SORT_FIELD_PATH:
            case APILocation::SORT_FIELD_PUBLISHED:
            case APILocation::SORT_FIELD_MODIFIED:
            case APILocation::SORT_FIELD_SECTION:
            case APILocation::SORT_FIELD_DEPTH:
            case APILocation::SORT_FIELD_CLASS_IDENTIFIER:
            case APILocation::SORT_FIELD_CLASS_NAME:
            case APILocation::SORT_FIELD_PRIORITY:
            case APILocation::SORT_FIELD_NAME:
            case APILocation::SORT_FIELD_MODIFIED_SUBNODE:
            case APILocation::SORT_FIELD_NODE_ID:
            case APILocation::SORT_FIELD_CONTENTOBJECT_ID:
                return true;
        }

        return false;
    }

    /**
     * Checks if given $sortOrder value is one of the defined sort order constants.
     *
     * @param mixed $sortOrder
     *
     * @return bool
     */
    public function isValidLocationSortOrder($sortOrder)
    {
        switch ($sortOrder) {
            case APILocation::SORT_ORDER_DESC:
            case APILocation::SORT_ORDER_ASC:
                return true;
        }

        return false;
    }

    /**
     * Checks if given $priority is valid.
     *
     * @param int $priority
     *
     * @return bool
     */
    public function isValidLocationPriority($priority)
    {
        if ($priority === null) {
            return true;
        }

        return is_int($priority) && $priority >= self::MIN_LOCATION_PRIORITY && $priority <= self::MAX_LOCATION_PRIORITY;
    }

    /**
     * Validates given translated list $list, which should be an array of strings with language codes as keys.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param mixed $list
     * @param string $argumentName
     */
    public function validateTranslatedList($list, $argumentName)
    {
        if (!is_array($list)) {
            throw new InvalidArgumentType($argumentName, 'array', $list);
        }

        foreach ($list as $languageCode => $translation) {
            $this->contentLanguageHandler->loadByLanguageCode($languageCode);

            if (!is_string($translation)) {
                throw new InvalidArgumentType($argumentName . "['$languageCode']", 'string', $translation);
            }
        }
    }

    /**
     * Returns \DateTime object from given $timestamp in environment timezone.
     *
     * This method is needed because constructing \DateTime with $timestamp will
     * return the object in UTC timezone.
     *
     * @param int $timestamp
     *
     * @return \DateTime
     */
    public function getDateTime($timestamp)
    {
        $dateTime = new DateTime();
        $dateTime->setTimestamp($timestamp);

        return $dateTime;
    }

    /**
     * Creates unique hash string for given $object.
     *
     * Used for remoteId.
     *
     * @param object $object
     *
     * @return string
     */
    public function getUniqueHash($object)
    {
        return md5(uniqid(get_class($object), true));
    }

    /**
     * Returns true if given location is a tree root.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Location $spiLocation
     *
     * @return bool
     */
    private function isRootLocation(SPILocation $spiLocation): bool
    {
        return $spiLocation->id === $spiLocation->parentId;
    }
}
