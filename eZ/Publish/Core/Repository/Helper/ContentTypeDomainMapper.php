<?php

/**
 * File containing the DomainMapper class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Repository\Helper;

use eZ\Publish\API\Repository\Values\ContentType\ContentType as APIContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup as APIContentTypeGroup;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft as APIContentTypeDraft;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct as APIContentTypeUpdateStruct;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition as APIFieldDefinition;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct as APIFieldDefinitionCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct as APIFieldDefinitionUpdateStruct;
use eZ\Publish\API\Repository\Values\User\UserReference as APIUserReference;
use eZ\Publish\Core\Base\Exceptions\ContentTypeFieldDefinitionValidationException;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Repository\Values\ContentType\ContentTypeDraft;
use eZ\Publish\Core\Repository\Values\ContentType\ContentTypeGroup;
use eZ\Publish\Core\Repository\Values\ContentType\ContentTypeGroupProxy;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\SPI\FieldType\FieldType as SPIFieldType;
use eZ\Publish\SPI\Persistence\Content\Type as SPIContentType;
use eZ\Publish\SPI\Persistence\Content\Type\Group as SPIContentTypeGroup;
use eZ\Publish\SPI\Persistence\Content\Type\Handler as SPITypeHandler;
use eZ\Publish\SPI\Persistence\Content\Type\UpdateStruct as SPIContentTypeUpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition as SPIFieldDefinition;
use eZ\Publish\SPI\Persistence\Content\Language\Handler as SPILanguageHandler;
use DateTime;

/**
 * ContentTypeDomainMapper is an internal service.
 *
 * @internal Meant for internal use by Repository.
 */
class ContentTypeDomainMapper
{
    /** @var \eZ\Publish\SPI\Persistence\Content\Type\Handler */
    protected $contentTypeHandler;

    /** @var \eZ\Publish\SPI\Persistence\Content\Language\Handler */
    protected $contentLanguageHandler;

    /** @var FieldTypeRegistry */
    protected $fieldTypeRegistry;

    /**
     * Setups service with reference to repository.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\Handler $contentTypeHandler
     * @param \eZ\Publish\SPI\Persistence\Content\Language\Handler $contentLanguageHandler
     * @param FieldTypeRegistry $fieldTypeRegistry
     */
    public function __construct(
        SPITypeHandler $contentTypeHandler,
        SPILanguageHandler $contentLanguageHandler,
        FieldTypeRegistry $fieldTypeRegistry
    ) {
        $this->contentTypeHandler = $contentTypeHandler;
        $this->contentLanguageHandler = $contentLanguageHandler;
        $this->fieldTypeRegistry = $fieldTypeRegistry;
    }

    /**
     * Builds a ContentType domain object from value object returned by persistence.
     */
    public function buildContentTypeDomainObject(
        SPIContentType $spiContentType,
        array $prioritizedLanguages = []
    ): APIContentType {
        $mainLanguageCode = $this->contentLanguageHandler->load(
            $spiContentType->initialLanguageId
        )->languageCode;

        $fieldDefinitions = [];
        foreach ($spiContentType->fieldDefinitions as $spiFieldDefinition) {
            $fieldDefinitions[] = $this->buildFieldDefinitionDomainObject(
                $spiFieldDefinition,
                $mainLanguageCode,
                $prioritizedLanguages
            );
        }

        return new ContentType(
            [
                'names' => $spiContentType->name,
                'descriptions' => $spiContentType->description,
                'contentTypeGroups' => $this->buildContentTypeGroupProxyList($spiContentType->groupIds, $prioritizedLanguages),
                'fieldDefinitions' => $fieldDefinitions,
                'id' => $spiContentType->id,
                'status' => $spiContentType->status,
                'identifier' => $spiContentType->identifier,
                'creationDate' => $this->getDateTime($spiContentType->created),
                'modificationDate' => $this->getDateTime($spiContentType->modified),
                'creatorId' => $spiContentType->creatorId,
                'modifierId' => $spiContentType->modifierId,
                'remoteId' => $spiContentType->remoteId,
                'urlAliasSchema' => $spiContentType->urlAliasSchema,
                'nameSchema' => $spiContentType->nameSchema,
                'isContainer' => $spiContentType->isContainer,
                'mainLanguageCode' => $mainLanguageCode,
                'defaultAlwaysAvailable' => $spiContentType->defaultAlwaysAvailable,
                'defaultSortField' => $spiContentType->sortField,
                'defaultSortOrder' => $spiContentType->sortOrder,
                'prioritizedLanguages' => $prioritizedLanguages,
                'languageCodes' => $spiContentType->languageCodes,
            ]
        );
    }

