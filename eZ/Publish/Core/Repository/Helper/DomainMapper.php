<?php

/**
 * File containing the DomainMapper class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Helper;

use eZ\Publish\API\Repository\Values\Content\Search\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Content as APIContent;
use eZ\Publish\SPI\Persistence\Content\Handler as ContentHandler;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as LocationHandler;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as LanguageHandler;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as TypeHandler;
use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\ContentProxy;
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
use eZ\Publish\SPI\Persistence\Content\Type as SPIType;
use eZ\Publish\SPI\Persistence\Content\Location\CreateStruct as SPILocationCreateStruct;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use DateTime;

/**
 * DomainMapper is an internal service.
 *
 * @internal Meant for internal use by Repository.
 */
class DomainMapper
{
    const MAX_LOCATION_PRIORITY = 2147483647;
    const MIN_LOCATION_PRIORITY = -2147483648;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Handler
     */
    protected $contentHandler;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Location\Handler
     */
    protected $locationHandler;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Type\Handler
     */
    protected $contentTypeHandler;

    /**
     * @var \eZ\Publish\Core\Repository\Helper\ContentTypeDomainMapper
     */
    protected $contentTypeDomainMapper;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Language\Handler
     */
    protected $contentLanguageHandler;

    /**
     * @var FieldTypeRegistry
     */
    protected $fieldTypeRegistry;

    /**
     * Setups service with reference to repository.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Handler $contentHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Location\Handler $locationHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $contentTypeHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Language\Handler $contentLanguageHandler
     * @param FieldTypeRegistry $fieldTypeRegistry
     */
    public function __construct(
        ContentHandler $contentHandler,
        LocationHandler $locationHandler,
        TypeHandler $contentTypeHandler,
        ContentTypeDomainMapper $contentTypeDomainMapper,
        LanguageHandler $contentLanguageHandler,
        FieldTypeRegistry $fieldTypeRegistry
    ) {
        $this->contentHandler = $contentHandler;
        $this->locationHandler = $locationHandler;
        $this->contentTypeHandler = $contentTypeHandler;
        $this->contentTypeDomainMapper = $contentTypeDomainMapper;
        $this->contentLanguageHandler = $contentLanguageHandler;
        $this->fieldTypeRegistry = $fieldTypeRegistry;
    }

    /**
     * Builds a Content domain object from value object returned from persistence.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $spiContent
     * @param ContentType $contentType
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

        return new Content(
            array(
                'internalFields' => $this->buildDomainFields($spiContent->fields, $contentType, $prioritizedLanguages, $fieldAlwaysAvailableLanguage),
                'versionInfo' => $this->buildVersionInfoDomainObject($spiContent->versionInfo, $prioritizedLanguages),
                'contentType' => $contentType,
                'prioritizedFieldLanguageCode' => $prioritizedFieldLanguageCode,
            )
        );
    }

    /**
     * Builds a Content proxy object (lazy loaded, loads as soon as used).
     */
    public function buildContentProxy(
        SPIContent\ContentInfo $info,
        array $prioritizedLanguages = [],
        bool $useAlwaysAvailable = true
    ): APIContent {
        $generator = $this->generatorForContentList([$info], $prioritizedLanguages, $useAlwaysAvailable);

        return new ContentProxy($generator, $info->id);
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
        $generator = $this->generatorForContentList($infoList, $prioritizedLanguages, $useAlwaysAvailable);
        foreach ($infoList as $info) {
            $list[$info->id] = new ContentProxy($generator, $info->id);
        }

        return $list;
    }

    /**
     * @todo Maybe change signature to generatorForContentList($contentIds, $prioritizedLanguages, $translations)
     * @todo to avoid keeping referance to $infoList all the way until the generator is called.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ContentInfo[] $infoList
     * @param string[] $prioritizedLanguages
     * @param bool $useAlwaysAvailable
     *
     * @return \Generator
     */
    private function generatorForContentList(
        array $infoList,
        array $prioritizedLanguages = [],
        bool $useAlwaysAvailable = true
    ): \Generator {
        $contentIds = [];
        $translations = $prioritizedLanguages;
        foreach ($infoList as $info) {
            $contentIds[] = $info->id;
            // Unless we are told to load all languages, we add main language to translations so they are loaded too
            // Might in some case load more languages then intended, but prioritised handling will pick right one
            if (!empty($prioritizedLanguages) && $useAlwaysAvailable && $info->alwaysAvailable) {
                $translations[] = $info->mainLanguageCode;
            }
        }

        unset($infoList);

        $list = $this->contentHandler->loadContentList($contentIds, array_unique($translations));
        while (!empty($list)) {
            $id = yield;
            /** @var \eZ\Publish\SPI\Persistence\Content\ContentInfo $info */
            $info = $list[$id]->versionInfo->contentInfo;
            yield $this->buildContentDomainObject(
                $list[$id],
                //@todo bulk load content type, AND(~/OR~) add in-memory cache for it which will also benefit all cases
                $this->contentTypeDomainMapper->buildContentTypeDomainObject(
                    $this->contentTypeHandler->load($info->contentTypeId),
                    $prioritizedLanguages
                ),
                $prioritizedLanguages,
                $info->alwaysAvailable ? $info->mainLanguageCode : null
            );

            unset($list[$id]);
        }
    }

