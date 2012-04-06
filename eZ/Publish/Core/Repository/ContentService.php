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
    eZ\Publish\SPI\Persistence\Handler;

use eZ\Publish\API\Repository\Values\Content\Content as APIContent;
use eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct as APIContentUpdateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\TranslationInfo;
use eZ\Publish\API\Repository\Values\Content\TranslationValues as APITranslationValues;
use eZ\Publish\API\Repository\Values\Content\ContentCreateStruct as APIContentCreateStruct;

use eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct;

use eZ\Publish\API\Repository\Values\Content\VersionInfo as APIVersionInfo;
use eZ\Publish\API\Repository\Values\Content\ContentInfo as APIContentInfo;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;

use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;

use eZ\Publish\API\Repository\Values\Content\SearchResult;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\ContentId as CriterionContentId;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\RemoteId as CriterionRemoteId;

use eZ\Publish\API\Repository\Exceptions\NotFoundException as APINotFoundException;

use eZ\Publish\Core\Repository\Values\Content\Content;
use eZ\Publish\Core\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\Core\Repository\Values\Content\ContentUpdateStruct;
use eZ\Publish\Core\Repository\Values\Content\TranslationValues;

use eZ\Publish\Core\Repository\FieldType\FieldType;
use eZ\Publish\Core\Repository\FieldType\Value;

use eZ\Publish\SPI\Persistence\Content\VersionInfo as SPIVersionInfo;
use eZ\Publish\SPI\Persistence\Content\ContentInfo as SPIContentInfo;
use eZ\Publish\SPI\Persistence\Content\Version as SPIVersion;
use eZ\Publish\SPI\Persistence\Content\RestrictedVersion as SPIRestrictedVersion;
use eZ\Publish\SPI\Persistence\ValueObject as SPIValueObject;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentValue;
use eZ\Publish\Core\Base\Exceptions\BadStateException;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\ContentValidationException;
use eZ\Publish\Core\Base\Exceptions\ContentFieldValidationException;

use eZ\Publish\SPI\Persistence\Content as SPIContent;
use eZ\Publish\SPI\Persistence\Content\MetadataUpdateStruct as SPIMetadataUpdateStruct;
use eZ\Publish\SPI\Persistence\Content\CreateStruct as SPIContentCreateStruct;
use eZ\Publish\SPI\Persistence\Content\UpdateStruct as SPIContentUpdateStruct;
use eZ\Publish\SPI\Persistence\Content\Field as SPIField;
use eZ\Publish\SPI\Persistence\Content\FieldValue as SPIFieldValue;
use eZ\Publish\SPI\Persistence\Content\Location\CreateStruct as SPILocationCreateStruct;

use eZ\Publish\Core\Repository\Values\Content\Relation;
use eZ\Publish\API\Repository\Values\Content\Relation as APIRelation;
use eZ\Publish\SPI\Persistence\Content\Relation as SPIRelation;
use eZ\Publish\SPI\Persistence\Content\Relation\CreateStruct as SPIRelationCreateStruct;