    /**
     * Builds ContentType update struct for storage layer.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft $contentTypeDraft
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct $contentTypeUpdateStruct
     * @param \eZ\Publish\API\Repository\Values\User\UserReference $user
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\UpdateStruct
     */
    public function buildSPIContentTypeUpdateStruct(APIContentTypeDraft $contentTypeDraft, APIContentTypeUpdateStruct $contentTypeUpdateStruct, APIUserReference $user)
    {
        $updateStruct = new SPIContentTypeUpdateStruct();

        $updateStruct->identifier = $contentTypeUpdateStruct->identifier !== null ?
            $contentTypeUpdateStruct->identifier :
            $contentTypeDraft->identifier;
        $updateStruct->remoteId = $contentTypeUpdateStruct->remoteId !== null ?
            $contentTypeUpdateStruct->remoteId :
            $contentTypeDraft->remoteId;

        $updateStruct->name = $contentTypeUpdateStruct->names !== null ?
            $contentTypeUpdateStruct->names :
            $contentTypeDraft->names;
        $updateStruct->description = $contentTypeUpdateStruct->descriptions !== null ?
            $contentTypeUpdateStruct->descriptions :
            $contentTypeDraft->descriptions;

        $updateStruct->modified = $contentTypeUpdateStruct->modificationDate !== null ?
            $contentTypeUpdateStruct->modificationDate->getTimestamp() :
            time();
        $updateStruct->modifierId = $contentTypeUpdateStruct->modifierId !== null ?
            $contentTypeUpdateStruct->modifierId :
            $user->getUserId();

        $updateStruct->urlAliasSchema = $contentTypeUpdateStruct->urlAliasSchema !== null ?
            $contentTypeUpdateStruct->urlAliasSchema :
            $contentTypeDraft->urlAliasSchema;
        $updateStruct->nameSchema = $contentTypeUpdateStruct->nameSchema !== null ?
            $contentTypeUpdateStruct->nameSchema :
            $contentTypeDraft->nameSchema;

        $updateStruct->isContainer = $contentTypeUpdateStruct->isContainer !== null ?
            $contentTypeUpdateStruct->isContainer :
            $contentTypeDraft->isContainer;
        $updateStruct->sortField = $contentTypeUpdateStruct->defaultSortField !== null ?
            $contentTypeUpdateStruct->defaultSortField :
            $contentTypeDraft->defaultSortField;
        $updateStruct->sortOrder = $contentTypeUpdateStruct->defaultSortOrder !== null ?
            (int)$contentTypeUpdateStruct->defaultSortOrder :
            $contentTypeDraft->defaultSortOrder;

        $updateStruct->defaultAlwaysAvailable = $contentTypeUpdateStruct->defaultAlwaysAvailable !== null ?
            $contentTypeUpdateStruct->defaultAlwaysAvailable :
            $contentTypeDraft->defaultAlwaysAvailable;
        $updateStruct->initialLanguageId = $this->contentLanguageHandler->loadByLanguageCode(
            $contentTypeUpdateStruct->mainLanguageCode !== null ? $contentTypeUpdateStruct->mainLanguageCode : $contentTypeDraft->mainLanguageCode
        )->id;

        return $updateStruct;
    }

    /**
     * Builds a ContentTypeDraft domain object from value object returned by persistence.
     *
     * Decorates ContentType.
     */
    public function buildContentTypeDraftDomainObject(SPIContentType $spiContentType): APIContentTypeDraft
    {
        return new ContentTypeDraft(
            [
                'innerContentType' => $this->buildContentTypeDomainObject($spiContentType),
            ]
        );
    }

    /**
     * Builds a ContentTypeGroup domain object from value object returned by persistence.
     */
    public function buildContentTypeGroupDomainObject(SPIContentTypeGroup $spiGroup, array $prioritizedLanguages = []): APIContentTypeGroup
    {
        return new ContentTypeGroup(
            [
                'id' => $spiGroup->id,
                'identifier' => $spiGroup->identifier,
                'creationDate' => $this->getDateTime($spiGroup->created),
                'modificationDate' => $this->getDateTime($spiGroup->modified),
                'creatorId' => $spiGroup->creatorId,
                'modifierId' => $spiGroup->modifierId,
                'names' => $spiGroup->name,
                'descriptions' => $spiGroup->description,
                'prioritizedLanguages' => $prioritizedLanguages,
            ]
        );
    }

