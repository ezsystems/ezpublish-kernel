<?php
/**
 * File containing the eZ\Publish\Core\Repository\ContentService class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package eZ\Publish\Core\Repository
 */

namespace eZ\Publish\Core\Repository;

use eZ\Publish\API\Repository\ContentService as ContentServiceInterface,
    eZ\Publish\API\Repository\Repository as RepositoryInterface,
    eZ\Publish\SPI\Persistence\Handler,
    eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct as APIContentUpdateStruct,
    eZ\Publish\API\Repository\Values\ContentType\ContentType,
    eZ\Publish\API\Repository\Values\Content\TranslationInfo,
    eZ\Publish\API\Repository\Values\Content\TranslationValues as APITranslationValues,
    eZ\Publish\API\Repository\Values\Content\ContentCreateStruct as APIContentCreateStruct,
    eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct,
    eZ\Publish\API\Repository\Values\Content\VersionInfo as APIVersionInfo,
    eZ\Publish\API\Repository\Values\Content\ContentInfo as APIContentInfo,
    eZ\Publish\API\Repository\Values\User\User,
    eZ\Publish\API\Repository\Values\Content\LocationCreateStruct,
    eZ\Publish\API\Repository\Values\Content\Field,
    eZ\Publish\API\Repository\Values\Content\Relation as APIRelation,
    eZ\Publish\API\Repository\Values\Content\Query\Criterion\RemoteId as CriterionRemoteId,
    eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException,

    eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue,
    eZ\Publish\Core\Base\Exceptions\BadStateException,
    eZ\Publish\Core\Base\Exceptions\NotFoundException,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentException,
    eZ\Publish\Core\Base\Exceptions\ContentValidationException,
    eZ\Publish\Core\Base\Exceptions\ContentFieldValidationException,
    eZ\Publish\Core\Base\Exceptions\UnauthorizedException,
    eZ\Publish\Core\Repository\Values\Content\Content,
    eZ\Publish\Core\Repository\Values\Content\ContentInfo,
    eZ\Publish\Core\Repository\Values\Content\VersionInfo,
    eZ\Publish\Core\Repository\Values\Content\ContentCreateStruct,
    eZ\Publish\Core\Repository\Values\Content\ContentUpdateStruct,
    eZ\Publish\Core\Repository\Values\Content\Relation,
    eZ\Publish\Core\Repository\Values\Content\TranslationValues,
    eZ\Publish\Core\Repository\ObjectStorage,

    eZ\Publish\SPI\Persistence\Content\VersionInfo as SPIVersionInfo,
    eZ\Publish\SPI\Persistence\Content\ContentInfo as SPIContentInfo,
    eZ\Publish\SPI\Persistence\Content\Version as SPIVersion,
    eZ\Publish\SPI\Persistence\Content as SPIContent,
    eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct as SPIMetadataUpdateStruct,
    eZ\Publish\SPI\Persistence\Content\CreateStruct as SPIContentCreateStruct,
    eZ\Publish\SPI\Persistence\Content\UpdateStruct as SPIContentUpdateStruct,
    eZ\Publish\SPI\Persistence\Content\Field as SPIField,
    eZ\Publish\SPI\Persistence\Content\Location\CreateStruct as SPILocationCreateStruct,
    eZ\Publish\SPI\Persistence\Content\Relation as SPIRelation,
    eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct as SPIRelationCreateStruct,

    DateTime,
    Exception;

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
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $repository;

    /**
     * @var \eZ\Publish\SPI\Persistence\Handler
     */
    protected $persistenceHandler;

    /**
     * @var ObjectStorage
     */
    protected $objectStore;

    /**
     * @var array
     */
    protected $settings;

    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\SPI\Persistence\Handler $handler
     * @param ObjectStorage $objectStore
     * @param array $settings
     */
    public function __construct( RepositoryInterface $repository, Handler $handler, ObjectStorage $objectStore, array $settings = array() )
    {
        $this->repository = $repository;
        $this->persistenceHandler = $handler;
        $this->objectStore = $objectStore;
        $this->settings = $settings;
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
        // reuse load content since it has cache (and to avoid having several caches pr object)
        // and we would need Content to check permissions anyway.
        return $this->loadContent( $contentId )->contentInfo;
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
        $content = $this->repository->getSearchService()->findSingle( new CriterionRemoteId( $remoteId ), array(), false );

        if ( !$this->repository->canUser( 'content', 'read', $content ) )
            throw new UnauthorizedException( 'content', 'read' );

        return $content->contentInfo;
    }

    /**
     * loads a version info of the given content object.
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
    public function loadVersionInfo( APIContentInfo $contentInfo, $versionNo = null )
    {
        return $this->loadVersionInfoById( $contentInfo->id, $versionNo );
    }

    /**
     * loads a version info of the given content object id.
     *
     * If no version number is given, the method returns the current version
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException - if the version with the given number does not exist
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to load this version
     *
     * @param int $contentId
     * @param int $versionNo the version number. If not given the current version is returned.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    public function loadVersionInfoById( $contentId, $versionNo = null )
    {
        try
        {
            if ( $versionNo === null )
            {
                $versionNo = $this->persistenceHandler->contentHandler()->loadContentInfo(
                    $contentId
                )->currentVersionNo;
            }

            $spiVersionInfo = $this->persistenceHandler->contentHandler()->loadVersionInfo(
                $contentId,
                $versionNo
            );
        }
        catch ( APINotFoundException $e )
        {
            throw new NotFoundException(
                "Content",
                $contentId,
                $e
            );
        }

        return $this->buildVersionInfoDomainObject( $spiVersionInfo );
    }

    /**
     * loads content in a version for the given content info object.
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
    public function loadContentByContentInfo( APIContentInfo $contentInfo, array $languages = null, $versionNo = null )
    {
        return $this->loadContent(
            $contentInfo->id,
            $languages,
            $versionNo
        );
    }

    /**
     * loads content in the version given by version info.
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
     * loads content in a version of the given content object.
     *
     * If no version number is given, the method returns the current version
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the content or version with the given id and languages does not exist
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to load this version
     *
     * @param int $contentId
     * @param array|null $languages A language filter for fields. If not given all languages are returned
     * @param int|null $versionNo the version number. If not given the current version is returned.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function loadContent( $contentId, array $languages = null, $versionNo = null )
    {
        // lookup cached version
        if ( empty( $languages ) &&
            /** @var Repository\Values\Content\Content $content */
            ( $content = $this->objectStore->get( 'content', $contentId ) ) &&
            ( $versionNo === null || $content->getVersionInfo()->versionNo == $versionNo ) )
        {
            return $content;
        }

        try
        {
            if ( $versionNo === null )
            {
                $versionNo = $this->persistenceHandler->contentHandler()->loadContentInfo(
                    $contentId
                )->currentVersionNo;
            }

            $spiContent = $this->persistenceHandler->contentHandler()->load(
                $contentId,
                $versionNo,
                $languages
            );
        }
        catch ( APINotFoundException $e )
        {
            throw new NotFoundException(
                "Content",
                array(
                    "id" => $contentId,
                    "languages" => $languages,
                    "versionNo" => $versionNo
                ),
                $e
            );
        }

        if ( !empty( $languages ) )
        {
            foreach ( $languages as $languageCode )
            {
                if ( !in_array(
                    $this->persistenceHandler->contentLanguageHandler()->loadByLanguageCode( $languageCode )->id,
                    $spiContent->versionInfo->languageIds )
                )
                {
                    throw new NotFoundException(
                        "Content",
                        array(
                            "id" => $contentId,
                            "languages" => $languages,
                            "versionNo" => $versionNo
                        )
                    );
                }
            }
        }

        $content = $this->buildContentDomainObject( $spiContent );

        if (
            empty( $languages ) &&
            ( $versionNo === null ||
              $versionNo === $content->getVersionInfo()->getContentInfo()->currentVersionNo ) )
        {
            $this->objectStore->add( $content );
        }

        return $content;
    }

    /**
     * loads content in a version for the content object reference by the given remote id.
     *
     * If no version is given, the method returns the current version
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException - if the content or version with the given remote id does not exist
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to load this version
     *
     * @param string $remoteId
     * @param array $languages A language filter for fields. If not given all languages are returned
     * @param int $versionNo the version number. If not given the current version is returned.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function loadContentByRemoteId( $remoteId, array $languages = null, $versionNo = null )
    {
        $content = $this->repository->getSearchService()->findSingle( new CriterionRemoteId( $remoteId ), array(), false );
        if ( $languages !== null || $versionNo !== null )
        {
            $content = $this->loadContent( $content->id, $languages, $versionNo );
        }

        if ( !$this->repository->canUser( 'content', 'read', $content ) )
            throw new UnauthorizedException( 'content', 'read' );

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
     *                                                                        are under the same parent
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException if a field in the $contentCreateStruct is not valid
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException if a required field is missing or is set to an empty value
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentCreateStruct $contentCreateStruct
     * @param array $locationCreateStructs an array of {@link \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct} for each location parent under which a location should be created for the content
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content - the newly created content draft
     */
    public function createContent( APIContentCreateStruct $contentCreateStruct, array $locationCreateStructs = array() )
    {
        $contentCreateStruct = clone $contentCreateStruct;

        if ( $contentCreateStruct->ownerId === null )
        {
            $contentCreateStruct->ownerId = $this->repository->getCurrentUser()->id;
        }

        if ( $contentCreateStruct->alwaysAvailable === null )
        {
            $contentCreateStruct->alwaysAvailable = false;
        }

        // @todo: check for user permissions

        if ( isset( $contentCreateStruct->remoteId ) )
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
            $contentCreateStruct->remoteId = md5( uniqid( get_class( $contentCreateStruct ), true ) );
        }

        // If location create structs are not valid throw exception early
        $spiLocationCreateStructs = $this->buildSPILocationCreateStructs( $locationCreateStructs );

        if ( !isset( $contentCreateStruct->sectionId ) )
        {
            $contentCreateStruct->sectionId = 1;
        }

        $fields = array();
        $languageCodes = array( $contentCreateStruct->mainLanguageCode );

        // Map fields to array $fields[$field->fieldDefIdentifier][$field->languageCode]
        // Check for inconsistencies along the way and throw exceptions where needed
        foreach ( $contentCreateStruct->fields as $field )
        {
            $fieldDefinition = $contentCreateStruct->contentType->getFieldDefinition( $field->fieldDefIdentifier );

            if ( !isset( $fieldDefinition ) )
            {
                throw new ContentValidationException(
                    "Field definition '{$field->fieldDefIdentifier}' does not exist in given ContentType"
                );
            }

            if ( $fieldDefinition->isTranslatable )
            {
                if ( isset( $fields[$field->fieldDefIdentifier][$field->languageCode] ) )
                {
                    throw new ContentValidationException(
                        "More than one field is set for translatable field definition '{$field->fieldDefIdentifier}' on language '{$field->languageCode}'"
                    );
                }

                $fields[$field->fieldDefIdentifier][$field->languageCode] = $field;
            }
            else
            {
                if ( isset( $fields[$field->fieldDefIdentifier][$contentCreateStruct->mainLanguageCode] ) )
                {
                    throw new ContentValidationException(
                        "More than one field is set for non translatable field definition '{$field->fieldDefIdentifier}'"
                    );
                }

                if ( $field->languageCode != $contentCreateStruct->mainLanguageCode )
                {
                    throw new ContentValidationException(
                        "A translation is set for non translatable field definition '{$field->fieldDefIdentifier}'"
                    );
                }

                $fields[$field->fieldDefIdentifier][$contentCreateStruct->mainLanguageCode] = $field;
            }

            $languageCodes[] = $field->languageCode;
        }

        $languageCodes = array_unique( $languageCodes );

        $fieldValues = array();
        $spiFields = array();
        $allFieldErrors = array();
        foreach ( $contentCreateStruct->contentType->getFieldDefinitions() as $fieldDefinition )
        {
            /** @var $fieldType \eZ\Publish\Core\FieldType\FieldType */
            $fieldType = $this->repository->getFieldTypeService()->buildFieldType(
                $fieldDefinition->fieldTypeIdentifier
            );

            foreach ( $languageCodes as $languageCode )
            {
                $valueLanguageCode = $fieldDefinition->isTranslatable ? $languageCode : $contentCreateStruct->mainLanguageCode;
                if ( isset( $fields[$fieldDefinition->identifier][$valueLanguageCode] ) )
                {
                    $fieldValue = $fieldType->acceptValue(
                        $fields[$fieldDefinition->identifier][$valueLanguageCode]->value
                    );
                }
                else
                {
                    $fieldValue = $fieldType->acceptValue( $fieldDefinition->defaultValue );
                }

                // ... && !$fieldType->hasContent( $fieldValue )
                if ( $fieldDefinition->isRequired && (string)$fieldValue === "" )
                {
                    throw new ContentValidationException( "Required field '{$fieldDefinition->identifier}' value is empty" );
                }

                $fieldErrors = $fieldType->validate(
                    $fieldDefinition,
                    $fieldValue
                );
                if ( !empty( $fieldErrors ) )
                {
                    $allFieldErrors[$fieldDefinition->id][$languageCode] = $fieldErrors;
                }
                if ( !empty( $allFieldErrors ) )
                {
                    continue;
                }

                $fieldValues[$fieldDefinition->identifier][$languageCode] = $fieldValue;
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

        if ( !empty( $allFieldErrors ) )
        {
            throw new ContentFieldValidationException( $allFieldErrors );
        }

        $spiContentCreateStruct = new SPIContentCreateStruct(
            array(
                "name" => $this->repository->getNameSchemaService()->resolve(
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
                "modified" => isset( $contentCreateStruct->modificationDate )
                    ? $contentCreateStruct->modificationDate->getTimestamp()
                    : time(),
                "initialLanguageId" => $this->persistenceHandler->contentLanguageHandler()->loadByLanguageCode(
                    $contentCreateStruct->mainLanguageCode
                )->id
            )
        );

        $this->repository->beginTransaction();
        try
        {
            $spiContent = $this->persistenceHandler->contentHandler()->create( $spiContentCreateStruct );
            $this->repository->commit();
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->buildContentDomainObject( $spiContent );
    }

    /**
     * Creates an array of SPI location create structs from given array of API location create structs
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     *
     * @param \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct[] $locationCreateStructs
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Location\CreateStruct[]
     */
    protected function buildSPILocationCreateStructs( array $locationCreateStructs )
    {
        $spiLocationCreateStructs = array();

        $parentLocationIdCache = array();
        foreach ( $locationCreateStructs as $index => $locationCreateStruct )
        {
            $parentLocation = $this->repository->getLocationService()->loadLocation( $locationCreateStruct->parentLocationId );
            if ( in_array( $parentLocation->id, $parentLocationIdCache ) )
            {
                throw new InvalidArgumentException(
                    "\$locationCreateStructs",
                    "Multiple LocationCreateStructs with parent Location '{$parentLocation->id}' exist"
                );
            }
            $parentLocationIdCache[] = $parentLocation->id;

            if ( $locationCreateStruct->priority !== null && !is_numeric( $locationCreateStruct->priority ) )
            {
                throw new InvalidArgumentValue( "priority", $locationCreateStruct->priority, "LocationCreateStruct" );
            }

            if ( !is_bool( $locationCreateStruct->hidden ) )
            {
                throw new InvalidArgumentValue( "hidden", $locationCreateStruct->hidden, "LocationCreateStruct" );
            }

            if ( $locationCreateStruct->remoteId !== null && ( !is_string( $locationCreateStruct->remoteId ) || empty( $locationCreateStruct->remoteId ) ) )
            {
                throw new InvalidArgumentValue( "remoteId", $locationCreateStruct->remoteId, "LocationCreateStruct" );
            }

            if ( $locationCreateStruct->sortField !== null && !is_numeric( $locationCreateStruct->sortField ) )
            {
                throw new InvalidArgumentValue( "sortField", $locationCreateStruct->sortField, "LocationCreateStruct" );
            }

            if ( $locationCreateStruct->sortOrder !== null && !is_numeric( $locationCreateStruct->sortOrder ) )
            {
                throw new InvalidArgumentValue( "sortOrder", $locationCreateStruct->sortOrder, "LocationCreateStruct" );
            }

            if ( null === $locationCreateStruct->remoteId )
            {
                $locationCreateStruct->remoteId = md5( uniqid( get_class( $locationCreateStruct ), true ) );
            }
            else
            {
                try
                {
                    $this->repository->getLocationService()->loadLocationByRemoteId( $locationCreateStruct->remoteId );
                    throw new InvalidArgumentException(
                        "\$locationCreateStructs",
                        "Another Location with remoteId '{$locationCreateStruct->remoteId}' exists"
                    );
                }
                catch ( APINotFoundException $e )
                {
                    // Do nothing
                }
            }

            $spiLocationCreateStructs[] = new SPILocationCreateStruct(
                array(
                    "priority" => $locationCreateStruct->priority,
                    "hidden" => $locationCreateStruct->hidden,
                    "invisible" => ( $locationCreateStruct->hidden === true || $parentLocation->hidden || $parentLocation->invisible ),
                    "remoteId" => $locationCreateStruct->remoteId,
                    // contentId and contentVersion are set in ContentHandler upon draft creation
                    "contentId" => null,
                    "contentVersion" => null,
                    // @todo: set pathIdentificationString
                    "pathIdentificationString" => null,
                    "mainLocationId" => ( $index === 0 ),
                    "sortField" => $locationCreateStruct->sortField,
                    "sortOrder" => $locationCreateStruct->sortOrder,
                    "parentId" => $locationCreateStruct->parentLocationId
                )
            );
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
    public function updateContentMetadata( APIContentInfo $contentInfo, ContentMetadataUpdateStruct $contentMetadataUpdateStruct )
    {
        $propertyCount = 0;
        foreach ( $contentMetadataUpdateStruct as $propertyName => $propertyValue )
        {
            if ( isset( $contentMetadataUpdateStruct->$propertyName ) ) $propertyCount += 1;
        }

        if ( $propertyCount === 0 )
        {
            throw new InvalidArgumentException(
                "\$contentMetadataUpdateStruct",
                "At least one property must be set"
            );
        }

        $this->repository->beginTransaction();
        try
        {
            if ( $propertyCount > 1 || empty( $contentMetadataUpdateStruct->mainLocationId ) )
            {
                if ( isset( $contentMetadataUpdateStruct->remoteId ) )
                {
                    try
                    {
                        $existingContent = $this->loadContentByRemoteId( $contentMetadataUpdateStruct->remoteId );

                        if ( $existingContent->id !== $contentInfo->id )
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

                $spiMetadataUpdateStruct = new SPIMetadataUpdateStruct(
                    array(
                        "ownerId" => $contentMetadataUpdateStruct->ownerId,
                        //@todo changes always available name
                        //"name" => $contentMetadataUpdateStruct->name,
                        "publicationDate" => isset( $contentMetadataUpdateStruct->publishedDate )
                            ? $contentMetadataUpdateStruct->publishedDate->getTimestamp()
                            : null,
                        "modificationDate" => isset( $contentMetadataUpdateStruct->modificationDate )
                            ? $contentMetadataUpdateStruct->modificationDate->getTimestamp()
                            : null,
                        "mainLanguageId" => isset( $contentMetadataUpdateStruct->mainLanguageCode )
                            ? $this->repository->getContentLanguageService()->loadLanguage(
                                $contentMetadataUpdateStruct->mainLanguageCode
                            )->id
                            : null,
                        "alwaysAvailable" => $contentMetadataUpdateStruct->alwaysAvailable,
                        "remoteId" => $contentMetadataUpdateStruct->remoteId
                    )
                );
                $this->persistenceHandler->contentHandler()->updateMetadata(
                    $contentInfo->id,
                    $spiMetadataUpdateStruct
                );
            }

            if ( isset( $contentMetadataUpdateStruct->mainLocationId ) )
            {
                $this->persistenceHandler->locationHandler()->changeMainLocation(
                    $contentInfo->id,
                    $contentMetadataUpdateStruct->mainLocationId
                );
            }
            $this->repository->commit();
            $this->objectStore->discard( 'content', $contentInfo->id );
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->loadContent( $contentInfo->id );
    }

    /**
     * deletes a content object including all its versions and locations including their subtrees.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to delete the content (in one of the locations of the given content object)
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     */
    public function deleteContent( APIContentInfo $contentInfo )
    {
        $this->repository->beginTransaction();
        try
        {
            $this->persistenceHandler->contentHandler()->deleteContent( $contentInfo->id );
            $this->repository->commit();
            $this->objectStore->discard( 'content', $contentInfo->id );
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
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to create the draft
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\API\Repository\Values\User\User $user if set given user is used to create the draft - otherwise the current user is used
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content - the newly created content draft
     */
    public function createContentDraft( APIContentInfo $contentInfo, APIVersionInfo $versionInfo = null, User $user = null )
    {
        if ( !isset( $user ) )
        {
            $user = $this->repository->getCurrentUser();
        }

        if ( isset( $versionInfo ) )
        {
            if ( !in_array( $versionInfo->status, array( VersionInfo::STATUS_PUBLISHED, VersionInfo::STATUS_ARCHIVED ) ) )
            {
                // @TODO: throw an exception here, to be defined
                throw new BadStateException(
                    "\$versionInfo",
                    "Draft can not be created from a draft version"
                );
            }

            $versionNo = $versionInfo->versionNo;
        }
        elseif ( $contentInfo->published )
        {
            $versionNo = $contentInfo->currentVersionNo;
        }
        else
        {
            // @TODO: throw an exception here, to be defined
            throw new BadStateException(
                "\$contentInfo",
                "Content is not published, draft can be created only from published or archived version"
            );
        }


        $this->repository->beginTransaction();
        try
        {
            $spiContent = $this->persistenceHandler->contentHandler()->createDraftFromVersion(
                $contentInfo->id,
                $versionNo,
                $user->id
            );
            $this->repository->commit();
            $this->objectStore->discard( 'content', $contentInfo->id );
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->buildContentDomainObject( $spiContent );
    }

    /**
     * Load drafts for a user.
     *
     * If no user is given the drafts for the authenticated user a returned
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to load the draft list
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user
     *
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo the drafts ({@link VersionInfo}) owned by the given user
     */
    public function loadContentDrafts( User $user = null )
    {
        if ( !isset( $user ) )
        {
            $user = $this->repository->getCurrentUser();
        }

        $spiVersionInfoList = $this->persistenceHandler->contentHandler()->loadDraftsForUser( $user->id );

        $versionInfoList = array();
        foreach ( $spiVersionInfoList as $spiVersionInfo )
        {
            $versionInfoList[] = $this->buildVersionInfoDomainObject( $spiVersionInfo );
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
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to update this version
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException if the given destination version is not a draft
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException if a required field is set to an empty value
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException if a field in the $translationValues is not valid
     *
     * @param \eZ\Publish\API\Repository\Values\Content\TranslationInfo $translationInfo
     * @param \eZ\Publish\API\Repository\Values\Content\TranslationValues $translationValues
     * @param \eZ\Publish\API\Repository\Values\User\User $user If set, this user is taken as modifier of the version
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content the content draft with the translated fields
     *
     * @since 5.0
     */
    public function translateVersion( TranslationInfo $translationInfo, APITranslationValues $translationValues, User $user = null )
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
        $contentId = $versionInfo->getContentInfo()->id;
        /** @var $content \eZ\Publish\Core\Repository\Values\Content\Content */
        $content = $this->loadContent(
            $contentId,
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

        $fields = array();
        $languageCodes = array();
        $initialLanguageCode = $contentUpdateStruct->initialLanguageCode ?: $content->contentInfo->mainLanguageCode;

        foreach ( $contentUpdateStruct->fields as $field )
        {
            $fieldDefinition = $content->contentType->getFieldDefinition( $field->fieldDefIdentifier );
            if ( !isset( $fieldDefinition ) )
            {
                throw new ContentValidationException(
                    "Field definition '{$field->fieldDefIdentifier}' does not exist in given ContentType"
                );
            }

            $fieldLanguageCode = $field->languageCode ?: $initialLanguageCode;

            if ( $fieldDefinition->isTranslatable )
            {
                if ( isset( $fields[$field->fieldDefIdentifier][$fieldLanguageCode] ) )
                {
                    throw new ContentValidationException(
                        "More than one field is set for translatable field definition '{$field->fieldDefIdentifier}' on language with code '{$fieldLanguageCode}'"
                    );
                }

                $fields[$field->fieldDefIdentifier][$fieldLanguageCode] = $field;
            }
            else
            {
                if ( isset( $fields[$field->fieldDefIdentifier][$content->contentInfo->mainLanguageCode] ) )
                {
                    throw new ContentValidationException(
                        "More than one field is set for non translatable field definition '{$field->fieldDefIdentifier}'"
                    );
                }

                if ( $fieldLanguageCode != $content->contentInfo->mainLanguageCode )
                {
                    throw new ContentValidationException(
                        "A translation '{$content->contentInfo->mainLanguageCode}/{$fieldLanguageCode}' is set for non translatable field definition '{$field->fieldDefIdentifier}'"
                    );
                }

                $fields[$field->fieldDefIdentifier][$content->contentInfo->mainLanguageCode] = $field;
            }

            $languageCodes[] = $fieldLanguageCode;
        }

        $languageCodes = array_unique( $languageCodes );
        $fieldValues = array();
        $spiFields = array();
        $allFieldErrors = array();

        foreach ( $content->contentType->getFieldDefinitions() as $fieldDefinition )
        {
            /** @var $fieldType \eZ\Publish\SPI\FieldType\FieldType */
            $fieldType = $this->repository->getFieldTypeService()->buildFieldType(
                $fieldDefinition->fieldTypeIdentifier
            );

            foreach ( $languageCodes as $languageCode )
            {
                $isLanguageNew = !in_array( $languageCode, $content->versionInfo->languageCodes );
                $valueLanguageCode = $fieldDefinition->isTranslatable ? $languageCode : $content->contentInfo->mainLanguageCode;
                $existingField = $content->getField( $fieldDefinition->identifier, $valueLanguageCode );
                $updateField = isset( $fields[$fieldDefinition->identifier][$valueLanguageCode] )
                    ? $fields[$fieldDefinition->identifier][$valueLanguageCode]
                    : null;

                if ( !isset( $updateField ) && !$isLanguageNew )
                {
                    continue;
                }

                if ( isset( $existingField ) && !isset( $updateField ) && $isLanguageNew && !$fieldDefinition->isTranslatable )
                 {
                    $fieldValue = $fieldType->acceptValue( $existingField->value );
                }
                elseif ( isset( $updateField ) )
                {
                    $fieldValue = $fieldType->acceptValue( $updateField->value );
                }
                else
                {
                    $fieldValue = $fieldType->acceptValue( $fieldDefinition->defaultValue );
                }

                if ( $fieldDefinition->isRequired && (string)$fieldValue === "" )
                {
                    throw new ContentValidationException( "Required field '{$fieldDefinition->identifier}' value is empty" );
                }

                $fieldErrors = $fieldType->validate(
                    $fieldDefinition,
                    $fieldValue
                );
                if ( !empty( $fieldErrors ) )
                {
                    $allFieldErrors[$fieldDefinition->id][$languageCode] = $fieldErrors;
                }
                if ( !empty( $allFieldErrors ) )
                {
                    continue;
                }

                $fieldValues[$fieldDefinition->identifier][$languageCode] = $fieldValue;
                $spiFields[] = new SPIField(
                    array(
                        "id" => ( isset( $existingField ) && !$isLanguageNew ) ? $existingField->id : null,
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
                "name" => $this->repository->getNameSchemaService()->resolveNameSchema( $content, $fieldValues ),
                "creatorId" => $this->repository->getCurrentUser()->id,
                "fields" => $spiFields,
                "modificationDate" => time(),
                "initialLanguageId" => $this->persistenceHandler->contentLanguageHandler()->loadByLanguageCode(
                    $initialLanguageCode
                )->id
            )
        );

        $this->repository->beginTransaction();
        try
        {
            $spiContent = $this->persistenceHandler->contentHandler()->updateContent(
                $versionInfo->getContentInfo()->id,
                $versionInfo->versionNo,
                $spiContentUpdateStruct
            );
            $this->repository->commit();
            $this->objectStore->discard( 'content', $contentId );
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->buildContentDomainObject( $spiContent );
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
        $contentId = $versionInfo->getContentInfo()->id;
        $loadedVersionInfo = $this->loadVersionInfoById(
            $versionInfo->getContentInfo()->id,
            $versionInfo->versionNo
        );


        $this->repository->beginTransaction();
        try
        {
            $content = $this->internalPublishVersion( $loadedVersionInfo );
            $this->repository->commit();
            $this->objectStore->discard( 'content', $contentId );
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
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to publish this version
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
            throw new BadStateException( "versionInfo", "only versions in draft status can be published" );
        }

        $metadataUpdateStruct = new SPIMetadataUpdateStruct();
        $metadataUpdateStruct->publicationDate = isset( $publicationDate ) ? $publicationDate : time();
        $metadataUpdateStruct->modificationDate = $metadataUpdateStruct->publicationDate;

        $spiContent = $this->persistenceHandler->contentHandler()->publish(
            $versionInfo->getContentInfo()->id,
            $versionInfo->versionNo,
            $metadataUpdateStruct
        );
        $content = $this->buildContentDomainObject( $spiContent );

        $urlAliasNames = $this->repository->getNameSchemaService()->resolveUrlAliasSchema( $content );
        $alwaysAvailableLanguageCode = ( isset( $urlAliasNames["always-available"] ) && isset( $urlAliasNames[$urlAliasNames["always-available"]] ) )
            ? $urlAliasNames[$urlAliasNames["always-available"]]
            : null;
        foreach ( $spiContent->locations as $spiLocation )
        {
            foreach ( $urlAliasNames as $languageCode => $name )
            {
                $this->persistenceHandler->urlAliasHandler()->publishUrlAliasForLocation(
                    $spiLocation->id,
                    $name,
                    $languageCode,
                    $languageCode === $alwaysAvailableLanguageCode
                );
            }
        }

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


        $this->repository->beginTransaction();
        try
        {
            $success = $this->persistenceHandler->contentHandler()->deleteVersion(
                $versionInfo->getContentInfo()->id,
                $versionInfo->versionNo
            );
            $this->repository->commit();
            $this->objectStore->discard( 'content', $versionInfo->getContentInfo()->id );
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
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo[] an array of {@link \eZ\Publish\API\Repository\Values\Content\VersionInfo} sorted by creation date
     */
    public function loadVersions( APIContentInfo $contentInfo )
    {
        $spiVersionInfoList = $this->persistenceHandler->contentHandler()->listVersions( $contentInfo->id );

        $versions = array();
        foreach ( $spiVersionInfoList as $spiVersionInfo )
        {
            $versions[] = $this->buildVersionInfoDomainObject( $spiVersionInfo );
        }

        usort(
            $versions,
            function( $a, $b )
            {
                if ( $a->creationDate->getTimestamp() === $b->creationDate->getTimestamp() ) return 0;
                return ( $a->creationDate->getTimestamp() < $b->creationDate->getTimestamp() ) ? -1 : 1;
            }
        );

        return $versions;
    }

    /**
     * copies the content to a new location. If no version is given,
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
    public function copyContent( APIContentInfo $contentInfo, LocationCreateStruct $destinationLocationCreateStruct, APIVersionInfo $versionInfo = null)
    {
        if ( $destinationLocationCreateStruct->remoteId !== null )
        {
            try
            {
                $existingLocation = $this->repository->getLocationService()->loadLocationByRemoteId(
                    $destinationLocationCreateStruct->remoteId
                );
                if ( $existingLocation !== null )
                {
                    throw new InvalidArgumentException(
                        "\$destinationLocationCreateStruct",
                        "Location with remoteId '{$destinationLocationCreateStruct->remoteId}' exists"
                    );
                }
            }
            catch ( APINotFoundException $e )
            {
                // Do nothing
            }
        }
        else
        {
            $destinationLocationCreateStruct->remoteId = md5( uniqid( get_class( $this ), true ) );
        }

        $this->repository->beginTransaction();
        try
        {
            $spiContent = $this->persistenceHandler->contentHandler()->copy(
                $contentInfo->id,
                $versionInfo ? $versionInfo->versionNo : null
            );

            $content = $this->internalPublishVersion(
                $this->buildVersionInfoDomainObject( $spiContent->versionInfo, $spiContent ),
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
     * load all outgoing relations for the given version
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to read this version
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Relation[] an array of {@link Relation}
     */
    public function loadRelations( APIVersionInfo $versionInfo )
    {
        $contentInfo = $versionInfo->getContentInfo();

        $spiRelations = $this->persistenceHandler->contentHandler()->loadRelations(
            $contentInfo->id,
            $versionInfo->versionNo
        );

        $returnArray = array();
        foreach ( $spiRelations as $spiRelation )
        {
            $returnArray[] = $this->buildRelationDomainObject(
                $spiRelation,
                $contentInfo
            );
        }

        return $returnArray;
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
     * @return \eZ\Publish\API\Repository\Values\Content\Relation[] an array of {@link Relation}
     */
    public function loadReverseRelations( APIContentInfo $contentInfo )
    {
        $spiRelations = $this->persistenceHandler->contentHandler()->loadReverseRelations(
            $contentInfo->id
        );

        $returnArray = array();
        foreach ( $spiRelations as $spiRelation )
        {
            $returnArray[] = $this->buildRelationDomainObject(
                $spiRelation,
                null,
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
    public function addRelation( APIVersionInfo $sourceVersion, APIContentInfo $destinationContent )
    {
        if ( $sourceVersion->status !== APIVersionInfo::STATUS_DRAFT )
        {
            throw new BadStateException(
                "\$sourceVersion",
                "Relations of type common can only be added to versions of status draft"
            );
        }

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
            $this->objectStore->discard( 'content', $sourceContentInfo->id );
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }

        return $this->buildRelationDomainObject( $spiRelation, $sourceContentInfo, $destinationContent );
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
    public function deleteRelation( APIVersionInfo $sourceVersion, APIContentInfo $destinationContent )
    {
        if ( $sourceVersion->status !== APIVersionInfo::STATUS_DRAFT )
        {
            throw new BadStateException(
                "\$sourceVersion",
                "Relations of type common can only be removed from versions of status draft"
            );
        }

        $spiRelations = $this->persistenceHandler->contentHandler()->loadRelations(
            $sourceVersion->getContentInfo()->id,
            $sourceVersion->versionNo,
            APIRelation::COMMON
        );

        if ( count( $spiRelations ) == 0 )
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
                    $this->persistenceHandler->contentHandler()->removeRelation( $spiRelation->id );
                }
            }
            $this->repository->commit();
            $this->objectStore->discard( 'content', $sourceVersion->getContentInfo()->id );
        }
        catch ( Exception $e )
        {
            $this->repository->rollback();
            throw $e;
        }
    }

    /**
     * add translation information to the content object
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
     * @todo TBD - filter by source version destination version and languages
     *
     * @return \eZ\Publish\API\Repository\Values\Content\TranslationInfo[] an array of {@link TranslationInfo}
     *
     * @since 5.0
     */
    public function loadTranslationInfos( APIContentInfo $contentInfo, array $filter = array() )
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

    /**
     * Builds a Content domain object from value object returned from persistence
     *
     * @TODO: Made public, since the search service also needs access to this
     * method. Should be refactored into its own class together with the other
     * build* methods.
     *
     * @param \eZ\Publish\SPI\Persistence\Content $spiContent
     *
     * @return \eZ\Publish\Core\Repository\Values\Content\Content
     */
    public function buildContentDomainObject( SPIContent $spiContent )
    {
        $versionInfo = $this->buildVersionInfoDomainObject( $spiContent->versionInfo, $spiContent );
        return new Content(
            array(
                "internalFields" => $this->buildDomainFields( $spiContent->fields ),
                "versionInfo" => $versionInfo,
                "relations" => $this->loadRelations( $versionInfo ),
            )
        );
    }

    /**
     * Returns an array of domain fields created from given array of SPI fields
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field[] $spiFields
     *
     * @return array
     */
    protected function buildDomainFields( array $spiFields )
    {
        $fields = array();

        foreach ( $spiFields as $spiField )
        {
            $fields[] = new Field(
                array(
                    "id" => $spiField->id,
                    "value" => $this->repository->getFieldTypeService()->buildFieldType(
                        $spiField->type
                    )->fromPersistenceValue( $spiField->value ),
                    "languageCode" => $spiField->languageCode,
                    "fieldDefIdentifier" => $this->persistenceHandler->contentTypeHandler()->getFieldDefinition(
                        $spiField->fieldDefinitionId,
                        ContentType::STATUS_DEFINED
                    )->identifier
                )
            );
        }

        return $fields;
    }

    /**
     * Builds a ContentInfo domain object from value object returned from persistence
     *
     * @TODO: Made public, since the search service also needs access to this
     * method. Should be refactored into its own class together with the other
     * build* methods.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ContentInfo $spiContentInfo
     * @param \eZ\Publish\SPI\Persistence\Content|null $spiContent
     *
     * @return \eZ\Publish\Core\Repository\Values\Content\ContentInfo
     */
    public function buildContentInfoDomainObject( SPIContentInfo $spiContentInfo, SPIContent $spiContent = null )
    {

        if ( $spiContent === null )
        {
            $spiContent = $this->persistenceHandler->contentHandler()->load(
                $spiContentInfo->id,
                $spiContentInfo->currentVersionNo
            );
        }
        // @todo: $mainLocationId should have been removed through SPI refactoring?
        $mainLocationId = null;
        foreach ( $spiContent->locations as $spiLocation )
        {
            if ( $spiLocation->mainLocationId === $spiLocation->id )
            {
                $mainLocationId = $spiLocation->mainLocationId;
                break;
            }
        }

        return new ContentInfo(
            array(
                "id" => $spiContentInfo->id,
                "name" => $spiContentInfo->name,
                "sectionId" => $spiContentInfo->sectionId,
                "currentVersionNo" => $spiContentInfo->currentVersionNo,
                "published" => $spiContentInfo->isPublished,
                "ownerId" => $spiContentInfo->ownerId,
                "modificationDate" => $spiContentInfo->modificationDate == 0
                    ? null
                    : $this->getDateTime( $spiContentInfo->modificationDate ),
                "publishedDate" => $spiContentInfo->publicationDate == 0
                    ? null
                    : $this->getDateTime( $spiContentInfo->publicationDate ),
                "alwaysAvailable" => $spiContentInfo->alwaysAvailable,
                "remoteId" => $spiContentInfo->remoteId,
                "mainLanguageCode" => $spiContentInfo->mainLanguageCode,
                "mainLocationId" => $mainLocationId,
                "contentType" => $this->repository->getContentTypeService()->loadContentType(
                    $spiContentInfo->contentTypeId
                )
            )
        );
    }

    /**
     * Builds a VersionInfo domain object from value object returned from persistence
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $persistenceVersionInfo
     * @param \eZ\Publish\SPI\Persistence\Content|null $spiContent
     *
     * @return VersionInfo
     */
    protected function buildVersionInfoDomainObject( SPIVersionInfo $persistenceVersionInfo, SPIContent $spiContent = null )
    {
        $languageCodes = array();
        foreach ( $persistenceVersionInfo->languageIds as $languageId )
        {
            $languageCodes[] = $this->persistenceHandler->contentLanguageHandler()->load(
                $languageId
            )->languageCode;
        }

        return new VersionInfo(
            array(
                "id" => $persistenceVersionInfo->id,
                "versionNo" => $persistenceVersionInfo->versionNo,
                "modificationDate" => $this->getDateTime( $persistenceVersionInfo->modificationDate ),
                "creatorId" => $persistenceVersionInfo->creatorId,
                "creationDate" => $this->getDateTime( $persistenceVersionInfo->creationDate ),
                "status" => $this->getDomainVersionStatus( $persistenceVersionInfo->status ),
                "initialLanguageCode" => $persistenceVersionInfo->initialLanguageCode,
                "languageCodes" => $languageCodes,
                "names" => $persistenceVersionInfo->names,
                "contentInfo" => ( $spiContent !== null ?
                    $this->buildContentInfoDomainObject( $spiContent->contentInfo, $spiContent ) :
                    $this->loadContentInfo( $persistenceVersionInfo->contentId ) )
            )
        );
    }

    /**
     * @param int $spiStatus
     *
     * @return int|null
     */
    protected function getDomainVersionStatus( $spiStatus )
    {
        $status = null;
        switch ( $spiStatus )
        {
            case SPIVersionInfo::STATUS_DRAFT:
                $status = VersionInfo::STATUS_DRAFT;
                break;
            case SPIVersionInfo::STATUS_PUBLISHED:
                $status = VersionInfo::STATUS_PUBLISHED;
                break;
            case SPIVersionInfo::STATUS_ARCHIVED:
                $status = VersionInfo::STATUS_ARCHIVED;
                break;
        }
        return $status;
    }

    /**
     * @param int|null $timestamp
     *
     * @return \DateTime|null
     */
    protected function getDateTime( $timestamp )
    {
        $dateTime = new DateTime();
        $dateTime->setTimestamp( $timestamp );
        return $dateTime;
    }

    /**
     * Builds API Relation object from provided SPI Relation object
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Relation $spiRelation
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo|null $sourceContentInfo
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo|null $destinationContentInfo
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Relation
     */
    protected function buildRelationDomainObject( SPIRelation $spiRelation, APIContentInfo $sourceContentInfo = null, APIContentInfo $destinationContentInfo = null )
    {
        if ( $sourceContentInfo === null )
            $sourceContentInfo = $this->loadContentInfo( $spiRelation->sourceContentId );

        if ( $destinationContentInfo === null )
            $destinationContentInfo = $this->loadContentInfo( $spiRelation->destinationContentId );

        $sourceFieldDefinitionIdentifier = null;
        if ( $spiRelation->type !== APIRelation::COMMON )
        {
            $sourceFieldDefinitionIdentifier = $sourceContentInfo->getContentType()->getFieldDefinitionById(
                $spiRelation->sourceFieldDefinitionId
            );
        }

        return new Relation(
            array(
                "id" => $spiRelation->id,
                "sourceFieldDefinitionIdentifier" => $sourceFieldDefinitionIdentifier,
                "type" => $spiRelation->type,
                "sourceContentInfo" => $sourceContentInfo,
                "destinationContentInfo" => $destinationContentInfo
            )
        );
    }
}