    /**
     * Returns an array of domain fields created from given array of SPI fields.
     *
     * @throws InvalidArgumentType On invalid $contentType
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field[] $spiFields
     * @param ContentType|SPIType $contentType
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
        if (!$contentType instanceof SPIType && !$contentType instanceof ContentType) {
            throw new InvalidArgumentType('$contentType', 'SPI ContentType | API ContentType');
        }

        $fieldIdentifierMap = array();
        foreach ($contentType->fieldDefinitions as $fieldDefinitions) {
            $fieldIdentifierMap[$fieldDefinitions->id] = $fieldDefinitions->identifier;
        }

        $fieldInFilterLanguagesMap = array();
        if (!empty($prioritizedLanguages) && $alwaysAvailableLanguage !== null) {
            foreach ($spiFields as $spiField) {
                if (in_array($spiField->languageCode, $prioritizedLanguages)) {
                    $fieldInFilterLanguagesMap[$spiField->fieldDefinitionId] = true;
                }
            }
        }

        $fields = array();
        foreach ($spiFields as $spiField) {
            // We ignore fields in content not part of the content type
            if (!isset($fieldIdentifierMap[$spiField->fieldDefinitionId])) {
                continue;
            }

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

            $fields[] = new Field(
                array(
                    'id' => $spiField->id,
                    'value' => $this->fieldTypeRegistry->getFieldType($spiField->type)
                        ->fromPersistenceValue($spiField->value),
                    'languageCode' => $spiField->languageCode,
                    'fieldDefIdentifier' => $fieldIdentifierMap[$spiField->fieldDefinitionId],
                    'fieldTypeIdentifier' => $spiField->type,
                )
            );
        }

        return $fields;
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
            array(
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
            )
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
            array(
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
            )
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
            array(
                'id' => $spiRelation->id,
                'sourceFieldDefinitionIdentifier' => $sourceFieldDefinitionIdentifier,
                'type' => $spiRelation->type,
                'sourceContentInfo' => $sourceContentInfo,
                'destinationContentInfo' => $destinationContentInfo,
            )
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
        if ($spiLocation->id == 1) {
            $legacyDateTime = $this->getDateTime(1030968000); //  first known commit of eZ Publish 3.x
            // NOTE: this is hardcoded workaround for missing ContentInfo on root location
            return $this->mapLocation(
                $spiLocation,
                new ContentInfo([
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
                ]),
                new Content([])
            );
        }

        $spiContentInfo = $this->contentHandler->loadContentInfo($spiLocation->contentId);

        return $this->mapLocation(
            $spiLocation,
            $this->buildContentInfoDomainObject($spiContentInfo),
            $this->buildContentProxy($spiContentInfo, $prioritizedLanguages, $useAlwaysAvailable)
        );
    }

    public function buildLocationWithContent(
        SPILocation $spiLocation,
        APIContent $content,
        SPIContentInfo $spiContentInfo = null
    ): APILocation {
        if ($spiContentInfo !== null) {
            $contentInfo = $this->buildContentInfoDomainObject($spiContentInfo);
        } else {
            $contentInfo = $content->contentInfo;
        }

        return $this->mapLocation($spiLocation, $contentInfo, $content);
    }

    private function mapLocation(SPILocation $spiLocation, ContentInfo $contentInfo, APIContent $content): APILocation
    {
        return new Location(
            array(
                'content' => $content,
                'contentInfo' => $contentInfo,
                'id' => $spiLocation->id,
                'priority' => $spiLocation->priority,
                'hidden' => $spiLocation->hidden,
                'invisible' => $spiLocation->invisible,
                'remoteId' => $spiLocation->remoteId,
                'parentLocationId' => $spiLocation->parentId,
                'pathString' => $spiLocation->pathString,
                'depth' => $spiLocation->depth,
                'sortField' => $spiLocation->sortField,
                'sortOrder' => $spiLocation->sortOrder,
            )
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
        $translations = $languageFilter['languages'] ?? [];
        $useAlwaysAvailable = $languageFilter['useAlwaysAvailable'] ?? true;
        foreach ($result->searchHits as $hit) {
            $contentIds[] = $hit->valueObject->id;
            // Unless we are told to load all languages, we add main language to translations so they are loaded too
            // Might in some case load more languages then intended, but prioritised handling will pick right one
            if (!empty($languageFilter['languages']) && $useAlwaysAvailable && $hit->valueObject->alwaysAvailable) {
                $translations[] = $hit->valueObject->mainLanguageCode;
            }
        }

        $missingContentList = [];
        $contentList = $this->contentHandler->loadContentList($contentIds, array_unique($translations));
        foreach ($result->searchHits as $key => $hit) {
            if (isset($contentList[$hit->valueObject->id])) {
                $hit->valueObject = $this->buildContentDomainObject(
                    $contentList[$hit->valueObject->id],
                    //@todo bulk load content type, AND(~/OR~) add in-memory cache for it which will also benefit all cases
                    $this->contentTypeDomainMapper->buildContentTypeDomainObject(
                        $this->contentTypeHandler->load($hit->valueObject->contentTypeId),
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
            array(
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
            )
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
}