    /**
     * Builds a list of ContentTypeGroup proxy objects (lazy loaded, loads all as soon as one of them loads).
     */
    public function buildContentTypeGroupProxyList(array $ids, array $prioritizedLanguages = []): array
    {
        $groups = [];
        $generator = $this->generatorForContentTypeGroupList($ids, $prioritizedLanguages);
        foreach ($ids as $id) {
            $groups[] = new ContentTypeGroupProxy($generator, $id);
        }

        return $groups;
    }

    private function generatorForContentTypeGroupList(array $ids, array $prioritizedLanguages = []): \Generator
    {
        $groups = $this->contentTypeHandler->loadGroups($ids);

        while (!empty($groups)) {
            $id = yield;
            yield $this->buildContentTypeGroupDomainObject(
                $groups[$id],
                $prioritizedLanguages
            );
            unset($groups[$id]);
        }
    }

    /**
     * Builds a FieldDefinition domain object from value object returned by persistence.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $spiFieldDefinition
     * @param string $mainLanguageCode
     * @param string[] $prioritizedLanguages
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition
     */
    public function buildFieldDefinitionDomainObject(SPIFieldDefinition $spiFieldDefinition, $mainLanguageCode, array $prioritizedLanguages = [])
    {
        /** @var $fieldType \eZ\Publish\SPI\FieldType\FieldType */
        $fieldType = $this->fieldTypeRegistry->getFieldType($spiFieldDefinition->fieldType);
        $fieldDefinition = new FieldDefinition(
            [
                'names' => $spiFieldDefinition->name,
                'descriptions' => $spiFieldDefinition->description,
                'id' => $spiFieldDefinition->id,
                'identifier' => $spiFieldDefinition->identifier,
                'fieldGroup' => $spiFieldDefinition->fieldGroup,
                'position' => $spiFieldDefinition->position,
                'fieldTypeIdentifier' => $spiFieldDefinition->fieldType,
                'isTranslatable' => $spiFieldDefinition->isTranslatable,
                'isRequired' => $spiFieldDefinition->isRequired,
                'isInfoCollector' => $spiFieldDefinition->isInfoCollector,
                'defaultValue' => $fieldType->fromPersistenceValue($spiFieldDefinition->defaultValue),
                'isSearchable' => !$fieldType->isSearchable() ? false : $spiFieldDefinition->isSearchable,
                'fieldSettings' => (array)$spiFieldDefinition->fieldTypeConstraints->fieldSettings,
                'validatorConfiguration' => (array)$spiFieldDefinition->fieldTypeConstraints->validators,
                'prioritizedLanguages' => $prioritizedLanguages,
                'mainLanguageCode' => $mainLanguageCode,
            ]
        );

        return $fieldDefinition;
    }

