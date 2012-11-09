<?php
/**
 * File containing the ContentServiceStub class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\Stubs;

use \eZ\Publish\API\Repository\ContentService;
use \eZ\Publish\API\Repository\Values\Content\Field;
use \eZ\Publish\API\Repository\Values\Content\Content;
use \eZ\Publish\API\Repository\Values\Content\ContentInfo;
use \eZ\Publish\API\Repository\Values\Content\ContentCreateStruct;
use \eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct;
use \eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct;
use \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use \eZ\Publish\API\Repository\Values\Content\Relation;
use \eZ\Publish\API\Repository\Values\Content\TranslationInfo;
use \eZ\Publish\API\Repository\Values\Content\TranslationValues;
use \eZ\Publish\API\Repository\Values\Content\VersionInfo;
use \eZ\Publish\API\Repository\Values\ContentType\ContentType;
use \eZ\Publish\API\Repository\Values\User\User;

use \eZ\Publish\API\Repository\Tests\Stubs\Exceptions\BadStateExceptionStub;
use \eZ\Publish\API\Repository\Tests\Stubs\Exceptions\ContentValidationExceptionStub;
use \eZ\Publish\API\Repository\Tests\Stubs\Exceptions\InvalidArgumentExceptionStub;
use \eZ\Publish\API\Repository\Tests\Stubs\Exceptions\NotFoundExceptionStub;
use \eZ\Publish\API\Repository\Tests\Stubs\Exceptions\UnauthorizedExceptionStub;
use \eZ\Publish\API\Repository\Tests\Stubs\Values\Content\ContentStub;
use \eZ\Publish\API\Repository\Tests\Stubs\Values\Content\ContentInfoStub;
use \eZ\Publish\API\Repository\Tests\Stubs\Values\Content\ContentCreateStructStub;
use \eZ\Publish\API\Repository\Tests\Stubs\Values\Content\ContentUpdateStructStub;
use \eZ\Publish\API\Repository\Tests\Stubs\Values\Content\RelationStub;
use \eZ\Publish\API\Repository\Tests\Stubs\Values\Content\VersionInfoStub;
use \eZ\Publish\API\Repository\Tests\Stubs\Values\Content\FieldStub;

/**
 * @example Examples/contenttype.php
 */
class ContentServiceStub implements ContentService
{
    /**
     * @var \eZ\Publish\API\Repository\Tests\Stubs\RepositoryStub
     */
    private $repository;

    /**
     * Exmulation of external storages in the in-memory stub.
     *
     * @var \eZ\Publish\API\Repository\Tests\Stubs\PseudoExternalStorage
     */
    private $pseudoExternalStorage;

    /**
     * @var integer
     */
    private $contentNextId = 0;

    /**
     * @var \eZ\Publish\API\Repository\Tests\Stubs\Values\Content\ContentStub[]
     */
    private $content = array();

    /**
     * @var \eZ\Publish\API\Repository\Tests\Stubs\Values\Content\ContentInfoStub[]
     */
    private $contentInfo = array();

    /**
     * @var \eZ\Publish\API\Repository\Tests\Stubs\Values\Content\VersionInfoStub[]
     */
    private $versionInfo = array();

    /**
     * @var integer
     */
    private $versionNextId = 0;

    /**
     * @var integer
     */
    private $fieldNextId = 0;

    /**
     * Locations to be created on first publish of an object
     *
     * @var LocationCreateStruct[][]
     */
    private $locationsOnPublish = array();

    /**
     * Instantiates a new content service stub.
     *
     * @param \eZ\Publish\API\Repository\Tests\Stubs\RepositoryStub $repository
     */
    public function __construct( RepositoryStub $repository )
    {
        $this->repository   = $repository;
        $this->pseudoExternalStorage = new PseudoExternalStorage\StorageDispatcher(
            array(
                'ezuser' => new PseudoExternalStorage\User( $repository ),
            )
        );
        $this->initFromFixture();
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
        if ( isset( $this->contentInfo[$contentId] ) )
        {
            if ( false === $this->repository->canUser( 'content', 'read', $this->contentInfo[$contentId] ) )
            {
                throw new UnauthorizedExceptionStub( 'What error code should be used?' );
            }
            return $this->contentInfo[$contentId];
        }
        throw new NotFoundExceptionStub( 'What error code should be used?' );
    }

