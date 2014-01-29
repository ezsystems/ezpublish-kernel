<?php
/**
 * File containing the eZ\Publish\Core\Repository\ContentService class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package eZ\Publish\Core\Repository
 */

namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\ContentService as ContentServiceInterface;
use eZ\Publish\API\Repository\Repository as RepositoryInterface;
use eZ\Publish\SPI\Persistence\Handler;
use eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct as APIContentUpdateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\Content\TranslationInfo;
use eZ\Publish\API\Repository\Values\Content\TranslationValues as APITranslationValues;
use eZ\Publish\API\Repository\Values\Content\ContentCreateStruct as APIContentCreateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\Content as APIContent;
use eZ\Publish\API\Repository\Values\Content\VersionInfo as APIVersionInfo;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\Content\Relation as APIRelation;
use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;
use eZ\Publish\Core\Base\Exceptions\BadStateException;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\ContentValidationException;
use eZ\Publish\Core\Base\Exceptions\ContentFieldValidationException;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\Core\Repository\Values\Content\ContentUpdateStruct;
use eZ\Publish\Core\Repository\Values\Content\TranslationValues;
use eZ\Publish\SPI\Persistence\Content as SPIContent;
use eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct as SPIMetadataUpdateStruct;
use eZ\Publish\SPI\Persistence\Content\CreateStruct as SPIContentCreateStruct;
use eZ\Publish\SPI\Persistence\Content\UpdateStruct as SPIContentUpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Field as SPIField;
use eZ\Publish\SPI\Persistence\Content\Relation as SPIRelation;
use eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct as SPIRelationCreateStruct;
use Exception;

/**
 * This class provides service methods for managing content
 *
 * @example Examples/content.php
 *
 * @package eZ\Publish\Core\Repository
 */
class ContentService implements ContentServiceInterface
{
    /**
     * @var \eZ\Publish\Core\Repository\Repository
     */
    protected $repository;

    /**
     * @var \eZ\Publish\SPI\Persistence\Handler
     */
    protected $persistenceHandler;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var \eZ\Publish\Core\Repository\DomainMapper
     */
    protected $domainMapper;

    /**
     * @var \eZ\Publish\Core\Repository\RelationProcessor
     */
    protected $relationProcessor;

    /**
     * @var \eZ\Publish\Core\Repository\NameSchemaService
     */
    protected $nameSchemaService;

    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\SPI\Persistence\Handler $handler
     * @param \eZ\Publish\Core\Repository\DomainMapper $domainMapper
     * @param \eZ\Publish\Core\Repository\RelationProcessor $relationProcessor
     * @param \eZ\Publish\Core\Repository\NameSchemaService $nameSchemaService
     * @param array $settings
     */
    public function __construct(
        RepositoryInterface $repository,
        Handler $handler,
        DomainMapper $domainMapper,
        RelationProcessor $relationProcessor,
        NameSchemaService $nameSchemaService,
        array $settings = array()
    )
    {
        $this->repository = $repository;
        $this->persistenceHandler = $handler;
        $this->domainMapper = $domainMapper;
        $this->relationProcessor = $relationProcessor;
        $this->nameSchemaService = $nameSchemaService;
        // Union makes sure default settings are ignored if provided in argument
        $this->settings = $settings + array(
            //'defaultSetting' => array(),
        );
    }