/**
* This class provides service methods for managing content
 *
 * @example Examples/content.php
 *
 * @package eZ\Publish\API\Repository
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
     * @var array
     */
    protected $settings;

    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     * @param \eZ\Publish\SPI\Persistence\Handler $handler
     * @param array $settings
     */
    public function __construct( RepositoryInterface $repository, Handler $handler, array $settings = array() )
    {
        $this->repository = $repository;
        $this->persistenceHandler = $handler;
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
        try
        {
            $spiContentInfo = $this->persistenceHandler->contentHandler()
                ->loadContentInfo( $contentId );
        }
        catch ( APINotFoundException $e )
        {
            throw new NotFoundException(
                "Content",
                $contentId,
                $e
            );
        }

        return $this->buildContentInfoDomainObject( $spiContentInfo );
    }

    /**
     * Loads a content info object for the given remoteId.
     *
     * To load fields use loadContent
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to create the content in the given location
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException - if the content with the given remote id does not exist
     *
     * @param string $remoteId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     * @todo implement contentHandler::loadContentInfoByRemoteId?
     */
    public function loadContentInfoByRemoteId( $remoteId )
    {
        try
        {
            $spiContent = $this->persistenceHandler->searchHandler()->findSingle(
                new CriterionRemoteId( $remoteId )
            );
        }
        catch ( APINotFoundException $e )
        {
            throw new NotFoundException(
                "Content",
                $remoteId,
                $e
            );
        }

        return $this->buildContentInfoDomainObject( $spiContent->contentInfo );
    }

    /**
     * Builds a ContentInfo domain object from value object returned from persistence
     *
     * @param \eZ\Publish\SPI\Persistence\Content\ContentInfo $spiContentInfo
     *
     * @return \eZ\Publish\Core\Repository\Values\Content\ContentInfo
     */
    protected function buildContentInfoDomainObject( SPIContentInfo $spiContentInfo )
    {
        $modificationDate = new \DateTime( "@{$spiContentInfo->modificationDate}" );
        $publishedDate = new \DateTime( "@{$spiContentInfo->publicationDate}" );

        // @todo: $mainLocationId should have been removed through SPI refactoring?
        $spiContent = $this->persistenceHandler->contentHandler()->load(
            $spiContentInfo->contentId,
            $spiContentInfo->currentVersionNo
        );
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
                "repository"       => $this->repository,
                "contentTypeId"    => $spiContentInfo->contentTypeId,

                "contentId"        => $spiContentInfo->contentId,
                "name"             => $spiContentInfo->name,
                "sectionId"        => $spiContentInfo->sectionId,
                "currentVersionNo" => $spiContentInfo->currentVersionNo,
                "published"        => $spiContentInfo->isPublished,
                "ownerId"          => $spiContentInfo->ownerId,
                "modificationDate" => $modificationDate,
                "publishedDate"    => $publishedDate,
                "alwaysAvailable"  => $spiContentInfo->isAlwaysAvailable,
                "remoteId"         => $spiContentInfo->remoteId,
                "mainLanguageCode" => $spiContentInfo->mainLanguageCode,
                "mainLocationId"   => $mainLocationId
            )
        );
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
        return $this->loadVersionInfoById( $contentInfo->contentId, $versionNo );
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
                $versionNo = $this->persistenceHandler->contentHandler()
                    ->loadContentInfo( $contentId )->currentVersionNo;

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
        if ( $versionNo === null ) $versionNo = $contentInfo->currentVersionNo;

        $spiContent = $this->persistenceHandler->contentHandler()->load(
            $contentInfo->contentId,
            $versionNo,
            $languages
        );

        return $this->buildContentDomainObject( $spiContent );
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
        $spiContent = $this->persistenceHandler->contentHandler()->load(
            $versionInfo->getContentInfo()->contentId,
            $versionInfo->versionNo,
            $languages
        );

        return $this->buildContentDomainObject( $spiContent );
    }

    /**
     * loads content in a version of the given content object.
     *
     * If no version number is given, the method returns the current version
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException - if the content or version with the given id does not exist
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to load this version
     *
     * @param int $contentId
     * @param array $languages A language filter for fields. If not given all languages are returned
     * @param int $versionNo the version number. If not given the current version is returned.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function loadContent( $contentId, array $languages = null, $versionNo = null )
    {
        try
        {
            if ( $versionNo === null )
                $versionNo = $this->persistenceHandler->contentHandler()
                    ->loadContentInfo( $contentId )->currentVersionNo;

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
                $contentId,
                $e
            );
        }

        return $this->buildContentDomainObject( $spiContent );
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
        try
        {
            $spiContent = $this->persistenceHandler->searchHandler()
                ->findSingle( new CriterionRemoteId( $remoteId ) );
        }
        catch ( APINotFoundException $e )
        {
            throw new NotFoundException(
                "Content",
                $remoteId,
                $e
            );
        }

        if ( $versionNo === null )
        {
            $versionNo = $spiContent->contentInfo->currentVersionNo;
        }

        $spiContent = $this->persistenceHandler->contentHandler()->load(
            $spiContent->contentInfo->id,
            $versionNo,
            $languages
        );

        return $this->buildContentDomainObject( $spiContent );
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
     *                                                            or (4.x) there is no location provided
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
        /*if ( count( $locationCreateStructs ) === 0 )
        {
            throw new InvalidArgumentException(
                '$locationCreateStructs',
                "array of locations is empty"
            );
        }*/

        if ( $contentCreateStruct->remoteId !== null )
        {
            try
            {
                $this->persistenceHandler->searchHandler()->findSingle(
                    new CriterionRemoteId(
                        $contentCreateStruct->remoteId
                    )
                );

                throw new InvalidArgumentException(
                    "\$contentCreateStruct->remoteId",
                    "content with given remoteId already exists"
                );
            }
            catch ( APINotFoundException $e ) {}

            $remoteId = $contentCreateStruct->remoteId;
        }
        else $remoteId = md5( uniqid( get_class( $contentCreateStruct ), true ) );

        /*if ( $contentCreateStruct->ownerId === null )
            $contentCreateStruct->ownerId = $this->repository->getCurrentUser()->id;
        else
        {
            // @todo: check for user permissions
        }*/

        $fields = array();
        $languageCodes = array( $contentCreateStruct->mainLanguageCode );

        // Map fields to array $fields[$field->fieldDefIdentifier][$field->languageCode]
        // Check for inconsistencies along the way and throw exceptions where needed
        foreach ( $contentCreateStruct->fields as $field )
        {
            $fieldDefinition = $contentCreateStruct->contentType->getFieldDefinition( $field->fieldDefIdentifier );

            if ( null === $fieldDefinition )
                throw new ContentValidationException(
                    "Field definition '{$field->fieldDefIdentifier}' does not exist in given ContentType"
                );

            if ( $fieldDefinition->isTranslatable )
            {
                if ( isset( $fields[$field->fieldDefIdentifier][$field->languageCode] ) )
                    throw new ContentValidationException(
                        "More than one field is set for translatable field definition '{$field->fieldDefIdentifier}' on language '{$field->languageCode}'"
                    );

                $fields[$field->fieldDefIdentifier][$field->languageCode] = $field;
            }
            else
            {
                if ( isset( $fields[$field->fieldDefIdentifier][$contentCreateStruct->mainLanguageCode] ) )
                    throw new ContentValidationException(
                        "More than one field is set for untranslatable field definition '{$field->fieldDefIdentifier}'"
                    );

                if (  $field->languageCode != $contentCreateStruct->mainLanguageCode )
                    throw new ContentValidationException(
                        "A translation is set for untranslatable field definition '{$field->fieldDefIdentifier}'"
                    );

                $fields[$field->fieldDefIdentifier][$contentCreateStruct->mainLanguageCode] = $field;
            }

            $languageCodes[] = $field->languageCode;
        }

        $languageCodes = array_unique( $languageCodes );

        $spiFields = array();
        $failedValidators = array();
        foreach ( $contentCreateStruct->contentType->getFieldDefinitions() as $fieldDefinition )
        {
            $fieldType = $this->repository->getContentTypeService()->buildFieldType(
                $fieldDefinition->fieldTypeIdentifier
            );

            foreach ( $languageCodes as $languageCode )
            {
                $valueLanguageCode = $fieldDefinition->isTranslatable ? $languageCode : $contentCreateStruct->mainLanguageCode;
                if ( isset( $fields[$fieldDefinition->identifier][$valueLanguageCode] ) )
                {
                    $field = $fields[$fieldDefinition->identifier][$valueLanguageCode];
                    $fieldValue = $field->value instanceof Value ?
                            $field->value :
                            $fieldType->buildValue( $field->value );
                }
                else
                {
                    $fieldValue = $fieldType->buildValue( $fieldDefinition->defaultValue );
                }

                $fieldValue = $fieldType->acceptValue( $fieldValue );

                if ( $fieldDefinition->isRequired && (string) $fieldValue === "" )
                {
                    throw new ContentFieldValidationException( '@TODO: What error code should be used?' );
                }

                $this->validateField( $fieldDefinition, $fieldType, $fieldValue, $failedValidators );
                if ( count( $failedValidators ) ) continue;

                $spiFields[] = new SPIField(
                    array(
                        "id"                => null,
                        "fieldDefinitionId" => $fieldDefinition->id,
                        "type"              => $fieldDefinition->fieldTypeIdentifier,
                        "value"             => $fieldType->toPersistenceValue( $fieldValue ),
                        "languageCode"      => $languageCode,
                        "versionNo"         => null
                    )
                );
            }
        }

        if ( count( $failedValidators ) )
        {
            throw new ContentFieldValidationException();
        }

        $spiContentCreateStruct = new SPIContentCreateStruct(
            array(
                // @todo calculate names
                "name"              => array( "eng-US" => "Some name" ),
                "typeId"            => $contentCreateStruct->contentType->id,
                "sectionId"         => $contentCreateStruct->sectionId,
                "ownerId"           => $contentCreateStruct->ownerId,
                "locations"         => $this->buildSPILocationCreateStructs( $locationCreateStructs ),
                "fields"            => $spiFields,
                "alwaysAvailable"   => $contentCreateStruct->alwaysAvailable,
                "remoteId"          => $remoteId,
                "modified"          => isset( $contentCreateStruct->modificationDate ) ?
                    $contentCreateStruct->modificationDate->getTimestamp() : time(),
                "initialLanguageId" => $this->persistenceHandler->contentLanguageHandler()
                    ->loadByLanguageCode( $contentCreateStruct->mainLanguageCode )->id
            )
        );

        return $this->buildContentDomainObject(
            $this->persistenceHandler->contentHandler()->create( $spiContentCreateStruct )
        );
    }

    /**
     * Creates an array of SPI location create structs from given array of API location create structs
     *
     * @param \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct[] $locationCreateStructs
     *
     * @return \eZ\Publish\SPI\Persistence\Content\Location\CreateStruct[]
     */
    protected function buildSPILocationCreateStructs( array $locationCreateStructs )
    {
        $spiLocationCreateStructs = array();

        foreach ( $locationCreateStructs as $index => $locationCreateStruct )
        {
            $parentLocation = $this->repository->getLocationService()->loadLocation( $locationCreateStruct->parentLocationId );

            if ( $locationCreateStruct->priority !== null && !is_numeric( $locationCreateStruct->priority ) )
                throw new InvalidArgumentValue( "priority", $locationCreateStruct->priority, "LocationCreateStruct" );

            if ( !is_bool( $locationCreateStruct->hidden ) )
                throw new InvalidArgumentValue( "hidden", $locationCreateStruct->hidden, "LocationCreateStruct" );

            if ( $locationCreateStruct->remoteId !== null && ( !is_string( $locationCreateStruct->remoteId ) || empty( $locationCreateStruct->remoteId ) ) )
                throw new InvalidArgumentValue( "remoteId", $locationCreateStruct->remoteId, "LocationCreateStruct" );

            if ( $locationCreateStruct->sortField !== null && !is_numeric( $locationCreateStruct->sortField ) )
                throw new InvalidArgumentValue( "sortField", $locationCreateStruct->sortField, "LocationCreateStruct" );

            if ( $locationCreateStruct->sortOrder !== null && !is_numeric( $locationCreateStruct->sortOrder ) )
                throw new InvalidArgumentValue( "sortOrder", $locationCreateStruct->sortOrder, "LocationCreateStruct" );

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
                        "location with provided remote ID already exists"
                    );
                }
                catch ( APINotFoundException $e ) {}
            }

            $spiLocationCreateStructs[] = new SPILocationCreateStruct(
                array(
                    "priority"                 => $locationCreateStruct->priority,
                    "hidden"                   => $locationCreateStruct->hidden,
                    "invisible"                => ( $locationCreateStruct->hidden === true || $parentLocation->hidden || $parentLocation->invisible ),
                    "remoteId"                 => $locationCreateStruct->remoteId,
                    // contentId and contentVersion are set in ContentHandler upon draft creation
                    "contentId"                => null,
                    "contentVersion"           => null,
                    // @todo: set pathIdentificationString
                    "pathIdentificationString" => null,
                    "mainLocationId"          => ( $index === 0 ),
                    "sortField"                => $locationCreateStruct->sortField,
                    "sortOrder"                => $locationCreateStruct->sortOrder,
                    "parentId"                 => $locationCreateStruct->parentLocationId
                )
            );
        }

        return $spiLocationCreateStructs;
    }

    /**
     * @param $contentType
     */
    private function generateContentNames( $contentType )
    {
    }

    /**
     * Validates a field against validators from FieldDefinition
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition
     * @param \eZ\Publish\Core\Repository\FieldType\FieldType $fieldType
     * @param \eZ\Publish\Core\Repository\FieldType\Value $fieldValue
     * @param array $failedValidators
     */
    protected function validateField( FieldDefinition $fieldDefinition, FieldType $fieldType, Value $fieldValue, array &$failedValidators )
    {
        $validators = $fieldDefinition->getValidators();
        $allowedValidators = $fieldType->allowedValidators();

        if ( is_array( $validators ) && is_array( $allowedValidators ) )
        {
            foreach ( $validators as $validator )
            {
                foreach ( $allowedValidators as $allowedValidatorClass )
                {
                    if ( $validator instanceof $allowedValidatorClass && !$validator->validate( $fieldValue ) )
                    {
                        $failedValidators[] = $validator;
                    }
                }
            }
        }
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
    public function updateContentMetadata( APIContentInfo $contentInfo, ContentMetaDataUpdateStruct $contentMetadataUpdateStruct )
    {
        $hasPropertySet = false;
        foreach ( $contentMetadataUpdateStruct as $propertyName => $propertyValue )
        {
            if ( isset( $contentMetadataUpdateStruct->$propertyName ) )
            {
                $hasPropertySet = true;
                break;
            }
        }
        if ( !$hasPropertySet )
        {
            throw new InvalidArgumentException(
                "\$contentMetadataUpdateStruct",
                "at least one property in update struct must be set"
            );
        }

        if ( isset( $contentMetadataUpdateStruct->remoteId ) )
        {
            try
            {
                $spiContent = $this->persistenceHandler->searchHandler()->findSingle(
                    new CriterionRemoteId( $contentMetadataUpdateStruct->remoteId )
                );

                if ( $spiContent->contentInfo->contentId !== $contentInfo->contentId )
                    throw new InvalidArgumentException(
                        "\$contentMetadataUpdateStruct->remoteId",
                        "remoteId already exists"
                    );
            }
            catch ( APINotFoundException $e ) {}
        }

        $spiMetadataUpdateStruct = new SPIMetadataUpdateStruct(
            array(
                "ownerId"          => $contentMetadataUpdateStruct->ownerId,
                //@todo name property is missing in API ContentMetaDataUpdateStruct
                //"name"             => $contentMetadataUpdateStruct->name,
                "publicationDate"  => isset( $contentMetadataUpdateStruct->publishedDate ) ?
                                        $contentMetadataUpdateStruct->publishedDate->getTimestamp() : null,
                "modificationDate" => isset( $contentMetadataUpdateStruct->modificationDate ) ?
                                        $contentMetadataUpdateStruct->modificationDate->getTimestamp() : null,
                "mainLanguageId"   => isset( $contentMetadataUpdateStruct->mainLanguageCode ) ?
                                        $this->repository->getContentLanguageService()->loadLanguage(
                                            $contentMetadataUpdateStruct->mainLanguageCode
                                        )->id : null,
                "alwaysAvailable"  => $contentMetadataUpdateStruct->alwaysAvailable,
                "remoteId"         => $contentMetadataUpdateStruct->remoteId,
                //@todo mainLocationId property is missing in SPI ContentMetaDataUpdateStruct
                //"mainLocationId"   => $contentMetadataUpdateStruct->mainLocationId,
            )
        );
        $this->persistenceHandler->contentHandler()->updateMetadata(
            $contentInfo->contentId,
            $spiMetadataUpdateStruct
        );

        return $this->loadContent( $contentInfo->contentId );
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
        $this->persistenceHandler->contentHandler()->deleteContent( $contentInfo->contentId );
    }

    /**
     * creates a draft from a published or archived version.
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
        if ( $versionInfo !== null )
        {
            if ( $versionInfo->status === VersionInfo::STATUS_DRAFT )
            {
                // @TODO: throw an exception here, to be defined
                throw new BadStateException(
                    "\$versionInfo->status",
                    "draft can not be created from a draft version"
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
                "\$contentInfo->published",
                "content is not published, draft can be created only from published or archived version"
            );
        }

        $spiContent = $this->persistenceHandler->contentHandler()->createDraftFromVersion(
            $contentInfo->contentId,
            $versionNo
        );

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
            $user = $this->repository->getCurrentUser();

        /** @var $spiVersionInfoList \eZ\Publish\SPI\Persistence\Content\VersionInfo[] */
        $spiVersionInfoList = $this->persistenceHandler->contentHandler()->loadDraftsForUser( $user->id );
        $languageCodes = array();

        $versionInfoList = array();
        foreach ( $spiVersionInfoList as $spiVersionInfo )
        {
            foreach ( $spiVersionInfo->languageIds as $languageId )
            {
                $languageCodes[] = $this->persistenceHandler->contentLanguageHandler()->load( $languageId );
            }

            $versionInfoList[] = new VersionInfo(
                array(
                    "id"                  => $spiVersionInfo->id,
                    "versionNo"           => $spiVersionInfo->versionNo,
                    "modificationDate"    => new \DateTime( "@{$spiVersionInfo->modificationDate}" ),
                    "creatorId"           => $spiVersionInfo->creatorId,
                    "creationDate"        => new \DateTime( "@{$spiVersionInfo->creationDate}" ),
                    "status"              => $spiVersionInfo->status,
                    "initialLanguageCode" => $spiVersionInfo->initialLanguageCode,
                    "languageCodes"       => $languageCodes,
                    // implementation properties
                    "contentId"           => $spiVersionInfo->contentId
                )
            );
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
        if ( $versionInfo->status !== APIVersionInfo::STATUS_DRAFT )
            throw new BadStateException( "\$versionInfo", "version is not a draft" );

        $content = $this->loadContent(
            $versionInfo->id,
            null,
            $versionInfo->status
        );
        $fields = array();
        $contentType = $versionInfo->getContentInfo()->getContentType();

        foreach ( $contentUpdateStruct->fields as $field )
        {
            $fieldDefinition = $contentType->getFieldDefinition( $field->fieldDefIdentifier );
            if ( null === $fieldDefinition )
                throw new ContentFieldValidationException(
                    "Field definition '{$field->fieldDefIdentifier}' does not exist in given ContentType"
                );

            if ( $fieldDefinition->isTranslatable )
            {
                if ( empty( $field->languageCode ) )
                    throw new ContentFieldValidationException(
                        "Language code is missing on a field for translatable field definition '{$field->fieldDefIdentifier}'"
                    );

                if ( !in_array( $field->languageCode, $versionInfo->languageCodes ) )
                    throw new ContentValidationException(
                        "Content draft does not contain language with code '{$field->languageCode}'"
                    );

                if ( isset( $fields[$field->fieldDefIdentifier][$field->languageCode] ) )
                    throw new ContentValidationException(
                        "More than one field is given for translatable field definition '{$field->fieldDefIdentifier}' on language with code '{$field->languageCode}'"
                    );

                $fields[$field->fieldDefIdentifier][$field->languageCode] = $field;
            }
            else
            {
                if ( isset( $fields[$field->fieldDefIdentifier][$versionInfo->initialLanguageCode] ) )
                    throw new ContentValidationException(
                        "More than one field is given for untranslatable field definition '{$field->fieldDefIdentifier}'"
                    );

                $fields[$field->fieldDefIdentifier][$versionInfo->initialLanguageCode] = $field;
            }
        }

        $spiFields = array();
        $failedValidators = array();
        foreach ( $fields as $fieldDefIdentifier => $languageFields )
        {
            $fieldDefinition = $contentType->getFieldDefinition( $fieldDefIdentifier );
            $fieldType = $this->repository->getContentTypeService()->buildFieldType(
                $fieldDefinition->fieldTypeIdentifier
            );

            foreach ( $languageFields as $languageCode => $field )
            {
                $contentField = $content->getField( $fieldDefIdentifier, $languageCode );
                $fieldValue = $fieldType->acceptValue(
                    $field->value instanceof Value ?
                        $field->value :
                        $fieldType->buildValue( $field->value )
                );

                if ( $fieldDefinition->isRequired && empty( $fieldValue ) )
                {
                    throw new ContentValidationException( '@TODO: What error code should be used?' );
                }

                $this->validateField( $fieldDefinition, $fieldType, $fieldValue, $failedValidators );
                if ( count( $failedValidators ) ) continue;

                $spiFields[] = new SPIField(
                    array(
                        "id"                => $contentField->id,
                        "fieldDefinitionId" => $fieldDefinition->id,
                        "type"              => $fieldDefinition->fieldTypeIdentifier,
                        "value"             => $fieldType->toPersistenceValue( $fieldValue ),
                        "languageCode"      => $languageCode,
                        "versionNo"         => $versionInfo->versionNo
                    )
                );
            }
        }

        if ( count( $failedValidators ) )
        {
            throw new ContentFieldValidationException();
        }

        $modifiedTimestamp = isset( $contentUpdateStruct->modificationDate ) ? $contentUpdateStruct->modificationDate->getTimestamp() : time();
        $userId = isset( $contentUpdateStruct->userId ) ? $contentUpdateStruct->userId : $this->repository->getCurrentUser()->id;

        $spiContentUpdateStruct = new SPIContentUpdateStruct(
            array(
                "name"              => array(),
                "creatorId"         => $userId,
                "fields"            => $spiFields,
                "modificationDate"  => $modifiedTimestamp,
                "initialLanguageId" => $contentUpdateStruct->initialLanguageCode
            )
        );

        $spiContent = $this->persistenceHandler->contentHandler()->updateContent(
            $versionInfo->getContentInfo()->contentId,
            $versionInfo->versionNo,
            $spiContentUpdateStruct
        );

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
        if ( $versionInfo->status !== APIVersionInfo::STATUS_DRAFT )
            throw new BadStateException( "versionInfo", "only versions in draft status can be published" );

        $time = time();
        $metadataUpdateStruct = new SPIMetadataUpdateStruct(
            array(
                "publicationDate"  => $time,
                "modificationDate" => $time
            )
        );

        $publishedContent = $this->persistenceHandler->contentHandler()->publish(
            $versionInfo->getContentInfo()->contentId,
            $versionInfo->versionNo,
            $metadataUpdateStruct
        );

        return $this->buildContentDomainObject( $publishedContent );
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
                "\$versionInfo->status",
                "given version is published and can not be removed"
            );
        }

        $success = $this->persistenceHandler->contentHandler()->deleteVersion(
            $versionInfo->getContentInfo()->contentId,
            $versionInfo->versionNo
        );
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
        $spiRestrictedVersions = $this->persistenceHandler->contentHandler()->listVersions( $contentInfo->contentId );

        $versions = array();
        foreach ( $spiRestrictedVersions as $spiRestrictedVersion )
        {
            $versions[] = $this->buildVersionInfoDomainObject( $spiRestrictedVersion );
        }

        usort(
            $versions,
            function( $a, $b )
            {
                if ( $a->createdDate->getTimestamp() === $b->createdDate->getTimestamp() ) return 0;
                return ( $a->createdDate->getTimestamp() < $b->createdDate->getTimestamp() ) ? -1 : 1;
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
     * @TODO: contentHandler::copy is not implemented yet
     */
    public function copyContent( APIContentInfo $contentInfo, LocationCreateStruct $destinationLocationCreateStruct, APIVersionInfo $versionInfo = null)
    {
        $spiContent = $this->persistenceHandler->contentHandler()->copy(
            $contentInfo->contentId,
            $versionInfo ? $versionInfo->versionNo : false
        );

        $this->repository->getLocationService()->createLocation(
            $this->buildContentInfoDomainObject( $spiContent ),
            $destinationLocationCreateStruct
        );

        return $this->loadContent( $spiContent->contentInfo->contentId );
    }

    /**
     * finds content objects for the given query.
     *
     * @TODO define structs for the field filters
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     * @param array  $fieldFilters - a map of filters for the returned fields.
     *        Currently supported: <code>array("languages" => array(<language1>,..))</code>.
     * @param boolean $filterOnUserPermissions if true only the objects which is the user allowed to read are returned.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\SearchResult
     */
    public function findContent( Query $query, array $fieldFilters, $filterOnUserPermissions = true )
    {
        $spiSearchResult = $this->persistenceHandler->searchHandler()->find(
            $query->criterion,
            $query->offset,
            $query->limit,
            $query->sortClauses
        );

        $filteredFields = array();
        $areFieldsFiltered = false;
        foreach ( $fieldFilters as $filterName => $filterSettings )
        {
            switch ( $filterName )
            {
                case "language":
                    $areFieldsFiltered = true;
                    foreach ( $spiSearchResult->content as $spiContent )
                    {
                        if ( !isset( $filteredFields[$spiContent->contentInfo->contentId][$spiContent->versionInfo->id] ) )
                            $filteredFields[$spiContent->contentInfo->contentId][$spiContent->versionInfo->id] =
                                $spiContent->fields;

                        $filteredFields[$spiContent->contentInfo->contentId][$spiContent->versionInfo->id] =
                            $this->filterFieldsByLanguages(
                                $spiContent->contentInfo->contentTypeId,
                                $filteredFields[$spiContent->contentInfo->contentId][$spiContent->versionInfo->id],
                                $filterSettings
                            );
                    }
                    break;
            }
        }

        $contentItems = array();
        foreach ( $spiSearchResult->content as $spiContent )
        {
            $contentItems[] = $this->buildContentDomainObject(
                $spiContent,
                $areFieldsFiltered ? $filteredFields[$spiContent->contentInfo->contentId][$spiContent->versionInfo->id] : null
            );
        }

        return new SearchResult(
            array(
                'query'  =>  $query,
                'count'  =>  $spiSearchResult->count,
                'items'  =>  $contentItems
            )
        );
    }

    /**
     * Performs a query for a single content object
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the object was not found by the query or due to permissions
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the query would return more than one result
     *
     * @TODO define structs for the field filters
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     * @param array  $fieldFilters - a map of filters for the returned fields.
     *        Currently supported: <code>array("languages" => array(<language1>,..))</code>.
     * @param boolean $filterOnUserPermissions if true only the objects which is the user allowed to read are returned.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function findSingle( Query $query, array $fieldFilters, $filterOnUserPermissions = true )
    {
        $searchResult = $this->findContent( $query, $fieldFilters, $filterOnUserPermissions );

        if ( $searchResult->count > 1 )
        {
            throw new InvalidArgumentException( "\$query", "Search with given \$query returned more than one result" );
        }

        return reset( $searchResult->items );
    }

    /**
     * Returns a filtered array of given fields when the given <b>$languages</b>
     * is not <b>NULL</b> and not empty.
     *
     * @param int $contentTypeId
     * @param \eZ\Publish\SPI\Persistence\Content\Field[] $spiFields
     * @param array $languageCodes
     *
     * @return array
     */
    private function filterFieldsByLanguages( $contentTypeId, array $spiFields, array $languageCodes = null )
    {
        if ( null === $languageCodes || 0 === count( $languageCodes ) )
        {
            return $spiFields;
        }

        $contentType = $this->repository->getContentTypeService()->loadContentType( $contentTypeId );

        $filteredFields = array();
        foreach ( $spiFields as $field )
        {
            if ( false === $contentType->getFieldDefinition( $field->fieldDefinitionId )->isTranslatable )
            {
                $filteredFields[] = $field;
            }
            else if ( in_array( $field->languageCode, $languageCodes ) )
            {
                $filteredFields[] = $field;
            }
        }

        return $filteredFields;
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
            $contentInfo->contentId,
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
     * The relations come only
     * from published versions of the source content objects
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
            $contentInfo->contentId
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
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to edit this version
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException if the version is not a draft
     *
     * The source of the relation is the content and version
     * referenced by $versionInfo.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $sourceVersion
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $destinationContent the destination of the relation
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Relation the newly created relation
     */
    public function addRelation( APIVersionInfo $sourceVersion, APIContentInfo $destinationContent )
    {
        if ( $sourceVersion->status !== APIVersionInfo::STATUS_DRAFT )
            throw new BadStateException( "sourceVersion", "relations of type common can only be added to versions of status draft" );

        $sourceContentInfo = $sourceVersion->getContentInfo();

        $spiRelation = $this->persistenceHandler->contentHandler()->addRelation(
            new SPIRelationCreateStruct(
                array(
                    'sourceContentId'         => $sourceContentInfo->contentId,
                    'sourceContentVersionNo'  => $sourceVersion->versionNo,
                    'sourceFieldDefinitionId' => null,
                    'destinationContentId'    => $destinationContent->contentId,
                    'type'                    => APIRelation::COMMON
                )
            )
        );

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
    public function deleteRelation( APIVersionInfo $sourceVersion, APIContentInfo $destinationContent)
    {
        if ( $sourceVersion->status !== APIVersionInfo::STATUS_DRAFT )
            throw new BadStateException( "sourceVersion", "relations of type common can only be removed from versions of status draft" );

        $spiRelations = $this->persistenceHandler->contentHandler()->loadRelations(
            $sourceVersion->getContentInfo()->contentId,
            $sourceVersion->versionNo,
            APIRelation::COMMON
        );

        if ( count( $spiRelations ) == 0 )
            throw new InvalidArgumentException( "sourceVersion", "there are no relations of type COMMON for the given destination" );

        // there should be only one relation of type COMMON for each destination,
        // but in case there were ever more then one, we will remove them all
        // @todo: alternatively, throw BadStateException?
        foreach ( $spiRelations as $spiRelation )
        {
            $this->persistenceHandler->contentHandler()->removeRelation( $spiRelation->id );
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
                "contentType"      => $contentType,
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
     * Instantiates a FieldType\Value object
     *
     * Instantiates a FieldType\Value object by using FieldType\Type->buildValue().
     *
     * @todo Add to API or remove!
     * @uses \eZ\Publish\Core\Repository\ContentTypeService::buildFieldType
     * @param string $type
     * @param mixed $plainValue
     * @return \eZ\Publish\Core\Repository\FieldType\Value
     */
    public function newFieldTypeValue( $type, $plainValue )
    {
        return $this->repository->getContentTypeService()->buildFieldType( $type )->buildValue( $plainValue );
    }

    /**
     * Builds a Content domain object from value object returned from persistence
     *
     * @param \eZ\Publish\SPI\Persistence\Content $spiContent
     * @param array $spiFields
     *
     * @return \eZ\Publish\Core\Repository\Values\Content\Content
     */
    protected function buildContentDomainObject( SPIContent $spiContent, array $spiFields = null )
    {
        $fields = $this->buildDomainFields(
            null === $spiFields ? $spiContent->fields : $spiFields
        );

        return new Content(
            array(
                "repository"               => $this->repository,
                "contentId"                => $spiContent->contentInfo->contentId,
                "versionNo"                => $spiContent->versionInfo->versionNo,
                "contentTypeId"            => $spiContent->contentInfo->contentTypeId,
                "internalFields"           => $fields,
                // @TODO: implement loadRelations()
                //"relations"                => $this->loadRelations( $versionInfo )
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
                    "id"                 => $spiField->id,
                    "value"              => $spiField->value->data,
                    "languageCode"       => $spiField->languageCode,
                    "fieldDefIdentifier" => $this->persistenceHandler->contentTypeHandler()
                        ->getFieldDefinition(
                            $spiField->fieldDefinitionId,
                            ContentType::STATUS_DEFINED
                        )->identifier
                )
            );
        }

        return $fields;
    }

    /**
     * Builds a VersionInfo domain object from value object returned from persistence
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $persistenceVersionInfo
     *
     * @return VersionInfo
     */
    protected function buildVersionInfoDomainObject( SPIVersionInfo $persistenceVersionInfo )
    {
        $modifiedDate = new \DateTime( "@{$persistenceVersionInfo->modificationDate}" );
        $createdDate = new \DateTime( "@{$persistenceVersionInfo->creationDate}" );

        $languageCodes = array();
        foreach ( $persistenceVersionInfo->languageIds as $languageId )
        {
            $languageCodes[] = $this->persistenceHandler->contentLanguageHandler()->load(
                $languageId
            )->languageCode;
        }

        return new VersionInfo(
            array(
                "repository"          => $this->repository,
                "contentId"           => $persistenceVersionInfo->contentId,
                "id"                  => $persistenceVersionInfo->id,
                "versionNo"           => $persistenceVersionInfo->versionNo,
                "modificationDate"    => $modifiedDate,
                "creatorId"           => $persistenceVersionInfo->creatorId,
                "creationDate"        => $createdDate,
                "status"              => $persistenceVersionInfo->status,
                "initialLanguageCode" => $persistenceVersionInfo->initialLanguageCode,
                "languageCodes"       => $languageCodes
            )
        );
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
                'id'                              => $spiRelation->id,
                'sourceFieldDefinitionIdentifier' => $sourceFieldDefinitionIdentifier,
                'type'                            => $spiRelation->type,
                'sourceContentInfo'               => $sourceContentInfo,
                'destinationContentInfo'          => $destinationContentInfo
            )
        );
    }
}