    /**
     * Loads a content info object for the given remoteId.
     *
     * To load fields use loadContent
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowd to create the content in the given location
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException - if the content with the given remote id does not exist
     *
     * @param string $remoteId
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    public function loadContentInfoByRemoteId( $remoteId )
    {
        foreach ( $this->contentInfo as $contentInfo )
        {
            if ( $remoteId !== $contentInfo->remoteId )
            {
                continue;
            }
            if ( false === $this->repository->canUser( 'content', 'read', $contentInfo ) )
            {
                throw new UnauthorizedExceptionStub( 'What error code should be used?' );
            }
            return $contentInfo;
        }
        throw new NotFoundExceptionStub( 'What error code should be used?' );
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
    public function loadVersionInfo( ContentInfo $contentInfo, $versionNo = null )
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
        $versions = array();
        foreach ( $this->versionInfo as $index => $versionInfo )
        {
            if ( $versionInfo->contentId !== $contentId )
            {
                continue;
            }

            if ( false === $this->repository->canUser( 'content', 'read', $versionInfo ) )
            {
                throw new UnauthorizedExceptionStub( 'What error code should be used?' );
            }

            if ( $versionInfo->versionNo === $versionNo )
            {
                return $versionInfo;
            }
            $versions[$versionInfo->status] = $versionInfo;
        }

        if ( null === $versionNo && isset( $versions[VersionInfo::STATUS_PUBLISHED] ) )
        {
            return $versions[VersionInfo::STATUS_PUBLISHED];
        }
        else if ( null === $versionNo && isset( $versions[VersionInfo::STATUS_DRAFT] ) )
        {
            return $versions[VersionInfo::STATUS_DRAFT];
        }

        throw new NotFoundExceptionStub( 'What error code should be used?' );
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
    public function loadContentByContentInfo( ContentInfo $contentInfo, array $languages = null, $versionNo = null )
    {
        return $this->loadContent( $contentInfo->id, $languages, $versionNo );
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
    public function loadContentByVersionInfo( VersionInfo $versionInfo, array $languages = null )
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
        $contents = array();

        foreach ( $this->content as $content )
        {
            if ( $content->id !== $contentId )
            {
                continue;
            }

            if ( false === $this->repository->canUser( 'content', 'read', $content ) )
            {
                throw new UnauthorizedExceptionStub( 'What error code should be used?' );
            }

            if ( $versionNo === $content->getVersionInfo()->versionNo )
            {
                return $this->filterFieldsByLanguages( $content, $languages );
            }

            $contents[$content->getVersionInfo()->status] = $content;
        }

        if ( null === $versionNo && isset( $contents[VersionInfo::STATUS_PUBLISHED] ) )
        {
            return $this->filterFieldsByLanguages( $contents[VersionInfo::STATUS_PUBLISHED], $languages );
        }
        else if ( null === $versionNo && isset( $contents[VersionInfo::STATUS_DRAFT] ) )
        {
            return $this->filterFieldsByLanguages( $contents[VersionInfo::STATUS_DRAFT], $languages );
        }

        throw new NotFoundExceptionStub( '@TODO: What error code should be used? ID(' . $contentId . ')' );
    }

    /**
     * Creates a filtered version of <b>$content</b> when the given <b>$languages</b>
     * is not <b>NULL</b> and not empty. The returned Content instance will only
     * contain fields for the given language codes.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param string[] $languageCodes
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    private function filterFieldsByLanguages( Content $content, array $languageCodes = null )
    {
        $contentType = $content->contentType;

        $fieldDefinitions = $contentType->getFieldDefinitions();
        foreach ( $content->getFields() as $field )
        {
            foreach ( $fieldDefinitions as $fieldDefinition )
            {
                if ( $fieldDefinition->identifier === $field->fieldDefIdentifier )
                {
                    // @TODO: Refactore out of here for clarity!
                    $this->pseudoExternalStorage->handleLoad(
                        $fieldDefinition,
                        $field,
                        $content
                    );
                }
            }
        }

        if ( empty( $languageCodes ) )
        {
            return $content;
        }

        $fields = array();
        foreach ( $content->getFields() as $field )
        {
            if ( false === $contentType->getFieldDefinition( $field->fieldDefIdentifier )->isTranslatable )
            {
                $fields[] = $field;
            }
            else if ( in_array( $field->languageCode, $languageCodes ) )
            {
                $fields[] = $field;
            }
        }

        return $this->copyContentObject(
            $content,
            array( 'fields' => $fields )
        );
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
        return $this->loadContent(
            $this->loadContentInfoByRemoteId( $remoteId )->id,
            $languages,
            $versionNo
        );
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
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException if a required field is missing
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentCreateStruct $contentCreateStruct
     * @param \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct[] $locationCreateStructs an array of {@link \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct} for each location parent under which a location should be created for the content
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content - the newly created content draft
     */
    public function createContent( ContentCreateStruct $contentCreateStruct, array $locationCreateStructs = array() )
    {
        if ( false === $this->repository->hasAccess( 'content', 'create' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }
        if ( $this->remoteIdExists( $contentCreateStruct->remoteId ) )
        {
            throw new InvalidArgumentExceptionStub( 'What error code should be used?' );
        }

        $languageCodes = $this->getLanguageCodes( $contentCreateStruct->fields, $contentCreateStruct->mainLanguageCode );
        $fields = $this->getFieldsByTypeAndLanguageCode( $contentCreateStruct->contentType, $contentCreateStruct->fields, $contentCreateStruct->mainLanguageCode );

        // Validate all required fields available in each language;
        $this->checkRequiredFields( $contentCreateStruct->contentType, $fields, $languageCodes, $contentCreateStruct->mainLanguageCode );

        // Complete missing fields
        $allFields = $this->createCompleteFields( $contentCreateStruct->contentType, $fields, $languageCodes, $contentCreateStruct->mainLanguageCode );

        // Perform some fake validation to emulate validation exceptions
        $this->fakeFieldValidation( $contentCreateStruct->contentType, $allFields );

        $content = new ContentStub(
            array(
                'id' => ++$this->contentNextId,
                'contentTypeId' => $contentCreateStruct->contentType->id,
                'fields' => $allFields,
                'relations' => array(),

                'versionNo' => 1,
                'repository' => $this->repository
            )
        );

        $contentInfo = new ContentInfoStub(
            array(
                'id' => $this->contentNextId,
                'contentTypeId' => $contentCreateStruct->contentType->id,
                'remoteId' => $contentCreateStruct->remoteId,
                'sectionId' => $contentCreateStruct->sectionId,
                'alwaysAvailable' => $contentCreateStruct->alwaysAvailable,
                'currentVersionNo' => 1,
                'mainLanguageCode' => $contentCreateStruct->mainLanguageCode,
                'modificationDate' => $contentCreateStruct->modificationDate,
                'ownerId' => $this->repository->getCurrentUser()->id,
                'published' => false,
                'publishedDate' => null,
                'mainLocationId' => null,

                'repository' => $this->repository
            )
        );

        $versionInfo = new VersionInfoStub(
            array(
                'id' => ++$this->versionNextId,
                'contentId' => $this->contentNextId,
                'status' => VersionInfo::STATUS_DRAFT,
                'versionNo' => 1,
                'creatorId' => $this->repository->getCurrentUser()->id,
                'creationDate' => new \DateTime(),
                'modificationDate' => $contentCreateStruct->modificationDate,
                'languageCodes' => $languageCodes,
                'initialLanguageCode' => $contentCreateStruct->mainLanguageCode,
                'names' => $this->generateNames( $contentCreateStruct->contentType, $allFields ),

                'repository' => $this->repository
            )
        );

        $fieldDefinitions = $contentCreateStruct->contentType->getFieldDefinitions();
        foreach ( $allFields as $field )
        {
            foreach ( $fieldDefinitions as $fieldDefinition )
            {
                if ( $fieldDefinition->identifier === $field->fieldDefIdentifier )
                {
                    $this->pseudoExternalStorage->handleCreate(
                        $fieldDefinition,
                        $field,
                        $content
                    );
                }
            }
        }

        $this->content[] = $content;
        $this->contentInfo[$contentInfo->id] = $contentInfo;
        $this->versionInfo[$versionInfo->id] = $versionInfo;

        $this->locationsOnPublish[$contentInfo->id] = $locationCreateStructs;

        return $content;
    }

    /**
     * Performs specific fake validations on the given $fields
     *
     * Checks:
     *
     * - String length <= 100 for folder::short_name
     *
     * @param ContentType $contentType
     * @param array $fields
     * @return void
     * @throws ContentFieldValidationException if any of the fake rules are
     *         violated
     */
    private function fakeFieldValidation( ContentType $contentType, array $fields )
    {
        foreach ( $fields as $field )
        {
            switch ( $contentType->identifier )
            {
                case 'folder':
                    switch ( $field->fieldDefIdentifier )
                    {
                        case 'short_name':
                            if ( strlen( $field->value ) > 100 )
                            {
                                throw new Exceptions\ContentFieldValidationExceptionStub();
                            }
                            break;
                    }
                    break;

                case 'forum':
                case 'user_group':
                    switch ( $field->fieldDefIdentifier )
                    {
                        case 'name':
                            if ( !is_string( $field->value ) && $field->value !== null )
                            {
                                throw new Exceptions\InvalidArgumentExceptionStub();
                            }
                            break;
                    }
                    break;

                case 'user':
                    switch ( $field->fieldDefIdentifier )
                    {
                        case 'first_name':
                            if ( !is_string( $field->value ) && $field->value !== null )
                            {
                                throw new Exceptions\InvalidArgumentExceptionStub();
                            }
                            break;
                    }
                    break;
            }
        }
    }

    /**
     * Creates locations on publish, which had been specified on content create
     *
     * @param ContentInfo $contentInfo
     * @return void
     */
    private function createLocationsOnFirstPublish( ContentInfo $contentInfo )
    {
        if ( !isset( $this->locationsOnPublish[$contentInfo->id] ) )
        {
            // Already published
            return;
        }

        $locationService = $this->repository->getLocationService();
        foreach ( $this->locationsOnPublish[$contentInfo->id] as $locationCreateStruct )
        {
            $locationService->createLocation(
                $contentInfo,
                $locationCreateStruct
            );
        }

        unset( $this->locationsOnPublish[$contentInfo->id] );
    }

    /**
     * Returns all language codes used in $fields, including $mainLanguageCode
     * if not null
     *
     * @param array $fields
     * @param string $mainLanguageCode
     * @return string[]
     */
    private function getLanguageCodes( array $fields, $mainLanguageCode = null )
    {
        $languageCodes = array();

        if ( $mainLanguageCode !== null )
        {
            $languageCodes[$mainLanguageCode] = true;
        }

        foreach ( $fields as $field )
        {
            if ( $field->languageCode !== null )
            {
                $languageCodes[$field->languageCode] = true;
            }
        }
        return array_keys( $languageCodes );
    }

    /**
     * Returns $fields structured by type and language code
     *
     * @param ContentType $contentType
     * @param array $fields
     * @param string $mainLanguageCode
     * @return \eZ\Publish\API\Repository\Values\Content\Field[]
     *
     * @throw Exceptions\ContentValidationException if the language code for a
     *        field could not be determined.
     */
    private function getFieldsByTypeAndLanguageCode( ContentType $contentType, array $fields, $mainLanguageCode = null )
    {
        $structuredFields = array();
        foreach ( $fields as $field )
        {
            $languageCode = ( $field->languageCode !== null
                ? $field->languageCode
                : $mainLanguageCode );

            if ( $languageCode === null
                && $contentType->getFieldDefinition( $field->fieldDefIdentifier )->isTranslatable )
            {
                throw new Exceptions\ContentValidationExceptionStub(
                    '@TODO: What error code should be used?'
                );
            }

            $field = $this->cloneField( $field, array( 'languageCode' => $languageCode ) );

            if ( false === isset( $structuredFields[$field->fieldDefIdentifier] ) )
            {
                $structuredFields[$field->fieldDefIdentifier] = array();
            }
            // Only one field of each type per langauge code
            $structuredFields[$field->fieldDefIdentifier][$languageCode] = $field;
        }
        return $structuredFields;
    }

    /**
     * Clones $field, potentially overriding specific properties from
     * $overrides
     *
     * @param Field $field
     * @param array $overrides
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
        return new FieldStub( $fieldData );
    }

    /**
     * Returns $originalFieldId if not null, otherwise a new field ID
     *
     * @param mixed $originalFieldId
     * @return void
     */
    private function getFieldId( $originalFieldId = null )
    {
        if ( $originalFieldId !== null )
        {
            return $originalFieldId;
        }
        return ++$this->fieldNextId;
    }

    /**
     * Checks all fields required by $contentType are available in $fields,
     * taking languages and non-translatable fields into account.
     *
     * Structure is $fields[$fieldIdentifier][$languageCode], while
     * non-translatable fields are stored with $mainLanguageCode.
     *
     * @param ContentType $contentType
     * @param array $fields
     * @param array $languageCodes
     * @param string $mainLanguageCode
     * @return void
     */
    private function checkRequiredFields( ContentType $contentType, array $fields, array $languageCodes, $mainLanguageCode )
    {
        foreach ( $contentType->getFieldDefinitions() as $fieldDefinition )
        {
            if ( !$fieldDefinition->isRequired )
            {
                continue;
            }

            if ( $fieldDefinition->isTranslatable )
            {
                foreach ( $languageCodes as $languageCode )
                {
                    if ( !isset( $fields[$fieldDefinition->identifier][$languageCode] ) || empty( $fields[$fieldDefinition->identifier][$languageCode]->value ) )
                    {
                        throw new ContentValidationExceptionStub(
                            '@TODO: What error code should be used? ' . $fieldDefinition->identifier . ' ' . $languageCode
                        );
                    }
                }
            }
            else
            {
                if ( !isset( $fields[$fieldDefinition->identifier][$mainLanguageCode] ) || empty( $fields[$fieldDefinition->identifier][$mainLanguageCode]->value ) )
                {
                    throw new ContentValidationExceptionStub(
                        '@TODO: What error code should be used? ' . $fieldDefinition->identifier
                    );
                }
            }
        }
    }

    /**
     * Creates a list of all fields, while missing non-required fields are
     * completed
     *
     * @param ContentType $contentType
     * @param array $fields
     * @param array $languageCodes
     * @param string $mainLanguageCode
     * @return \eZ\Publish\API\Repository\Values\Content\Field[]
     */
    private function createCompleteFields( ContentType $contentType, array $fields, array $languageCodes, $mainLanguageCode )
    {
        $allFields = array();
        foreach ( $contentType->getFieldDefinitions() as $fieldDefinition )
        {
            if ( $fieldDefinition->isTranslatable )
            {
                foreach ( $languageCodes as $languageCode )
                {
                    if ( isset( $fields[$fieldDefinition->identifier][$languageCode] ) )
                    {
                        $field = $fields[$fieldDefinition->identifier][$languageCode];
                        $fieldId = $this->getFieldId( $field->id );

                        // Existing translatable field
                        $allFields[$fieldId] = $this->cloneField(
                            $field,
                            array( 'id' => $fieldId )
                        );
                    }
                    else
                    {
                        $fieldId = $this->getFieldId();
                        // Missing translatable field
                        $allFields[$fieldId] = new FieldStub(
                            array(
                                'id' => $fieldId,
                                // 'value' => $fieldDefinition->defaultValue,
                                // No default value in memory!
                                'value' => 'Pseudo default value from memory stuff.',
                                'languageCode' => $languageCode,
                                'fieldDefIdentifier' => $fieldDefinition->identifier
                            )
                        );
                    }
                }
            }
            else
            {
                if ( isset( $fields[$fieldDefinition->identifier][$mainLanguageCode] ) )
                {
                    $field = $fields[$fieldDefinition->identifier][$mainLanguageCode];
                    $fieldId = $this->getFieldId( $field->id );

                    // Existing non-translatable field
                    $allFields[$fieldId] = $this->cloneField(
                        $field,
                        array( 'id' => $fieldId )
                    );
                }
                else
                {
                    $fieldId = $this->getFieldId();

                    // Missing non-translatable field
                    $allFields[$fieldId] = new FieldStub(
                        array(
                            'id' => $fieldId,
                            // 'value' => $fieldDefinition->defaultValue,
                            // No default value in memory!
                            'value' => 'Pseudo default value from memory stuff.',
                            'languageCode' => null,
                            'fieldDefIdentifier' => $fieldDefinition->identifier
                        )
                    );
                }
            }
        }
        return $allFields;
     }

    /**
     * Generates the names based on the given $contentType and $fields
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param \eZ\Publish\API\Repository\Values\Content\Field[] $fields
     * @return string[]
     */
    private function generateNames( ContentType $contentType, array $fields )
    {
        $languages = array_unique(
            array_filter(
                array_map(
                    function ( $field ) use ( $contentType )
                    {
                        $fieldDefinition = $contentType->getFieldDefinition( $field->fieldDefIdentifier );
                        return ( $fieldDefinition->isTranslatable
                            ? $field->languageCode
                            : false );
                    },
                    $fields
                )
            )
        );

        $names = array();
        foreach ( $languages as $languageCode )
        {
            $names[$languageCode] = preg_replace_callback(
                '(<([^>]+)>)',
                function ( $matches ) use ( $fields, $languageCode )
                {
                    $fieldIdentifiers = explode( '|', $matches[1] );
                    foreach ( $fieldIdentifiers as $fieldIdentifier )
                    {
                        foreach ( $fields as $field )
                        {
                            if ( $field->fieldDefIdentifier == $fieldIdentifier
                                && $field->languageCode == $languageCode )
                            {
                                return $field->value;
                            }
                        }
                    }
                },
                $contentType->nameSchema
            );
        }

        return $names;
    }

    /**
     * Parses a name template ala "<short_name|long_name>".
     *
     * @param string $nameTemplate
     * @return string[]
     */
    private function parseNameTemplate( $nameTemplate )
    {
        return explode( '|', substr( $nameTemplate, 1, strlen( $nameTemplate ) - 2 ) );
    }

    /**
     * Updates the metadata.
     *
     * (see {@link ContentMetadataUpdateStruct}) of a content object - to update fields use updateContent
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowd to update the content meta data
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the remoteId in $contentMetadataUpdateStruct is set but already existis
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param \eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct $contentMetadataUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content the content with the updated attributes
     */
    public function updateContentMetadata( ContentInfo $contentInfo, ContentMetadataUpdateStruct $contentMetadataUpdateStruct )
    {
        if ( false === $this->repository->hasAccess( 'content', 'edit' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }
        if ( $this->remoteIdExists( $contentMetadataUpdateStruct->remoteId ) )
        {
            throw new InvalidArgumentExceptionStub( 'What error code should be used?' );
        }

        $this->contentInfo[$contentInfo->id] = new ContentInfoStub(
            array(
                'id' => $contentInfo->id,
                'contentTypeId' => $this->contentInfo[$contentInfo->id]->contentTypeId,
                'remoteId' => $contentMetadataUpdateStruct->remoteId ?: $contentInfo->remoteId,
                'sectionId' => $contentInfo->sectionId,
                'alwaysAvailable' => is_null( $contentMetadataUpdateStruct->alwaysAvailable ) ? $contentInfo->alwaysAvailable : $contentMetadataUpdateStruct->alwaysAvailable,
                'currentVersionNo' => $contentInfo->currentVersionNo,
                'mainLanguageCode' => $contentMetadataUpdateStruct->mainLanguageCode ?: $contentInfo->mainLanguageCode,
                'modificationDate' => $contentMetadataUpdateStruct->modificationDate ?: $contentInfo->modificationDate,
                'ownerId' => $contentMetadataUpdateStruct->ownerId ?: $contentInfo->ownerId,
                'published' => $contentInfo->published,
                'publishedDate' => $contentMetadataUpdateStruct->publishedDate ?: $contentInfo->publishedDate,
                'mainLocationId' => $contentInfo->mainLocationId,

                'repository' => $this->repository
            )
        );

        return $this->loadContent( $contentInfo->id );
    }

    /**
     * deletes a content object including all its versions and locations including their subtrees.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowd to delete the content (in one of the locations of the given content object)
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     */
    public function deleteContent( ContentInfo $contentInfo )
    {
        if ( false === $this->repository->hasAccess( 'content', 'remove' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        // Avoid cycles between ContentService and LocationService
        if ( false === isset( $this->contentInfo[$contentInfo->id] ) )
        {
            return;
        }

        // Load utilized content service
        $locationService = $this->repository->getLocationService();

        foreach ( $this->versionInfo as $key => $versionInfo )
        {
            if ( $versionInfo->contentInfo->id === $contentInfo->id )
            {
                unset( $this->versionInfo[$key] );
            }
        }

        foreach ( $this->content as $key => $content )
        {
            if ( $content->id === $contentInfo->id )
            {
                unset( $this->content[$key] );
            }
        }

        unset( $this->contentInfo[$contentInfo->id] );

        // @HACK: See Asana TODO -- drafts and locations are not handled
        // correctly
        if ( ( $versionInfo->status === VersionInfo::STATUS_DRAFT ) &&
             ( $versionInfo->versionNo === 1 ) )
        {
            return;
        }

        // Delete all locations for the given $contentInfo
        $locations = $locationService->loadLocations( $contentInfo );
        foreach ( $locations as $location )
        {
            $locationService->deleteLocation( $location );
        }
    }

    /**
     * creates a draft from a publshed or archived version.
     *
     * If no version is given, the current published version is used.
     * 4.x: The draft is created with the initialLanguge code of the source version or if not present with the main language.
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
    public function createContentDraft( ContentInfo $contentInfo, VersionInfo $versionInfo = null, User $user = null )
    {
        if ( false === $this->repository->canUser( 'content', 'edit', $contentInfo ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        $versionNo = $versionInfo ? $versionInfo->versionNo : null;

        $content = $this->loadContentByContentInfo( $contentInfo, null, $versionNo );
        $versionInfo = $content->getVersionInfo();

        // Select the greatest version number
        foreach ( $this->versionInfo as $existingVersionInfo )
        {
            if ( $existingVersionInfo->contentId !== $contentInfo->id )
            {
                continue;
            }
            $versionNo = max( $versionNo, $existingVersionInfo->versionNo );
        }

        $contentDraft = new ContentStub(
            array(
                'id' => $content->id,
                'fields' => $content->getFields(),
                'relations' => $content->getRelations(),

                'contentTypeId' => $contentInfo->getContentType()->id,
                'versionNo' => $versionNo + 1,
                'repository' => $this->repository
            )
        );

        $versionDraft = new VersionInfoStub(
            array(
                'id' => ++$this->versionNextId,
                'status' => VersionInfo::STATUS_DRAFT,
                'versionNo' => $versionNo + 1,
                'creatorId' => $this->repository->getCurrentUser()->id,
                'creationDate' => new \DateTime(),
                'modificationDate' => new \DateTime(),
                'languageCodes' => $versionInfo->languageCodes,
                'initialLanguageCode' => $versionInfo->initialLanguageCode,
                'names' => $versionInfo->getNames(),

                'contentId' => $content->id,
                'repository' => $this->repository
            )
        );

        $this->content[] = $contentDraft;
        $this->versionInfo[$versionDraft->id] = $versionDraft;

        return $contentDraft;
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
        $user = $user ?: $this->repository->getCurrentUser();

        if ( false === $this->repository->hasAccess( 'content', 'pendinglist' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        $contentDrafts = array();
        foreach ( $this->versionInfo as $versionInfo )
        {
            if ( $versionInfo->status !== VersionInfo::STATUS_DRAFT )
            {
                continue;
            }
            if ( $versionInfo->creatorId !== $user->id )
            {
                continue;
            }
            $contentDrafts[] = $versionInfo;
        }

        return $contentDrafts;
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
    public function updateContent( VersionInfo $versionInfo, ContentUpdateStruct $contentUpdateStruct )
    {
        if ( false === $this->repository->hasAccess( 'content', 'edit' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }
        if ( $versionInfo->status !== VersionInfo::STATUS_DRAFT )
        {
            throw new BadStateExceptionStub( 'What error code should be used?' );
        }

        $content = $this->loadContentByVersionInfo( $versionInfo );
        $contentType = $content->contentType;

        $initialLanguageCode = $contentUpdateStruct->initialLanguageCode;
        $mainLanguageCode = $versionInfo->getContentInfo()->mainLanguageCode;

        $oldAndNewFields = array_merge( $content->fields, $contentUpdateStruct->fields );

        $languageCodes = $this->getLanguageCodes( $oldAndNewFields, $initialLanguageCode );

        // Automatically overwrites old with new fields
        $fields = $this->getFieldsByTypeAndLanguageCode( $contentType, $oldAndNewFields, $initialLanguageCode ?: $mainLanguageCode );

        // Validate all required fields available in each language
        $this->checkRequiredFields( $contentType, $fields, $languageCodes, $mainLanguageCode );

        // Complete missing fields
        $allFields = $this->createCompleteFields( $contentType, $fields, $languageCodes, $mainLanguageCode );

        // Perform some fake validation to emulate validation exceptions
        $this->fakeFieldValidation( $contentType, $allFields );

        $draftedContent = new ContentStub(
            array(
                'id' => $content->id,
                'fields' => $allFields,
                'relations' => $content->getRelations(),

                'contentTypeId' => $content->contentTypeId,
                'versionNo' => $versionInfo->versionNo,
                'repository' => $this->repository
            )
        );

        $draftedVersionInfo = new VersionInfoStub(
            array(
                'id' => $versionInfo->id,
                'contentId' => $content->id,
                'status' => $versionInfo->status,
                'versionNo' => $versionInfo->versionNo,
                'creatorId' => $versionInfo->creatorId,
                'creationDate' => $versionInfo->creationDate,
                'modificationDate' => new \DateTime(),
                'languageCodes' => $languageCodes,
                'initialLanguageCode' => $mainLanguageCode,
                'names' => $this->generateNames( $contentType, $allFields ),

                'repository' => $this->repository
            )
        );

        $fieldDefinitions = $contentType->getFieldDefinitions();
        foreach ( $allFields as $field )
        {
            foreach ( $fieldDefinitions as $fieldDefinition )
            {
                if ( $fieldDefinition->identifier === $field->fieldDefIdentifier )
                {
                    $this->pseudoExternalStorage->handleUpdate(
                        $fieldDefinition,
                        $field,
                        $draftedContent
                    );
                }
            }
        }

        if ( false === ( $index = array_search( $content, $this->content ) ) )
        {
            throw new \ErrorException( "An implementation error..." );
        }

        $this->versionInfo[$versionInfo->id] = $draftedVersionInfo;
        $this->content[$index] = $draftedContent;

        return $draftedContent;
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
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to publish this version
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException if the version is not a draft
     */
    public function publishVersion( VersionInfo $versionInfo )
    {
        if ( false === $this->repository->canUser( 'content', 'edit', $versionInfo ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }
        if ( $versionInfo->status !== VersionInfo::STATUS_DRAFT )
        {
            throw new BadStateExceptionStub( 'What error code should be used?' );
        }

        $contentInfo = $versionInfo->getContentInfo();

        // Newer versions will be ignored
        $versionNo = $versionInfo->versionNo;

        $publishedContentInfo = new ContentInfoStub(
            array(
                'id' => $contentInfo->id,
                'remoteId' => $contentInfo->remoteId,
                'sectionId' => $contentInfo->sectionId,
                'alwaysAvailable' => $contentInfo->alwaysAvailable,
                'currentVersionNo' => $versionNo,
                'mainLanguageCode' => $contentInfo->mainLanguageCode,
                'modificationDate' => $contentInfo->modificationDate,
                'ownerId' => $contentInfo->ownerId,
                'published' => true,
                'publishedDate' => new \DateTime(),
                'mainLocationId' => $contentInfo->mainLocationId,

                'contentTypeId' => $contentInfo->getContentType()->id,
                'repository' => $this->repository
            )
        );

        $publishedVersionInfo = new VersionInfoStub(
            array(
                'id' => $versionInfo->id,
                'status' => VersionInfo::STATUS_PUBLISHED,
                'versionNo' => $versionNo,
                'creatorId' => $versionInfo->creatorId,
                'initialLanguageCode' => $versionInfo->initialLanguageCode,
                'languageCodes' => $versionInfo->languageCodes,
                'names' => $versionInfo->getNames(),
                'modificationDate' => new \DateTime(),

                'contentId' => $contentInfo->id,
                'repository' => $this->repository
            )
        );

        // Set all published versions of this content object to ARCHIVED
        foreach ( $this->versionInfo as $existingVersionId => $existingVersionInfo )
        {
            if ( $existingVersionInfo->contentId !== $contentInfo->id )
            {
                continue;
            }
            if ( $existingVersionInfo->status !== VersionInfo::STATUS_PUBLISHED )
            {
                continue;
            }

            $this->versionInfo[$existingVersionId] = new VersionInfoStub(
                array(
                    'id' => $existingVersionInfo->id,
                    'status' => VersionInfo::STATUS_ARCHIVED,
                    'versionNo' => $existingVersionInfo->versionNo,
                    'creatorId' => $existingVersionInfo->creatorId,
                    'initialLanguageCode' => $existingVersionInfo->initialLanguageCode,
                    'languageCodes' => $existingVersionInfo->languageCodes,
                    'names' => $existingVersionInfo->getNames(),
                    'modificationDate' => new \DateTime(),

                    'contentId' => $contentInfo->id,
                    'repository' => $this->repository
                )
            );
        }

        // Creates locations specified on content created, if necessary
        $this->createLocationsOnFirstPublish( $publishedContentInfo );

        $this->contentInfo[$contentInfo->id] = $publishedContentInfo;
        $this->versionInfo[$versionInfo->id] = $publishedVersionInfo;

        $this->repository->getUrlAliasService()->_createAliasesForVersion(
            $publishedVersionInfo
        );

        return $this->loadContentByVersionInfo( $versionInfo );
    }

    /**
     * removes the given version
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException if the version is in state published
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to remove this version
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     */
    public function deleteVersion( VersionInfo $versionInfo )
    {
        if ( false === $this->repository->hasAccess( 'content', 'versionremove' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        if ( VersionInfo::STATUS_PUBLISHED === $versionInfo->status )
        {
            throw new BadStateExceptionStub( 'What error code should be used?' );
        }

        foreach ( $this->content as $i => $content )
        {
            if ( $content->versionNo !== $versionInfo->versionNo )
            {
                continue;
            }
            else if ( $content->id !== $versionInfo->contentId )
            {
                continue;
            }

            unset( $this->content[$i] );
            unset( $this->versionInfo[$versionInfo->id] );

            break;
        }

        $references = 0;
        foreach ( $this->content as $i => $content )
        {
            if ( $content->id === $versionInfo->contentId )
            {
                ++$references;
            }
        }

        if ( count( $references ) === 0 )
        {
            unset( $this->contentInfo[$versionInfo->contentId] );
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
    public function loadVersions( ContentInfo $contentInfo )
    {
        if ( false === $this->repository->hasAccess( 'content', 'versionread' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        $versions = array();
        foreach ( $this->versionInfo as $versionInfo )
        {
            if ( $contentInfo->id === $versionInfo->contentId )
            {
                $versions[] = $versionInfo;
            }
        }
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
    public function copyContent( ContentInfo $contentInfo, LocationCreateStruct $destinationLocationCreateStruct, VersionInfo $versionInfo = null )
    {
        if ( false === $this->repository->hasAccess( 'content', 'edit' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        ++$this->contentNextId;

        $versionNo = $versionInfo ? $versionInfo->versionNo : null;

        $this->contentInfo[$this->contentNextId] = new ContentInfoStub(
            array(
                'id' => $this->contentNextId,
                'remoteId' => md5( uniqid( $contentInfo->remoteId, true ) ),
                'sectionId' => $contentInfo->sectionId,
                'alwaysAvailable' => $contentInfo->alwaysAvailable,
                'currentVersionNo' => $versionNo ? 1 : $contentInfo->currentVersionNo,
                'mainLanguageCode' => $contentInfo->mainLanguageCode,
                'modificationDate' => new \DateTime(),
                'ownerId' => $contentInfo->ownerId,
                'published' => $contentInfo->published,
                'publishedDate' => new \DateTime(),
                'mainLocationId' => $contentInfo->mainLocationId,

                'contentTypeId' => $contentInfo->getContentType()->id,
                'repository' => $this->repository
            )
        );

        foreach ( $this->versionInfo as $versionInfoStub )
        {
            if ( $versionInfoStub->contentId !== $contentInfo->id )
            {
                continue;
            }
            if ( $versionNo && $versionInfoStub->versionNo !== $versionNo )
            {
                continue;
            }

            ++$this->versionNextId;

            $this->versionInfo[$this->versionNextId] = new VersionInfoStub(
                array(
                    'id' => $this->versionNextId,
                    'status' => VersionInfo::STATUS_DRAFT,
                    'versionNo' => $versionNo ? 1 : $versionInfoStub->versionNo,
                    'creatorId' => $versionInfoStub->creatorId,
                    'creationDate' => new \DateTime(),
                    'modificationDate' => new \DateTime(),
                    'languageCodes' => $versionInfoStub->languageCodes,
                    'initialLanguageCode' => $versionInfoStub->initialLanguageCode,
                    'names' => $versionInfoStub->getNames(),

                    'contentId' => $this->contentNextId,
                    'repository' => $this->repository
                )
            );
        }

        foreach ( $this->content as $content )
        {
            if ( $content->id !== $contentInfo->id )
            {
                continue;
            }
            if ( $versionNo && $content->versionNo !== $versionNo )
            {
                continue;
            }

            $this->content[] = $this->copyContentObject(
                $content,
                array(
                    'id' => $this->contentNextId,
                    'versionNo' => $versionNo ? 1 : $content->versionNo
                )
            );
        }

        $locationService = $this->repository->getLocationService();
        $location = $locationService->createLocation(
            $this->contentInfo[$this->contentNextId],
            $destinationLocationCreateStruct
        );

        $this->repository->getUrlAliasService()->_createAliasesForLocation( $location );

        return $this->loadContent( $this->contentNextId );
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
    public function loadRelations( VersionInfo $versionInfo )
    {
        if ( false === $this->repository->canUser( 'content', 'read', $versionInfo ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }
        return $this->loadContentByVersionInfo( $versionInfo )->getRelations();
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
    public function loadReverseRelations( ContentInfo $contentInfo )
    {
        if ( false === $this->repository->canUser( 'content', 'reverserelatedlist', $contentInfo ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }

        $relations = array();
        foreach ( $this->content as $content )
        {
            foreach ( $content->getRelations() as $relation )
            {
                if ( $relation->destinationContentInfo === $contentInfo )
                {
                    $relations[] = $relation;
                }
            }
        }
        return $relations;
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
    public function addRelation( VersionInfo $sourceVersion, ContentInfo $destinationContent )
    {
        if ( false === $this->repository->hasAccess( 'content', 'edit' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }
        if ( $sourceVersion->status !== VersionInfo::STATUS_DRAFT )
        {
            throw new BadStateExceptionStub( 'What error code should be used?' );
        }

        $relation = new RelationStub(
            array(
                'id' => 23,
                'sourceContentInfo' => $sourceVersion->contentInfo,
                'destinationContentInfo' => $destinationContent,
                'type' => Relation::COMMON
            )
        );

        $content = $this->loadContentByVersionInfo( $sourceVersion );

        $this->replaceContentObject(
            $content,
            $this->copyContentObject(
                $content,
                array(
                    'relations' => array_merge(
                        $content->getRelations(),
                        array( $relation )
                    )
                )
            )
        );

        return $relation;
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
    public function deleteRelation( VersionInfo $sourceVersion, ContentInfo $destinationContent )
    {
        if ( false === $this->repository->hasAccess( 'content', 'edit' ) )
        {
            throw new UnauthorizedExceptionStub( 'What error code should be used?' );
        }
        if ( VersionInfo::STATUS_DRAFT !== $sourceVersion->status )
        {
            throw new BadStateExceptionStub( 'What error code should be used?' );
        }

        $content = $this->loadContentByVersionInfo( $sourceVersion );
        $relations = $content->getRelations();

        $relationNotFound = true;
        $relationNoCommon = true;
        foreach ( $relations as $i => $relation )
        {
            if ( $relation->destinationContentInfo !== $destinationContent )
            {
                continue;
            }
            $relationNotFound = false;

            if ( $relation->type !== Relation::COMMON )
            {
                continue;
            }
            $relationNoCommon = false;

            unset( $relations[$i] );
            break;
        }

        if ( $relationNotFound || $relationNoCommon )
        {
            throw new InvalidArgumentExceptionStub( 'What error code should be used?' );
        }

        $this->replaceContentObject(
            $content,
            $this->copyContentObject(
                $content,
                array(
                    'relations' => $relations
                )
            )
        );
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
        return new ContentCreateStructStub(
            array(
                'contentType' => $contentType,
                'mainLanguageCode' => $mainLanguageCode,
                'modificationDate' => new \DateTime(),
                'remoteId' => md5( uniqid( __CLASS__, true ) ),
                'ownerId' => $this->repository->getCurrentUser()->id
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
     * @return \eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct
     */
    public function newContentUpdateStruct()
    {
        return new ContentUpdateStructStub();
    }

    /**
     * Internal helper method that returns all ContentInfo objects for the given
     * <b>$contentType</b>.
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     *
     * @return \eZ\Publish\API\Repository\Tests\Stubs\Values\Content\ContentInfoStub[]
     */
    public function __loadContentInfoByContentType( ContentType $contentType )
    {
        $result = array();
        foreach ( $this->contentInfo as $contentInfo )
        {
            if ( $contentInfo->contentType->id === $contentType->id )
            {
                $result[] = $contentInfo;
            }
        }

        return $result;
    }

    /**
     * Internal helper method used to load ContentInfo objects by their main
     * language code.
     *
     * @param string $languageCode
     *
     * @return \eZ\Publish\API\Repository\Tests\Stubs\Values\Content\ContentInfoStub[]
     */
    public function __loadContentInfoByLanguageCode( $languageCode )
    {
        $matches = array();
        foreach ( $this->contentInfo as $contentInfo )
        {
            if ( $contentInfo->mainLanguageCode === $languageCode )
            {
                $matches[] = $contentInfo;
            }
        }
        return $matches;
    }

    /**
     * Internal helper method to emulate a rollback.
     *
     * @return void
     */
    public function __rollback()
    {
        $this->initFromFixture();
    }

    /**
     * Replaces an object internally.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $oldContent
     * @param \eZ\Publish\API\Repository\Values\Content\Content $newContent
     * @return void
     */
    private function replaceContentObject( Content $oldContent, Content $newContent )
    {
        if ( false === ( $index = array_search( $oldContent, $this->content ) ) )
        {
            throw new \ErrorException( "Implementation error..." );
        }

        $this->content[$index] = $newContent;
    }

    /**
     * Copies a content object.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     * @param string[] $overwrites
     * @return Values\Content\ContentStub
     */
    private function copyContentObject( Content $content, array $overwrites = array() )
    {
        $names = array(
            'id',
            'fields',
            'relations',
            'contentTypeId',
            'versionNo',
            'repository'
        );

        $values = array();
        foreach ( $names as $name )
        {
            if ( array_key_exists( $name, $overwrites ) )
            {
                $values[$name] = $overwrites[$name];
            }
            else
            {
                $values[$name] = $content->{$name};
            }
        }

        return new ContentStub( $values );
    }

    /**
     * Tests if the given <b>$remoteId</b> already exists.
     *
     * @param string $remoteId
     *
     * @return boolean
     */
    private function remoteIdExists( $remoteId )
    {
        if ( null === $remoteId )
        {
            return false;
        }

        foreach ( $this->contentInfo as $contentInfo )
        {
            if ( $remoteId === $contentInfo->remoteId )
            {
                return true;
            }
        }
        return false;
    }

    /**
     * Helper method that initializes some default data from an existing legacy
     * test fixture.
     *
     * @return void
     */
    private function initFromFixture()
    {
        list(
            $this->contentInfo,
            $this->contentNextId,
            $this->versionInfo,
            $this->versionNextId,
            $this->content
        ) = $this->repository->loadFixture( 'Content' );
    }

    // Ignore this eZ Publish 5 feature by now.

    // @codeCoverageIgnoreStart

    /**
     * Translate a version
     *
     * updates the destination version given in $translationInfo with the provided translated fields in $translationValues
     *
     * @example Examples/translation_5x.php
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to update this version
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException if the given destiantioon version is not a draft
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
    public function translateVersion( TranslationInfo $translationInfo, TranslationValues $translationValues, User $user = null )
    {
        // TODO: Implement translateVersion() method.
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
        // TODO: Implement addTranslationInfo() method.
    }

    /**
     * lists the translations done on this content object
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed read translation infos
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param array $filter
     * @todo TBD - filter by sourceversion destination version and languages
     *
     * @return \eZ\Publish\API\Repository\Values\Content\TranslationInfo[] an array of {@link TranslationInfo}
     *
     * @since 5.0
     */
    public function loadTranslationInfos( ContentInfo $contentInfo, array $filter = array() )
    {
        // TODO: Implement loadTranslationInfos() method.
    }

    /**
     * Instantiates a new TranslationInfo object
     * @return \eZ\Publish\API\Repository\Values\Content\TranslationInfo
     */
    public function newTranslationInfo()
    {
        // TODO: Implement newTranslationInfo() method.
    }

    /**
     * Instantiates a Translation object
     * @return \eZ\Publish\API\Repository\Values\Content\TranslationValues
     */
    public function newTranslationValues()
    {
        // TODO: Implement newTranslationValues() method.
    }

    // @codeCoverageIgnoreEnd
}