    /**
     * Loads a content info object.
     *
     * To load fields use loadContent
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to read the content
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException - if the content with the given id does not exist
     *
     * @param int $contentId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    public function loadContentInfo( $contentId )
    {
        $contentInfo = $this->internalLoadContentInfo( $contentId );
        if ( !$this->repository->canUser( 'content', 'read', $contentInfo ) )
            throw new UnauthorizedException( 'content', 'read' );

        return $contentInfo;
    }

    /**
     * Loads a content info object.
     *
     * To load fields use loadContent
     *
     * @access private This is only available to services that needs access to Content w/o permissions checks
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException - if the content with the given id does not exist
     *
     * @param mixed $id
     * @param bool $isRemoteId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    public function internalLoadContentInfo( $id, $isRemoteId = false )
    {
        try
        {
            $method = $isRemoteId ? "loadContentInfoByRemoteId" : "loadContentInfo";
            return $this->domainMapper->buildContentInfoDomainObject(
                $this->persistenceHandler->contentHandler()->$method( $id )
            );
        }
        catch ( APINotFoundException $e )
        {
            throw new NotFoundException(
                "Content",
                $id,
                $e
            );
        }
    }

    /**
     * Loads a content info object for the given remoteId.
     *
     * To load fields use loadContent
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to read the content
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException - if the content with the given remote id does not exist
     *
     * @param string $remoteId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    public function loadContentInfoByRemoteId( $remoteId )
    {
        $contentInfo = $this->internalLoadContentInfo( $remoteId, true );

        if ( !$this->repository->canUser( 'content', 'read', $contentInfo ) )
            throw new UnauthorizedException( 'content', 'read' );

        return $contentInfo;
    }

    /**
     * Loads a version info of the given content object.
     *
     * If no version number is given, the method returns the current version
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException - if the version with the given number does not exist
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to load this version
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param int $versionNo the version number. If not given the current version is returned.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    public function loadVersionInfo( ContentInfo $contentInfo, $versionNo = null )
    {
        return $this->loadVersionInfoById( $contentInfo->id, $versionNo );
    }

    /**
     * Loads a version info of the given content object id.
     *
     * If no version number is given, the method returns the current version
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException - if the version with the given number does not exist
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to load this version
     *
     * @param mixed $contentId
     * @param int $versionNo the version number. If not given the current version is returned.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    public function loadVersionInfoById( $contentId, $versionNo = null )
    {
        if ( $versionNo === null )
        {
            $versionNo = $this->loadContentInfo( $contentId )->currentVersionNo;
        }

        try
        {
            $spiVersionInfo = $this->persistenceHandler->contentHandler()->loadVersionInfo(
                $contentId,
                $versionNo
            );
        }
        catch ( APINotFoundException $e )
        {
            throw new NotFoundException(
                "VersionInfo",
                array(
                    "contentId" => $contentId,
                    "versionNo" => $versionNo
                ),
                $e
            );
        }

        $versionInfo = $this->domainMapper->buildVersionInfoDomainObject( $spiVersionInfo );

        if ( $versionInfo->status === APIVersionInfo::STATUS_PUBLISHED )
        {
            $function = "read";
        }
        else
        {
            $function = "versionread";
        }

        if ( !$this->repository->canUser( 'content', $function, $versionInfo ) )
        {
            throw new UnauthorizedException( 'content', $function );
        }

        return $versionInfo;
    }

    /**
     * Loads content in a version for the given content info object.
     *
     * If no version number is given, the method returns the current version
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException - if version with the given number does not exist
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to load this version
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param array $languages A language filter for fields. If not given all languages are returned
     * @param int $versionNo the version number. If not given the current version is returned.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function loadContentByContentInfo( ContentInfo $contentInfo, array $languages = null, $versionNo = null )
    {
        return $this->loadContent(
            $contentInfo->id,
            $languages,
            $versionNo
        );
    }

    /**
     * Loads content in the version given by version info.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to load this version
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     * @param array $languages A language filter for fields. If not given all languages are returned
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function loadContentByVersionInfo( APIVersionInfo $versionInfo, array $languages = null )
    {
        return $this->loadContent(
            $versionInfo->getContentInfo()->id,
            $languages,
            $versionInfo->versionNo
        );
    }

    /**
     * Loads content in a version of the given content object.
     *
     * If no version number is given, the method returns the current version
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the content or version with the given id and languages does not exist
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the user has no access to read content and in case of un-published content: read versions
     *
     * @param int $contentId
     * @param array|null $languages A language filter for fields. If not given all languages are returned
     * @param int|null $versionNo the version number. If not given the current version is returned.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function loadContent( $contentId, array $languages = null, $versionNo = null )
    {
        $content = $this->internalLoadContent( $contentId, $languages, $versionNo );

        if ( !$this->repository->canUser( 'content', 'read', $content ) )
            throw new UnauthorizedException( 'content', 'read' );

        if (
            $content->getVersionInfo()->status !== APIVersionInfo::STATUS_PUBLISHED
            && !$this->repository->canUser( 'content', 'versionread', $content )
        )
            throw new UnauthorizedException( 'content', 'versionread' );

        return $content;
    }

    /**
     * Loads content in a version of the given content object.
     *
     * If no version number is given, the method returns the current version
     *
     * @access private This is only available to services that needs access to Content w/o permissions checks
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the content or version with the given id and languages does not exist
     *
     * @param mixed $contentId
     * @param array|null $languages A language filter for fields. If not given all languages are returned
     * @param int|null $versionNo the version number. If not given the current version is returned.
     * @param bool $isRemoteId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function internalLoadContent( $id, array $languages = null, $versionNo = null, $isRemoteId = false )
    {
        try
        {
            if ( $isRemoteId )
            {
                $spiContentInfo = $this->persistenceHandler->contentHandler()->loadContentInfoByRemoteId( $id );
                $id = $spiContentInfo->id;

                if ( $versionNo === null )
                {
                    $versionNo = $spiContentInfo->currentVersionNo;
                }
            }
            else if ( $versionNo === null )
            {
                $versionNo = $this->persistenceHandler->contentHandler()->loadContentInfo(
                    $id
                )->currentVersionNo;
            }

            $spiContent = $this->persistenceHandler->contentHandler()->load(
                $id,
                $versionNo,
                $languages
            );
        }
        catch ( APINotFoundException $e )
        {
            throw new NotFoundException(
                "Content",
                array(
                    $isRemoteId ? "remoteId" : "id" => $id,
                    "languages" => $languages,
                    "versionNo" => $versionNo
                ),
                $e
            );
        }

        return $this->domainMapper->buildContentDomainObject( $spiContent );
    }

    /**
     * Loads content in a version for the content object reference by the given remote id.
     *
     * If no version is given, the method returns the current version
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException - if the content or version with the given remote id does not exist
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the user has no access to read content and in case of un-published content: read versions
     *
     * @param string $remoteId
     * @param array $languages A language filter for fields. If not given all languages are returned
     * @param int $versionNo the version number. If not given the current version is returned.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function loadContentByRemoteId( $remoteId, array $languages = null, $versionNo = null )
    {
        $content = $this->internalLoadContent( $remoteId, $languages, $versionNo, true );

        if ( !$this->repository->canUser( 'content', 'read', $content ) )
            throw new UnauthorizedException( 'content', 'read' );

        if (
            $content->getVersionInfo()->status !== APIVersionInfo::STATUS_PUBLISHED
            && !$this->repository->canUser( 'content', 'versionread', $content )
        )
            throw new UnauthorizedException( 'content', 'versionread' );

        return $content;
    }

    /**
     * Creates a new content draft assigned to the authenticated user.
     *
     * If a different userId is given in $contentCreateStruct it is assigned to the given user
     * but this required special rights for the authenticated user
     * (this is useful for content staging where the transfer process does not
     * have to authenticate with the user which created the content object in the source server).
     * The user has to publish the draft if it should be visible.
     * In 4.x at least one location has to be provided in the location creation array.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to create the content in the given location
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if there is a provided remoteId which exists in the system
     *                                                                        or there is no location provided (4.x) or multiple locations
     *                                                                        are under the same parent or if the a field value is not accepted by the field type
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException if a field in the $contentCreateStruct is not valid
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException if a required field is missing or is set to an empty value
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentCreateStruct $contentCreateStruct
     * @param \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct[] $locationCreateStructs For each location parent under which a location should be created for the content
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content - the newly created content draft
     */
    public function createContent( APIContentCreateStruct $contentCreateStruct, array $locationCreateStructs = array() )
    {
        if ( $contentCreateStruct->mainLanguageCode === null )
        {
            throw new InvalidArgumentException( "\$contentCreateStruct", "'mainLanguageCode' property must be set" );
        }

        if ( $contentCreateStruct->contentType === null )
        {
            throw new InvalidArgumentException( "\$contentCreateStruct", "'contentType' property must be set" );
        }

        $contentCreateStruct = clone $contentCreateStruct;

        if ( $contentCreateStruct->ownerId === null )
        {
            $contentCreateStruct->ownerId = $this->repository->getCurrentUser()->id;
        }

        if ( $contentCreateStruct->alwaysAvailable === null )
        {
            $contentCreateStruct->alwaysAvailable = false;
        }

        $contentCreateStruct->contentType = $this->repository->getContentTypeService()->loadContentType(
            $contentCreateStruct->contentType->id
        );

        if ( empty( $contentCreateStruct->sectionId ) )
        {
            if ( isset( $locationCreateStructs[0] ) )
            {
                $location = $this->repository->getLocationService()->loadLocation(
                    $locationCreateStructs[0]->parentLocationId
                );
                $contentCreateStruct->sectionId = $location->contentInfo->sectionId;
            }
            else
            {
                $contentCreateStruct->sectionId = 1;
            }
        }

        if ( !$this->repository->canUser( 'content', 'create', $contentCreateStruct, $locationCreateStructs ) )
        {
            throw new UnauthorizedException( 'content', 'create' );
        }

        if ( !empty( $contentCreateStruct->remoteId ) )
        {
            try
            {
                $this->loadContentByRemoteId( $contentCreateStruct->remoteId );

                throw new InvalidArgumentException(
                    "\$contentCreateStruct",
                    "Another content with remoteId '{$contentCreateStruct->remoteId}' exists"
                );
            }
            catch ( APINotFoundException $e )
            {
                // Do nothing
            }
        }
        else
        {
            $contentCreateStruct->remoteId = $this->domainMapper->getUniqueHash( $contentCreateStruct );
        }

        $spiLocationCreateStructs = $this->buildSPILocationCreateStructs( $locationCreateStructs );

        $languageCodes = $this->getLanguageCodesForCreate( $contentCreateStruct );
        $fields = $this->mapFieldsForCreate( $contentCreateStruct );

        $fieldValues = array();
        $spiFields = array();
        $allFieldErrors = array();
        $inputRelations = array();
        $locationIdToContentIdMapping = array();

        foreach ( $contentCreateStruct->contentType->getFieldDefinitions() as $fieldDefinition )
        {
            /** @var $fieldType \eZ\Publish\Core\FieldType\FieldType */
            $fieldType = $this->repository->getFieldTypeService()->buildFieldType(
                $fieldDefinition->fieldTypeIdentifier
            );

            foreach ( $languageCodes as $languageCode )
            {
                $isEmptyValue = false;
                $valueLanguageCode = $fieldDefinition->isTranslatable ? $languageCode : $contentCreateStruct->mainLanguageCode;
                $isLanguageMain = $languageCode === $contentCreateStruct->mainLanguageCode;
                if ( isset( $fields[$fieldDefinition->identifier][$valueLanguageCode] ) )
                {
                    $fieldValue = $fields[$fieldDefinition->identifier][$valueLanguageCode]->value;
                }
                else
                {
                    $fieldValue = $fieldDefinition->defaultValue;
                }

                $fieldValue = $fieldType->acceptValue( $fieldValue );

                if ( $fieldType->isEmptyValue( $fieldValue ) )
                {
                    $isEmptyValue = true;
                    if ( $fieldDefinition->isRequired )
                    {
                        throw new ContentValidationException(
                            "Value for required field definition '{$fieldDefinition->identifier}' with language '{$languageCode}' is empty"
                        );
                    }
                }
                else
                {
                    $fieldErrors = $fieldType->validate(
                        $fieldDefinition,
                        $fieldValue
                    );
                    if ( !empty( $fieldErrors ) )
                    {
                        $allFieldErrors[$fieldDefinition->id][$languageCode] = $fieldErrors;
                    }
                }

                if ( !empty( $allFieldErrors ) )
                {
                    continue;
                }

                $this->relationProcessor->appendFieldRelations(
                    $inputRelations,
                    $locationIdToContentIdMapping,
                    $fieldType,
                    $fieldValue,
                    $fieldDefinition->id
                );
                $fieldValues[$fieldDefinition->identifier][$languageCode] = $fieldValue;

                // Only non-empty value for: translatable field or in main language
                if (
                    ( !$isEmptyValue && $fieldDefinition->isTranslatable ) ||
                    ( !$isEmptyValue && $isLanguageMain )
                )
                {
                    $spiFields[] = new SPIField(
                        array(
                            "id" => null,
                            "fieldDefinitionId" => $fieldDefinition->id,
                            "type" => $fieldDefinition->fieldTypeIdentifier,
                            "value" => $fieldType->toPersistenceValue( $fieldValue ),
                            "languageCode" => $languageCode,
                            "versionNo" => null
                        )
                    );
                }
            }
        }

        if ( !empty( $allFieldErrors ) )
        {
            throw new ContentFieldValidationException( $allFieldErrors );
        }

        $spiContentCreateStruct = new SPIContentCreateStruct(
            array(
                "name" => $this->nameSchemaService->resolve(
                    $contentCreateStruct->contentType->nameSchema,
                    $contentCreateStruct->contentType,
                    $fieldValues,
                    $languageCodes
                ),
                "typeId" => $contentCreateStruct->contentType->id,
                "sectionId" => $contentCreateStruct->sectionId,
                "ownerId" => $contentCreateStruct->ownerId,
                "locations" => $spiLocationCreateStructs,
                "fields" => $spiFields,
                "alwaysAvailable" => $contentCreateStruct->alwaysAvailable,
                "remoteId" => $contentCreateStruct->remoteId,
                "modified" => isset( $contentCreateStruct->modificationDate ) ? $contentCreateStruct->modificationDate->getTimestamp() : time(),
                "initialLanguageId" => $this->persistenceHandler->contentLanguageHandler()->loadByLanguageCode(
                    $contentCreateStruct->mainLanguageCode
                )->id
            )
        );

        $defaultObjectStates = $this->getDefaultObjectStates();

        $this->repository->beginTransaction();
        try
        {
            $spiContent = $this->persistenceHandler->contentHandler()->create( $spiContentCreateStruct );
            $this->relationProcessor->processFieldRelations(
                $inputRelations,
                $spiContent->versionInfo->contentInfo->id,
                $spiContent->versionInfo->versionNo,
                $contentCreateStruct->contentType
            );

            foreach ( $defaultObjectStates as $objectStateGroupId => $objectState )
            {
                $this->persistenceHandler->objectStateHandler()->setContentState(
                    $spiContent->versionInfo->contentInfo->id,
                    $objectStateGroupId,
                    $objectState->id
                );
            }

            $this->repository->commit();
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->domainMapper->buildContentDomainObject( $spiContent );
    }

    /**
     * Returns an array of default content states with content state group id as key.
     *
     * @return \eZ\Publish\SPI\Persistence\Content\ObjectState[]
     */
    protected function getDefaultObjectStates()
    {
        $defaultObjectStatesMap = array();
        $objectStateHandler = $this->persistenceHandler->objectStateHandler();

        foreach ( $objectStateHandler->loadAllGroups() as $objectStateGroup )
        {
            foreach ( $objectStateHandler->loadObjectStates( $objectStateGroup->id ) as $objectState )
            {
                // Only register the first object state which is the default one.
                $defaultObjectStatesMap[$objectStateGroup->id] = $objectState;
                break;
            }
        }

        return $defaultObjectStatesMap;
    }

    /**
     * Returns all language codes used in given $fields.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException if no field value is set in main language
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentCreateStruct $contentCreateStruct
     *
     * @return string[]
     */
    protected function getLanguageCodesForCreate( APIContentCreateStruct $contentCreateStruct )
    {
        $languageCodes = array();

        foreach ( $contentCreateStruct->fields as $field )
        {
            if ( $field->languageCode === null || isset( $languageCodes[$field->languageCode] ) )
            {
                continue;
            }

            $this->persistenceHandler->contentLanguageHandler()->loadByLanguageCode(
                $field->languageCode
            );
            $languageCodes[$field->languageCode] = true;
        }

        if ( !isset( $languageCodes[$contentCreateStruct->mainLanguageCode] ) )
        {
            $this->persistenceHandler->contentLanguageHandler()->loadByLanguageCode(
                $contentCreateStruct->mainLanguageCode
            );
            $languageCodes[$contentCreateStruct->mainLanguageCode] = true;
        }

        return array_keys( $languageCodes );
    }

    /**
     * Returns an array of fields like $fields[$field->fieldDefIdentifier][$field->languageCode].
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException If field definition does not exist in the ContentType
     *                                                                          or value is set for non-translatable field in language
     *                                                                          other than main
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentCreateStruct $contentCreateStruct
     *
     * @return array
     */
    protected function mapFieldsForCreate( APIContentCreateStruct $contentCreateStruct )
    {
        $fields = array();

        foreach ( $contentCreateStruct->fields as $field )
        {
            $fieldDefinition = $contentCreateStruct->contentType->getFieldDefinition( $field->fieldDefIdentifier );

            if ( $fieldDefinition === null )
            {
                throw new ContentValidationException(
                    "Field definition '{$field->fieldDefIdentifier}' does not exist in given ContentType"
                );
            }

            if ( $field->languageCode === null )
            {
                $field = $this->cloneField(
                    $field,
                    array( "languageCode" => $contentCreateStruct->mainLanguageCode )
                );
            }

            if ( !$fieldDefinition->isTranslatable && ( $field->languageCode != $contentCreateStruct->mainLanguageCode ) )
            {
                throw new ContentValidationException(
                    "A value is set for non translatable field definition '{$field->fieldDefIdentifier}' with language '{$field->languageCode}'"
                );
            }

            $fields[$field->fieldDefIdentifier][$field->languageCode] = $field;
        }

        return $fields;
    }

    /**
     * Clones $field with overriding specific properties from given $overrides array.
     *
     * @param Field $field
     * @param array $overrides
     *
     * @return Field
     */
    private function cloneField( Field $field, array $overrides = array() )
    {
        $fieldData = array_merge(
            array(
                'id' => $field->id,
                'value' => $field->value,
                'languageCode' => $field->languageCode,
                'fieldDefIdentifier' => $field->fieldDefIdentifier,
            ),
            $overrides
        );
        return new Field( $fieldData );
    }

    /**
     *
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct[] $locationCreateStructs
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Location\CreateStruct[]
     */
    protected function buildSPILocationCreateStructs( array $locationCreateStructs )
    {
        $spiLocationCreateStructs = array();
        $parentLocationIdSet = array();
        $mainLocation = true;

        foreach ( $locationCreateStructs as $locationCreateStruct )
        {
            if ( isset( $parentLocationIdSet[$locationCreateStruct->parentLocationId] ) )
            {
                throw new InvalidArgumentException(
                    "\$locationCreateStructs",
                    "Multiple LocationCreateStructs with the same parent Location '{$locationCreateStruct->parentLocationId}' are given"
                );
            }

            $parentLocationIdSet[$locationCreateStruct->parentLocationId] = true;
            $parentLocation = $this->repository->getLocationService()->loadLocation(
                $locationCreateStruct->parentLocationId
            );

            $spiLocationCreateStructs[] = $this->domainMapper->buildSPILocationCreateStruct(
                $locationCreateStruct,
                $parentLocation,
                $mainLocation,
                // For Content draft contentId and contentVersionNo are set in ContentHandler upon draft creation
                null,
                null
            );

            // First Location in the list will be created as main Location
            $mainLocation = false;
        }

        return $spiLocationCreateStructs;
    }

    /**
     * Updates the metadata.
     *
     * (see {@link ContentMetadataUpdateStruct}) of a content object - to update fields use updateContent
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to update the content meta data
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the remoteId in $contentMetadataUpdateStruct is set but already exists
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param \eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct $contentMetadataUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content the content with the updated attributes
     */
    public function updateContentMetadata( ContentInfo $contentInfo, ContentMetadataUpdateStruct $contentMetadataUpdateStruct )
    {
        $propertyCount = 0;
        foreach ( $contentMetadataUpdateStruct as $propertyName => $propertyValue )
        {
            if ( isset( $contentMetadataUpdateStruct->$propertyName ) )
            {
                $propertyCount += 1;
            }
        }
        if ( $propertyCount === 0 )
        {
            throw new InvalidArgumentException(
                "\$contentMetadataUpdateStruct",
                "At least one property must be set"
            );
        }

        $loadedContentInfo = $this->loadContentInfo( $contentInfo->id );

        if ( !$this->repository->canUser( 'content', 'edit', $loadedContentInfo ) )
            throw new UnauthorizedException( 'content', 'edit' );

        if ( isset( $contentMetadataUpdateStruct->remoteId ) )
        {
            try
            {
                $existingContentInfo = $this->loadContentInfoByRemoteId( $contentMetadataUpdateStruct->remoteId );

                if ( $existingContentInfo->id !== $loadedContentInfo->id )
                    throw new InvalidArgumentException(
                        "\$contentMetadataUpdateStruct",
                        "Another content with remoteId '{$contentMetadataUpdateStruct->remoteId}' exists"
                    );
            }
            catch ( APINotFoundException $e )
            {
                // Do nothing
            }
        }

        $this->repository->beginTransaction();
        try
        {
            if ( $propertyCount > 1 || !isset( $contentMetadataUpdateStruct->mainLocationId ) )
            {
                $this->persistenceHandler->contentHandler()->updateMetadata(
                    $loadedContentInfo->id,
                    new SPIMetadataUpdateStruct(
                        array(
                            "ownerId" => $contentMetadataUpdateStruct->ownerId,
                            "publicationDate" => isset( $contentMetadataUpdateStruct->publishedDate ) ?
                                $contentMetadataUpdateStruct->publishedDate->getTimestamp() :
                                null,
                            "modificationDate" => isset( $contentMetadataUpdateStruct->modificationDate ) ?
                                $contentMetadataUpdateStruct->modificationDate->getTimestamp() :
                                null,
                            "mainLanguageId" => isset( $contentMetadataUpdateStruct->mainLanguageCode ) ?
                                $this->repository->getContentLanguageService()->loadLanguage(
                                    $contentMetadataUpdateStruct->mainLanguageCode
                                )->id :
                                null,
                            "alwaysAvailable" => $contentMetadataUpdateStruct->alwaysAvailable,
                            "remoteId" => $contentMetadataUpdateStruct->remoteId
                        )
                    )
                );
            }

            // Change main location
            if ( isset( $contentMetadataUpdateStruct->mainLocationId )
                && $loadedContentInfo->mainLocationId !== $contentMetadataUpdateStruct->mainLocationId )
            {
                $this->persistenceHandler->locationHandler()->changeMainLocation(
                    $loadedContentInfo->id,
                    $contentMetadataUpdateStruct->mainLocationId
                );
            }

            // Republish URL aliases to update always-available flag
            if ( isset( $contentMetadataUpdateStruct->alwaysAvailable )
                && $loadedContentInfo->alwaysAvailable !== $contentMetadataUpdateStruct->alwaysAvailable )
            {
                $content = $this->loadContent( $loadedContentInfo->id );
                $this->publishUrlAliasesForContent( $content, false );
            }

            $this->repository->commit();
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return isset( $content ) ? $content : $this->loadContent( $loadedContentInfo->id );
    }

    /**
     * Publishes URL aliases for all locations of a given content.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param boolean $updatePathIdentificationString this parameter is legacy storage specific for updating
     *                      ezcontentobject_tree.path_identification_string, it is ignored by other storage engines
     *
     * @return void
     */
    protected function publishUrlAliasesForContent( APIContent $content, $updatePathIdentificationString = true )
    {
        $urlAliasNames = $this->nameSchemaService->resolveUrlAliasSchema( $content );
        $locations = $this->repository->getLocationService()->loadLocations(
            $content->getVersionInfo()->getContentInfo()
        );
        foreach ( $locations as $location )
        {
            foreach ( $urlAliasNames as $languageCode => $name )
            {
                $this->persistenceHandler->urlAliasHandler()->publishUrlAliasForLocation(
                    $location->id,
                    $location->parentLocationId,
                    $name,
                    $languageCode,
                    $content->contentInfo->alwaysAvailable,
                    $updatePathIdentificationString ?
                        $languageCode === $content->contentInfo->mainLanguageCode :
                        false
                );
            }
        }
    }

    /**
     * Deletes a content object including all its versions and locations including their subtrees.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to delete the content (in one of the locations of the given content object)
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     */
    public function deleteContent( ContentInfo $contentInfo )
    {
        $contentInfo = $this->internalLoadContentInfo( $contentInfo->id );

        if ( !$this->repository->canUser( 'content', 'remove', $contentInfo ) )
            throw new UnauthorizedException( 'content', 'remove' );

        $this->repository->beginTransaction();
        try
        {
            // Load Locations first as deleting Content also deletes belonging Locations
            $spiLocations = $this->persistenceHandler->locationHandler()->loadLocationsByContent( $contentInfo->id );
            $this->persistenceHandler->contentHandler()->deleteContent( $contentInfo->id );
            foreach ( $spiLocations as $spiLocation )
            {
                $this->persistenceHandler->urlAliasHandler()->locationDeleted( $spiLocation->id );
            }
            $this->repository->commit();
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Creates a draft from a published or archived version.
     *
     * If no version is given, the current published version is used.
     * 4.x: The draft is created with the initialLanguage code of the source version or if not present with the main language.
     * It can be changed on updating the version.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the current-user is not allowed to create the draft
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\API\Repository\Values\User\User $creator if set given user is used to create the draft - otherwise the current-user is used
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content - the newly created content draft
     */
    public function createContentDraft( ContentInfo $contentInfo, APIVersionInfo $versionInfo = null, User $creator = null )
    {
        $contentInfo = $this->loadContentInfo( $contentInfo->id );

        if ( $versionInfo !== null )
        {
            // Check that given $contentInfo and $versionInfo belong to the same content
            if ( $versionInfo->getContentInfo()->id != $contentInfo->id )
            {
                throw new InvalidArgumentException(
                    "\$versionInfo",
                    "VersionInfo does not belong to the same content as given ContentInfo"
                );
            }

            $versionInfo = $this->loadVersionInfoById( $contentInfo->id, $versionInfo->versionNo );

            switch ( $versionInfo->status )
            {
                case VersionInfo::STATUS_PUBLISHED:
                case VersionInfo::STATUS_ARCHIVED:
                    break;

                default:
                    // @todo: throw an exception here, to be defined
                    throw new BadStateException(
                        "\$versionInfo",
                        "Draft can not be created from a draft version"
                    );
            }

            $versionNo = $versionInfo->versionNo;
        }
        else if ( $contentInfo->published )
        {
            $versionNo = $contentInfo->currentVersionNo;
        }
        else
        {
            // @todo: throw an exception here, to be defined
            throw new BadStateException(
                "\$contentInfo",
                "Content is not published, draft can be created only from published or archived version"
            );
        }

        if ( $creator === null )
        {
            $creator = $this->repository->getCurrentUser();
        }
        else
        {
            $creator = $this->repository->getUserService()->loadUser( $creator->id );
        }

        if ( !$this->repository->canUser( 'content', 'edit', $contentInfo ) )
            throw new UnauthorizedException( 'content', 'edit', array( 'name' => $contentInfo->name ) );

        $this->repository->beginTransaction();
        try
        {
            $spiContent = $this->persistenceHandler->contentHandler()->createDraftFromVersion(
                $contentInfo->id,
                $versionNo,
                $creator->id
            );
            $this->repository->commit();
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->domainMapper->buildContentDomainObject( $spiContent );
    }

    /**
     * Loads drafts for a user.
     *
     * If no user is given the drafts for the authenticated user a returned
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the current-user is not allowed to load the draft list
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     *
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo the drafts ({@link VersionInfo}) owned by the given user
     */
    public function loadContentDrafts( User $user = null )
    {
        if ( $user === null )
        {
            $user = $this->repository->getCurrentUser();
        }

        // throw early if user has absolutely no access to versionread
        if ( $this->repository->hasAccess( 'content', 'versionread' ) === false )
            throw new UnauthorizedException( 'content', 'versionread' );

        $spiVersionInfoList = $this->persistenceHandler->contentHandler()->loadDraftsForUser( $user->id );

        $versionInfoList = array();
        foreach ( $spiVersionInfoList as $spiVersionInfo )
        {
            $versionInfo = $this->domainMapper->buildVersionInfoDomainObject( $spiVersionInfo );
            if ( !$this->repository->canUser( 'content', 'versionread', $versionInfo ) )
                throw new UnauthorizedException( 'content', 'versionread' );

            $versionInfoList[] = $versionInfo;
        }

        return $versionInfoList;
    }

    /**
     * Translate a version
     *
     * updates the destination version given in $translationInfo with the provided translated fields in $translationValues
     *
     * @example Examples/translation_5x.php
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the current-user is not allowed to update this version
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException if the given destination version is not a draft
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException if a required field is set to an empty value
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException if a field in the $translationValues is not valid
     *
     * @param \eZ\Publish\API\Repository\Values\Content\TranslationInfo $translationInfo
     * @param \eZ\Publish\API\Repository\Values\Content\TranslationValues $translationValues
     * @param \eZ\Publish\API\Repository\Values\User\User $modifier If set, this user is taken as modifier of the version
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content the content draft with the translated fields
     *
     * @since 5.0
     */
    public function translateVersion( TranslationInfo $translationInfo, APITranslationValues $translationValues, User $modifier = null )
    {

    }

    /**
     * Updates the fields of a draft.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to update this version
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException if the version is not a draft
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException if a field in the $contentUpdateStruct is not valid
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException if a required field is set to an empty value
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct $contentUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content the content draft with the updated fields
     */
    public function updateContent( APIVersionInfo $versionInfo, APIContentUpdateStruct $contentUpdateStruct )
    {
        $contentUpdateStruct = clone $contentUpdateStruct;

        /** @var $content \eZ\Publish\Core\Repository\Values\Content\Content */
        $content = $this->loadContent(
            $versionInfo->getContentInfo()->id,
            null,
            $versionInfo->versionNo
        );
        if ( $content->versionInfo->status !== APIVersionInfo::STATUS_DRAFT )
        {
            throw new BadStateException(
                "\$versionInfo",
                "Version is not a draft and can not be updated"
            );
        }

        if ( !$this->repository->canUser( 'content', 'edit', $content ) )
            throw new UnauthorizedException( 'content', 'edit' );

        $mainLanguageCode = $content->contentInfo->mainLanguageCode;
        $languageCodes = $this->getLanguageCodesForUpdate( $contentUpdateStruct, $content );
        $contentType = $this->repository->getContentTypeService()->loadContentType(
            $content->contentInfo->contentTypeId
        );
        $fields = $this->mapFieldsForUpdate(
            $contentUpdateStruct,
            $contentType,
            $mainLanguageCode
        );

        $fieldValues = array();
        $spiFields = array();
        $allFieldErrors = array();
        $inputRelations = array();
        $locationIdToContentIdMapping = array();

        foreach ( $contentType->getFieldDefinitions() as $fieldDefinition )
        {
            /** @var $fieldType \eZ\Publish\SPI\FieldType\FieldType */
            $fieldType = $this->repository->getFieldTypeService()->buildFieldType(
                $fieldDefinition->fieldTypeIdentifier
            );

            foreach ( $languageCodes as $languageCode )
            {
                $isCopied = $isEmpty = $isRetained = false;
                $isLanguageNew = !in_array( $languageCode, $content->versionInfo->languageCodes );
                $valueLanguageCode = $fieldDefinition->isTranslatable ? $languageCode : $mainLanguageCode;
                $isFieldUpdated = isset( $fields[$fieldDefinition->identifier][$valueLanguageCode] );
                $isProcessed = isset( $fieldValues[$fieldDefinition->identifier][$valueLanguageCode] );

                if ( !$isFieldUpdated && !$isLanguageNew )
                {
                    $isRetained = true;
                    $fieldValue = $content->getField( $fieldDefinition->identifier, $valueLanguageCode )->value;
                }
                else if ( !$isFieldUpdated && $isLanguageNew && !$fieldDefinition->isTranslatable )
                {
                    $isCopied = true;
                    $fieldValue = $content->getField( $fieldDefinition->identifier, $valueLanguageCode )->value;
                }
                else if ( $isFieldUpdated )
                {
                    $fieldValue = $fields[$fieldDefinition->identifier][$valueLanguageCode]->value;
                }
                else
                {
                    $fieldValue = $fieldDefinition->defaultValue;
                }

                $fieldValue = $fieldType->acceptValue( $fieldValue );

                if ( $fieldType->isEmptyValue( $fieldValue ) )
                {
                    $isEmpty = true;
                    if ( $fieldDefinition->isRequired )
                    {
                        throw new ContentValidationException(
                            "Value for required field definition '{$fieldDefinition->identifier}' with language '{$languageCode}' is empty"
                        );
                    }
                }
                else
                {
                    $fieldErrors = $fieldType->validate(
                        $fieldDefinition,
                        $fieldValue
                    );
                    if ( !empty( $fieldErrors ) )
                    {
                        $allFieldErrors[$fieldDefinition->id][$languageCode] = $fieldErrors;
                    }
                }

                if ( !empty( $allFieldErrors ) )
                {
                    continue;
                }

                $this->relationProcessor->appendFieldRelations(
                    $inputRelations,
                    $locationIdToContentIdMapping,
                    $fieldType,
                    $fieldValue,
                    $fieldDefinition->id
                );
                $fieldValues[$fieldDefinition->identifier][$languageCode] = $fieldValue;

                if ( $isRetained || $isCopied || ( $isLanguageNew && $isEmpty ) || $isProcessed )
                {
                    continue;
                }

                $spiFields[] = new SPIField(
                    array(
                        "id" => $isLanguageNew ?
                            null :
                            $content->getField( $fieldDefinition->identifier, $languageCode )->id,
                        "fieldDefinitionId" => $fieldDefinition->id,
                        "type" => $fieldDefinition->fieldTypeIdentifier,
                        "value" => $fieldType->toPersistenceValue( $fieldValue ),
                        "languageCode" => $languageCode,
                        "versionNo" => $versionInfo->versionNo
                    )
                );
            }
        }

        if ( !empty( $allFieldErrors ) )
        {
            throw new ContentFieldValidationException( $allFieldErrors );
        }

        $spiContentUpdateStruct = new SPIContentUpdateStruct(
            array(
                "name" => $this->nameSchemaService->resolveNameSchema(
                    $content,
                    $fieldValues,
                    $languageCodes,
                    $contentType
                ),
                "creatorId" => $this->repository->getCurrentUser()->id,
                "fields" => $spiFields,
                "modificationDate" => time(),
                "initialLanguageId" => $this->persistenceHandler->contentLanguageHandler()->loadByLanguageCode(
                    $contentUpdateStruct->initialLanguageCode
                )->id
            )
        );
        $existingRelations = $this->loadRelations( $versionInfo );

        $this->repository->beginTransaction();
        try
        {
            $spiContent = $this->persistenceHandler->contentHandler()->updateContent(
                $versionInfo->getContentInfo()->id,
                $versionInfo->versionNo,
                $spiContentUpdateStruct
            );
            $this->relationProcessor->processFieldRelations(
                $inputRelations,
                $spiContent->versionInfo->contentInfo->id,
                $spiContent->versionInfo->versionNo,
                $contentType,
                $existingRelations
            );
            $this->repository->commit();
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->domainMapper->buildContentDomainObject(
            $spiContent,
            $contentType
        );
    }

    /**
     * Returns all language codes used in given $fields.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException if no field value exists in initial language
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct $contentUpdateStruct
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return array
     */
    protected function getLanguageCodesForUpdate( APIContentUpdateStruct $contentUpdateStruct, APIContent $content )
    {
        if ( $contentUpdateStruct->initialLanguageCode !== null )
        {
            $this->persistenceHandler->contentLanguageHandler()->loadByLanguageCode(
                $contentUpdateStruct->initialLanguageCode
            );
        }
        else
        {
            $contentUpdateStruct->initialLanguageCode = $content->contentInfo->mainLanguageCode;
        }

        $languageCodes = array_fill_keys( $content->versionInfo->languageCodes, true );
        $languageCodes[$contentUpdateStruct->initialLanguageCode] = true;

        foreach ( $contentUpdateStruct->fields as $field )
        {
            if ( $field->languageCode === null || isset( $languageCodes[$field->languageCode] ) )
            {
                continue;
            }

            $this->persistenceHandler->contentLanguageHandler()->loadByLanguageCode(
                $field->languageCode
            );
            $languageCodes[$field->languageCode] = true;
        }

        return array_keys( $languageCodes );
    }

    /**
     * Returns an array of fields like $fields[$field->fieldDefIdentifier][$field->languageCode].
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException If field definition does not exist in the ContentType
     *                                                                          or value is set for non-translatable field in language
     *                                                                          other than main
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct $contentUpdateStruct
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param string $mainLanguageCode
     *
     * @return array
     */
    protected function mapFieldsForUpdate(
        APIContentUpdateStruct $contentUpdateStruct,
        ContentType $contentType,
        $mainLanguageCode
    )
    {
        $fields = array();

        foreach ( $contentUpdateStruct->fields as $field )
        {
            $fieldDefinition = $contentType->getFieldDefinition( $field->fieldDefIdentifier );

            if ( $fieldDefinition === null )
            {
                throw new ContentValidationException(
                    "Field definition '{$field->fieldDefIdentifier}' does not exist in given ContentType"
                );
            }

            if ( $field->languageCode === null )
            {
                if ( $fieldDefinition->isTranslatable )
                {
                    $languageCode = $contentUpdateStruct->initialLanguageCode;
                }
                else
                {
                    $languageCode = $mainLanguageCode;
                }
                $field = $this->cloneField( $field, array( "languageCode" => $languageCode ) );
            }

            if ( !$fieldDefinition->isTranslatable && ( $field->languageCode != $mainLanguageCode ) )
            {
                throw new ContentValidationException(
                    "A value is set for non translatable field definition '{$field->fieldDefIdentifier}' with language '{$field->languageCode}'"
                );
            }

            $fields[$field->fieldDefIdentifier][$field->languageCode] = $field;
        }

        return $fields;
    }

    /**
     * Publishes a content version
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to publish this version
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException if the version is not a draft
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function publishVersion( APIVersionInfo $versionInfo )
    {
        $content = $this->internalLoadContent(
            $versionInfo->contentInfo->id,
            null,
            $versionInfo->versionNo
        );

        if ( !$content->getVersionInfo()->getContentInfo()->published )
        {
            if ( !$this->repository->canUser( "content", "create", $content ) )
            {
                throw new UnauthorizedException( 'content', 'create' );
            }
        }
        else if ( !$this->repository->canUser( 'content', 'edit', $content ) )
        {
            throw new UnauthorizedException( 'content', 'edit' );
        }

        $this->repository->beginTransaction();
        try
        {
            $content = $this->internalPublishVersion( $content->getVersionInfo() );
            $this->repository->commit();
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $content;
    }

    /**
     * Publishes a content version
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException if the version is not a draft
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     * @param int|null $publicationDate
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    protected function internalPublishVersion( APIVersionInfo $versionInfo, $publicationDate = null )
    {
        if ( $versionInfo->status !== APIVersionInfo::STATUS_DRAFT )
        {
            throw new BadStateException( "\$versionInfo", "Only versions in draft status can be published." );
        }

        $metadataUpdateStruct = new SPIMetadataUpdateStruct();
        $metadataUpdateStruct->publicationDate = isset( $publicationDate ) ? $publicationDate : time();
        $metadataUpdateStruct->modificationDate = $metadataUpdateStruct->publicationDate;

        $spiContent = $this->persistenceHandler->contentHandler()->publish(
            $versionInfo->getContentInfo()->id,
            $versionInfo->versionNo,
            $metadataUpdateStruct
        );
        $content = $this->domainMapper->buildContentDomainObject( $spiContent );

        $this->publishUrlAliasesForContent( $content );

        return $content;
    }

    /**
     * removes the given version
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException if the version is in state published
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to remove this version
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     */
    public function deleteVersion( APIVersionInfo $versionInfo )
    {
        if ( $versionInfo->status === APIVersionInfo::STATUS_PUBLISHED )
        {
            throw new BadStateException(
                "\$versionInfo",
                "Version is published and can not be removed"
            );
        }

        if ( !$this->repository->canUser( 'content', 'versionremove', $versionInfo ) )
            throw new UnauthorizedException( 'content', 'versionremove' );

        $this->repository->beginTransaction();
        try
        {
            $this->persistenceHandler->contentHandler()->deleteVersion(
                $versionInfo->getContentInfo()->id,
                $versionInfo->versionNo
            );
            $this->repository->commit();
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Loads all versions for the given content
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to list versions
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     *
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo[] Sorted by creation date
     */
    public function loadVersions( ContentInfo $contentInfo )
    {
        if ( !$this->repository->canUser( 'content', 'versionread', $contentInfo ) )
            throw new UnauthorizedException( 'content', 'versionread' );

        $spiVersionInfoList = $this->persistenceHandler->contentHandler()->listVersions( $contentInfo->id );

        $versions = array();
        foreach ( $spiVersionInfoList as $spiVersionInfo )
        {
            $versionInfo = $this->domainMapper->buildVersionInfoDomainObject( $spiVersionInfo );
            if ( !$this->repository->canUser( 'content', 'versionread', $versionInfo ) )
                throw new UnauthorizedException( 'content', 'versionread' );

            $versions[] = $versionInfo;
        }

        usort(
            $versions,
            function ( $a, $b )
            {
                if ( $a->creationDate->getTimestamp() === $b->creationDate->getTimestamp() ) return 0;
                return ( $a->creationDate->getTimestamp() < $b->creationDate->getTimestamp() ) ? -1 : 1;
            }
        );

        return $versions;
    }

    /**
     * Copies the content to a new location. If no version is given,
     * all versions are copied, otherwise only the given version.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to copy the content to the given location
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct $destinationLocationCreateStruct the target location where the content is copied to
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function copyContent( ContentInfo $contentInfo, LocationCreateStruct $destinationLocationCreateStruct, APIVersionInfo $versionInfo = null)
    {
        if ( !$this->repository->canUser( 'content', 'create', $contentInfo, $destinationLocationCreateStruct ) )
            throw new UnauthorizedException( 'content', 'create' );

        $defaultObjectStates = $this->getDefaultObjectStates();

        $this->repository->beginTransaction();
        try
        {
            $spiContent = $this->persistenceHandler->contentHandler()->copy(
                $contentInfo->id,
                $versionInfo ? $versionInfo->versionNo : null
            );

            foreach ( $defaultObjectStates as $objectStateGroupId => $objectState )
            {
                $this->persistenceHandler->objectStateHandler()->setContentState(
                    $spiContent->versionInfo->contentInfo->id,
                    $objectStateGroupId,
                    $objectState->id
                );
            }

            $content = $this->internalPublishVersion(
                $this->domainMapper->buildVersionInfoDomainObject( $spiContent->versionInfo ),
                $spiContent->versionInfo->creationDate
            );

            $this->repository->getLocationService()->createLocation(
                $content->getVersionInfo()->getContentInfo(),
                $destinationLocationCreateStruct
            );
            $this->repository->commit();
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $content;
    }

    /**
     * Loads all outgoing relations for the given version
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to read this version
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Relation[]
     */
    public function loadRelations( APIVersionInfo $versionInfo )
    {
        if ( $versionInfo->status === APIVersionInfo::STATUS_PUBLISHED )
        {
            $function = "read";
        }
        else
        {
            $function = "versionread";
        }

        if ( !$this->repository->canUser( 'content', $function, $versionInfo ) )
        {
            throw new UnauthorizedException( 'content', $function );
        }

        $contentInfo = $versionInfo->getContentInfo();
        $spiRelations = $this->persistenceHandler->contentHandler()->loadRelations(
            $contentInfo->id,
            $versionInfo->versionNo
        );

        /** @var $relations \eZ\Publish\API\Repository\Values\Content\Relation[] */
        $relations = array();
        foreach ( $spiRelations as $spiRelation )
        {
            $destinationContentInfo = $this->internalLoadContentInfo( $spiRelation->destinationContentId );
            if ( !$this->repository->canUser( 'content', 'read', $destinationContentInfo ) )
                continue;

            $relations[] = $this->domainMapper->buildRelationDomainObject(
                $spiRelation,
                $contentInfo,
                $destinationContentInfo
            );
        }

        return $relations;
    }

    /**
     * Loads all incoming relations for a content object.
     *
     * The relations come only from published versions of the source content objects
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to read this version
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Relation[]
     */
    public function loadReverseRelations( ContentInfo $contentInfo )
    {
        if ( $this->repository->hasAccess( 'content', 'reverserelatedlist' ) !== true )
            throw new UnauthorizedException( 'content', 'reverserelatedlist' );

        $spiRelations = $this->persistenceHandler->contentHandler()->loadReverseRelations(
            $contentInfo->id
        );

        $returnArray = array();
        foreach ( $spiRelations as $spiRelation )
        {
            $sourceContentInfo = $this->internalLoadContentInfo( $spiRelation->sourceContentId );
            if ( !$this->repository->canUser( 'content', 'read', $sourceContentInfo ) )
                continue;

            $returnArray[] = $this->domainMapper->buildRelationDomainObject(
                $spiRelation,
                $sourceContentInfo,
                $contentInfo
            );
        }

        return $returnArray;
    }

    /**
     * Adds a relation of type common.
     *
     * The source of the relation is the content and version
     * referenced by $versionInfo.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to edit this version
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException if the version is not a draft
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $sourceVersion
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $destinationContent the destination of the relation
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Relation the newly created relation
     */
    public function addRelation( APIVersionInfo $sourceVersion, ContentInfo $destinationContent )
    {
        $sourceVersion = $this->loadVersionInfoById(
            $sourceVersion->contentInfo->id,
            $sourceVersion->versionNo
        );

        if ( $sourceVersion->status !== APIVersionInfo::STATUS_DRAFT )
        {
            throw new BadStateException(
                "\$sourceVersion",
                "Relations of type common can only be added to versions of status draft"
            );
        }

        if ( !$this->repository->canUser( 'content', 'edit', $sourceVersion ) )
            throw new UnauthorizedException( 'content', 'edit' );

        $sourceContentInfo = $sourceVersion->getContentInfo();

        $this->repository->beginTransaction();
        try
        {
            $spiRelation = $this->persistenceHandler->contentHandler()->addRelation(
                new SPIRelationCreateStruct(
                    array(
                        'sourceContentId' => $sourceContentInfo->id,
                        'sourceContentVersionNo' => $sourceVersion->versionNo,
                        'sourceFieldDefinitionId' => null,
                        'destinationContentId' => $destinationContent->id,
                        'type' => APIRelation::COMMON
                    )
                )
            );
            $this->repository->commit();
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->domainMapper->buildRelationDomainObject( $spiRelation, $sourceContentInfo, $destinationContent );
    }

    /**
     * Removes a relation of type COMMON from a draft.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed edit this version
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException if the version is not a draft
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if there is no relation of type COMMON for the given destination
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $sourceVersion
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $destinationContent
     */
    public function deleteRelation( APIVersionInfo $sourceVersion, ContentInfo $destinationContent )
    {
        $sourceVersion = $this->loadVersionInfoById(
            $sourceVersion->contentInfo->id,
            $sourceVersion->versionNo
        );

        if ( $sourceVersion->status !== APIVersionInfo::STATUS_DRAFT )
        {
            throw new BadStateException(
                "\$sourceVersion",
                "Relations of type common can only be removed from versions of status draft"
            );
        }

        if ( !$this->repository->canUser( 'content', 'edit', $sourceVersion ) )
            throw new UnauthorizedException( 'content', 'edit' );

        $spiRelations = $this->persistenceHandler->contentHandler()->loadRelations(
            $sourceVersion->getContentInfo()->id,
            $sourceVersion->versionNo,
            APIRelation::COMMON
        );

        if ( empty( $spiRelations ) )
        {
            throw new InvalidArgumentException(
                "\$sourceVersion",
                "There are no relations of type COMMON for the given destination"
            );
        }

        // there should be only one relation of type COMMON for each destination,
        // but in case there were ever more then one, we will remove them all
        // @todo: alternatively, throw BadStateException?
        $this->repository->beginTransaction();
        try
        {
            foreach ( $spiRelations as $spiRelation )
            {
                if ( $spiRelation->destinationContentId == $destinationContent->id )
                {
                    $this->persistenceHandler->contentHandler()->removeRelation(
                        $spiRelation->id,
                        APIRelation::COMMON
                    );
                }
            }
            $this->repository->commit();
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * Adds translation information to the content object
     *
     * @example Examples/translation_5x.php
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed add a translation info
     *
     * @param \eZ\Publish\API\Repository\Values\Content\TranslationInfo $translationInfo
     *
     * @since 5.0
     */
    public function addTranslationInfo( TranslationInfo $translationInfo )
    {

    }

    /**
     * lists the translations done on this content object
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed read translation infos
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param array $filter
     *
     * @todo TBD - filter by source version destination version and languages
     *
     * @return \eZ\Publish\API\Repository\Values\Content\TranslationInfo[]
     *
     * @since 5.0
     */
    public function loadTranslationInfos( ContentInfo $contentInfo, array $filter = array() )
    {

    }

    /**
     * Instantiates a new content create struct object
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param string $mainLanguageCode
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentCreateStruct
     */
    public function newContentCreateStruct( ContentType $contentType, $mainLanguageCode )
    {
        return new ContentCreateStruct(
            array(
                "contentType" => $contentType,
                "mainLanguageCode" => $mainLanguageCode
            )
        );
    }

    /**
     * Instantiates a new content meta data update struct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct
     */
    public function newContentMetadataUpdateStruct()
    {
        return new ContentMetadataUpdateStruct();
    }

    /**
     * Instantiates a new content update struct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct
     */
    public function newContentUpdateStruct()
    {
        return new ContentUpdateStruct();
    }

    /**
     * Instantiates a new TranslationInfo object
     * @return \eZ\Publish\API\Repository\Values\Content\TranslationInfo
     */
    public function newTranslationInfo()
    {
        return new TranslationInfo();
    }

    /**
     * Instantiates a Translation object
     * @return \eZ\Publish\API\Repository\Values\Content\TranslationValues
     */
    public function newTranslationValues()
    {
        return new TranslationValues();
    }
}