    /**
     * Builds SPIFieldDefinition object using API FieldDefinitionUpdateStruct
     * and API FieldDefinition.
     *
     * @deprecated use \eZ\Publish\Core\Repository\Helper\ContentTypeDomainMapper::buildSPIFieldDefinitionFromUpdateStruct()
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentTypeFieldDefinitionValidationException if validator configuration or
     *         field setting do not validate
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct $fieldDefinitionUpdateStruct
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition
     */
    public function buildSPIFieldDefinitionUpdate(APIFieldDefinitionUpdateStruct $fieldDefinitionUpdateStruct, APIFieldDefinition $fieldDefinition)
    {
        /** @var $fieldType \eZ\Publish\SPI\FieldType\FieldType */
        $fieldType = $this->fieldTypeRegistry->getFieldType(
            $fieldDefinition->fieldTypeIdentifier
        );

        $validatorConfiguration = $fieldDefinitionUpdateStruct->validatorConfiguration === null
            ? $fieldDefinition->validatorConfiguration
            : $fieldDefinitionUpdateStruct->validatorConfiguration;
        $fieldSettings = $fieldDefinitionUpdateStruct->fieldSettings === null
            ? $fieldDefinition->fieldSettings
            : $fieldDefinitionUpdateStruct->fieldSettings;

        $validationErrors = [];
        if ($fieldDefinitionUpdateStruct->isSearchable && !$fieldType->isSearchable()) {
            $validationErrors[] = new ValidationError(
                "FieldType '{$fieldDefinition->fieldTypeIdentifier}' is not searchable"
            );
        }
        $validationErrors = array_merge(
            $validationErrors,
            $fieldType->validateValidatorConfiguration($validatorConfiguration),
            $fieldType->validateFieldSettings($fieldSettings)
        );

        if (!empty($validationErrors)) {
            throw new ContentTypeFieldDefinitionValidationException($validationErrors);
        }

        $spiFieldDefinition = new SPIFieldDefinition(
            [
                'id' => $fieldDefinition->id,
                'fieldType' => $fieldDefinition->fieldTypeIdentifier,
                'name' => $fieldDefinitionUpdateStruct->names === null ?
                    $fieldDefinition->getNames() :
                    array_merge($fieldDefinition->getNames(), $fieldDefinitionUpdateStruct->names),
                'description' => $fieldDefinitionUpdateStruct->descriptions === null ?
                    $fieldDefinition->getDescriptions() :
                    array_merge($fieldDefinition->getDescriptions(), $fieldDefinitionUpdateStruct->descriptions),
                'identifier' => $fieldDefinitionUpdateStruct->identifier === null ?
                    $fieldDefinition->identifier :
                    $fieldDefinitionUpdateStruct->identifier,
                'fieldGroup' => $fieldDefinitionUpdateStruct->fieldGroup === null ?
                    $fieldDefinition->fieldGroup :
                    $fieldDefinitionUpdateStruct->fieldGroup,
                'position' => $fieldDefinitionUpdateStruct->position === null ?
                    $fieldDefinition->position :
                    $fieldDefinitionUpdateStruct->position,
                'isTranslatable' => $fieldDefinitionUpdateStruct->isTranslatable === null ?
                    $fieldDefinition->isTranslatable :
                    $fieldDefinitionUpdateStruct->isTranslatable,
                'isRequired' => $fieldDefinitionUpdateStruct->isRequired === null ?
                    $fieldDefinition->isRequired :
                    $fieldDefinitionUpdateStruct->isRequired,
                'isInfoCollector' => $fieldDefinitionUpdateStruct->isInfoCollector === null ?
                    $fieldDefinition->isInfoCollector :
                    $fieldDefinitionUpdateStruct->isInfoCollector,
                'isSearchable' => $fieldDefinitionUpdateStruct->isSearchable === null ?
                    $fieldDefinition->isSearchable :
                    $fieldDefinitionUpdateStruct->isSearchable,
                // These properties are precreated in constructor
                //"fieldTypeConstraints"
                //"defaultValue"
            ]
        );

        $spiFieldDefinition->fieldTypeConstraints->validators = $validatorConfiguration;
        $spiFieldDefinition->fieldTypeConstraints->fieldSettings = $fieldSettings;
        $spiFieldDefinition->defaultValue = $fieldType->toPersistenceValue(
            $fieldType->acceptValue($fieldDefinitionUpdateStruct->defaultValue)
        );

        return $spiFieldDefinition;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct $fieldDefinitionUpdateStruct
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition
     * @param string $mainLanguageCode
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\Core\Base\Exceptions\ContentTypeFieldDefinitionValidationException
     */
    public function buildSPIFieldDefinitionFromUpdateStruct(
        APIFieldDefinitionUpdateStruct $fieldDefinitionUpdateStruct,
        APIFieldDefinition $fieldDefinition,
        string $mainLanguageCode
    ): SPIFieldDefinition {
        /** @var $fieldType \eZ\Publish\SPI\FieldType\FieldType */
        $fieldType = $this->fieldTypeRegistry->getFieldType(
            $fieldDefinition->fieldTypeIdentifier
        );

        $validatorConfiguration = $fieldDefinitionUpdateStruct->validatorConfiguration === null
            ? $fieldDefinition->validatorConfiguration
            : $fieldDefinitionUpdateStruct->validatorConfiguration;
        $fieldSettings = $fieldDefinitionUpdateStruct->fieldSettings === null
            ? $fieldDefinition->fieldSettings
            : $fieldDefinitionUpdateStruct->fieldSettings;

        $validationErrors = [];
        if ($fieldDefinitionUpdateStruct->isSearchable && !$fieldType->isSearchable()) {
            $validationErrors[] = new ValidationError(
                "FieldType '{$fieldDefinition->fieldTypeIdentifier}' is not searchable"
            );
        }
        $validationErrors = array_merge(
            $validationErrors,
            $fieldType->validateValidatorConfiguration($validatorConfiguration),
            $fieldType->validateFieldSettings($fieldSettings)
        );

        if (!empty($validationErrors)) {
            throw new ContentTypeFieldDefinitionValidationException($validationErrors);
        }

        $spiFieldDefinition = new SPIFieldDefinition(
            [
                'id' => $fieldDefinition->id,
                'fieldType' => $fieldDefinition->fieldTypeIdentifier,
                'name' => $fieldDefinitionUpdateStruct->names === null ?
                    $fieldDefinition->getNames() :
                    array_merge($fieldDefinition->getNames(), $fieldDefinitionUpdateStruct->names),
                'description' => $fieldDefinitionUpdateStruct->descriptions === null ?
                    $fieldDefinition->getDescriptions() :
                    array_merge($fieldDefinition->getDescriptions(), $fieldDefinitionUpdateStruct->descriptions),
                'identifier' => $fieldDefinitionUpdateStruct->identifier === null ?
                    $fieldDefinition->identifier :
                    $fieldDefinitionUpdateStruct->identifier,
                'fieldGroup' => $fieldDefinitionUpdateStruct->fieldGroup === null ?
                    $fieldDefinition->fieldGroup :
                    $fieldDefinitionUpdateStruct->fieldGroup,
                'position' => $fieldDefinitionUpdateStruct->position === null ?
                    $fieldDefinition->position :
                    $fieldDefinitionUpdateStruct->position,
                'isTranslatable' => $fieldDefinitionUpdateStruct->isTranslatable === null ?
                    $fieldDefinition->isTranslatable :
                    $fieldDefinitionUpdateStruct->isTranslatable,
                'isRequired' => $fieldDefinitionUpdateStruct->isRequired === null ?
                    $fieldDefinition->isRequired :
                    $fieldDefinitionUpdateStruct->isRequired,
                'isInfoCollector' => $fieldDefinitionUpdateStruct->isInfoCollector === null ?
                    $fieldDefinition->isInfoCollector :
                    $fieldDefinitionUpdateStruct->isInfoCollector,
                'isSearchable' => $fieldDefinitionUpdateStruct->isSearchable === null ?
                    $fieldDefinition->isSearchable :
                    $fieldDefinitionUpdateStruct->isSearchable,
                'mainLanguageCode' => $mainLanguageCode,
                // These properties are precreated in constructor
                //"fieldTypeConstraints"
                //"defaultValue"
            ]
        );

        $spiFieldDefinition->fieldTypeConstraints->validators = $validatorConfiguration;
        $spiFieldDefinition->fieldTypeConstraints->fieldSettings = $fieldSettings;
        $spiFieldDefinition->defaultValue = $fieldType->toPersistenceValue(
            $fieldType->acceptValue($fieldDefinitionUpdateStruct->defaultValue)
        );

        return $spiFieldDefinition;
    }

    /**
     * Builds SPIFieldDefinition object using API FieldDefinitionCreateStruct.
     *
     * @deprecated use \eZ\Publish\Core\Repository\Helper\ContentTypeDomainMapper::buildSPIFieldDefinitionFromCreateStruct()
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentTypeFieldDefinitionValidationException if validator configuration or
     *         field setting do not validate
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct $fieldDefinitionCreateStruct
     * @param \eZ\Publish\SPI\FieldType\FieldType $fieldType
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition
     */
    public function buildSPIFieldDefinitionCreate(APIFieldDefinitionCreateStruct $fieldDefinitionCreateStruct, SPIFieldType $fieldType)
    {
        $spiFieldDefinition = new SPIFieldDefinition(
            [
                'id' => null,
                'identifier' => $fieldDefinitionCreateStruct->identifier,
                'fieldType' => $fieldDefinitionCreateStruct->fieldTypeIdentifier,
                'name' => $fieldDefinitionCreateStruct->names === null ?
                    [] :
                    $fieldDefinitionCreateStruct->names,
                'description' => $fieldDefinitionCreateStruct->descriptions === null ?
                    [] :
                    $fieldDefinitionCreateStruct->descriptions,
                'fieldGroup' => $fieldDefinitionCreateStruct->fieldGroup === null ?
                    '' :
                    $fieldDefinitionCreateStruct->fieldGroup,
                'position' => (int)$fieldDefinitionCreateStruct->position,
                'isTranslatable' => $fieldDefinitionCreateStruct->isTranslatable === null ?
                    true :
                    $fieldDefinitionCreateStruct->isTranslatable,
                'isRequired' => $fieldDefinitionCreateStruct->isRequired === null ?
                    false :
                    $fieldDefinitionCreateStruct->isRequired,
                'isInfoCollector' => $fieldDefinitionCreateStruct->isInfoCollector === null ?
                    false :
                    $fieldDefinitionCreateStruct->isInfoCollector,
                'isSearchable' => $fieldDefinitionCreateStruct->isSearchable === null ?
                    $fieldType->isSearchable() :
                    $fieldDefinitionCreateStruct->isSearchable,
                // These properties are precreated in constructor
                //"fieldTypeConstraints"
                //"defaultValue"
            ]
        );

        $spiFieldDefinition->fieldTypeConstraints->validators = $fieldDefinitionCreateStruct->validatorConfiguration;
        $spiFieldDefinition->fieldTypeConstraints->fieldSettings = $fieldDefinitionCreateStruct->fieldSettings;
        $spiFieldDefinition->defaultValue = $fieldType->toPersistenceValue(
            $fieldType->acceptValue($fieldDefinitionCreateStruct->defaultValue)
        );

        return $spiFieldDefinition;
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct $fieldDefinitionCreateStruct
     * @param \eZ\Publish\SPI\FieldType\FieldType $fieldType
     * @param string $mainLanguageCode
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function buildSPIFieldDefinitionFromCreateStruct(
        APIFieldDefinitionCreateStruct $fieldDefinitionCreateStruct,
        SPIFieldType $fieldType,
        string $mainLanguageCode
    ): SPIFieldDefinition {
        $spiFieldDefinition = new SPIFieldDefinition(
            [
                'id' => null,
                'identifier' => $fieldDefinitionCreateStruct->identifier,
                'fieldType' => $fieldDefinitionCreateStruct->fieldTypeIdentifier,
                'name' => $fieldDefinitionCreateStruct->names === null ?
                    [] :
                    $fieldDefinitionCreateStruct->names,
                'description' => $fieldDefinitionCreateStruct->descriptions === null ?
                    [] :
                    $fieldDefinitionCreateStruct->descriptions,
                'fieldGroup' => $fieldDefinitionCreateStruct->fieldGroup === null ?
                    '' :
                    $fieldDefinitionCreateStruct->fieldGroup,
                'position' => (int)$fieldDefinitionCreateStruct->position,
                'isTranslatable' => $fieldDefinitionCreateStruct->isTranslatable === null ?
                    true :
                    $fieldDefinitionCreateStruct->isTranslatable,
                'isRequired' => $fieldDefinitionCreateStruct->isRequired === null ?
                    false :
                    $fieldDefinitionCreateStruct->isRequired,
                'isInfoCollector' => $fieldDefinitionCreateStruct->isInfoCollector === null ?
                    false :
                    $fieldDefinitionCreateStruct->isInfoCollector,
                'isSearchable' => $fieldDefinitionCreateStruct->isSearchable === null ?
                    $fieldType->isSearchable() :
                    $fieldDefinitionCreateStruct->isSearchable,
                'mainLanguageCode' => $mainLanguageCode,
                // These properties are precreated in constructor
                //"fieldTypeConstraints"
                //"defaultValue"
            ]
        );

        $spiFieldDefinition->fieldTypeConstraints->validators = $fieldDefinitionCreateStruct->validatorConfiguration;
        $spiFieldDefinition->fieldTypeConstraints->fieldSettings = $fieldDefinitionCreateStruct->fieldSettings;
        $spiFieldDefinition->defaultValue = $fieldType->toPersistenceValue(
            $fieldType->acceptValue($fieldDefinitionCreateStruct->defaultValue)
        );

        return $spiFieldDefinition;
    }

    protected function getDateTime(int $timestamp): DateTime
    {
        // Instead of using DateTime(ts) we use setTimeStamp() so timezone does not get set to UTC
        $dateTime = new DateTime();
        $dateTime->setTimestamp($timestamp);

        return $dateTime;
    }
}
