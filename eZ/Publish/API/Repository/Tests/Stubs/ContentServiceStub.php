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
use \eZ\Publish\API\Repository\Values\Content\SearchResult;
use \eZ\Publish\API\Repository\Values\Content\TranslationInfo;
use \eZ\Publish\API\Repository\Values\Content\TranslationValues;
use \eZ\Publish\API\Repository\Values\Content\VersionInfo;
use \eZ\Publish\API\Repository\Values\ContentType\ContentType;
use \eZ\Publish\API\Repository\Values\Content\Query;
use \eZ\Publish\API\Repository\Values\Content\Query\Criterion;
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
     * Instantiates a new content service stub.
     *
     * @param \eZ\Publish\API\Repository\Tests\Stubs\RepositoryStub $repository
     */
    public function __construct( RepositoryStub $repository )
    {
        $this->repository = $repository;
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
            return  $this->contentInfo[$contentId];
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
        foreach ( $this->versionInfo as $versionInfo )
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
        if ( null === $languageCodes || 0 === count( $languageCodes ) )
        {
            return $content;
        }

        $contentType = $content->contentType;

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

        $fields = array();
        foreach ( $contentCreateStruct->fields as $field )
        {
            if ( false === isset( $fields[$field->fieldDefIdentifier] ) )
            {
                $fields[$field->fieldDefIdentifier] = array();
            }
            $fields[$field->fieldDefIdentifier][] = $field;
        }

        // Now validate that all required fields
        $allFields = array();
        foreach ( $contentCreateStruct->contentType->getFieldDefinitions() as $fieldDefinition )
        {
            if ( isset( $fields[$fieldDefinition->identifier] ) )
            {
                foreach ( $fields[$fieldDefinition->identifier] as $field )
                {
                    $fieldId = ++$this->fieldNextId;

                    $allFields[$fieldId] = new Field(
                        array(
                            'id'                  =>  $fieldId,
                            'value'               =>  $field->value,
                            'languageCode'        =>  $field->languageCode,
                            'fieldDefIdentifier'  =>  $fieldDefinition->identifier
                        )
                    );
                }
            }
            else if ( $fieldDefinition->isRequired )
            {
                throw new ContentValidationExceptionStub(
                    '@TODO: What error code should be used? ' . $fieldDefinition->identifier
                );
            }
            else
            {
                $fieldId = ++$this->fieldNextId;

                $allFields[$fieldId] = new Field(
                    array(
                        'id'                  =>  $fieldId,
                        'value'               =>  $fieldDefinition->defaultValue,
                        'languageCode'        =>  $contentCreateStruct->contentType->mainLanguageCode,
                        'fieldDefIdentifier'  =>  $fieldDefinition->identifier
                    )
                );
            }
        }

        if ( $this->remoteIdExists( $contentCreateStruct->remoteId ) )
        {
            throw new InvalidArgumentExceptionStub( 'What error code should be used?' );
        }

        $languageCodes = array( $contentCreateStruct->mainLanguageCode );
        foreach ( $allFields as $field )
        {
            $languageCodes[] = $field->languageCode;
        }
        $languageCodes = array_unique( $languageCodes );

        $content = new ContentStub(
            array(
                'id'      =>  ++$this->contentNextId,
                'contentTypeId'  =>  $contentCreateStruct->contentType->id,
                'fields'         =>  $allFields,
                'relations'      =>  array(),

                'versionNo'      =>  1,
                'repository'     =>  $this->repository
            )
        );

        $contentInfo = new ContentInfoStub(
            array(
                'id'         =>  $this->contentNextId,
                'contentTypeId'     =>  $contentCreateStruct->contentType->id,
                'remoteId'          =>  $contentCreateStruct->remoteId,
                'sectionId'         =>  $contentCreateStruct->sectionId,
                'alwaysAvailable'   =>  $contentCreateStruct->alwaysAvailable,
                'currentVersionNo'  =>  1,
                'mainLanguageCode'  =>  $contentCreateStruct->mainLanguageCode,
                'modificationDate'  =>  $contentCreateStruct->modificationDate,
                'ownerId'           =>  $this->repository->getCurrentUser()->id,
                'published'         =>  false,
                'publishedDate'     =>  null,
                'mainLocationId'    =>  null,

                'repository'      =>  $this->repository
            )
        );

        $versionInfo = new VersionInfoStub(
            array(
                'id'                   =>  ++$this->versionNextId,
                'contentId'            =>  $this->contentNextId,
                'status'               =>  VersionInfo::STATUS_DRAFT,
                'versionNo'            =>  1,
                'creatorId'            =>  $this->repository->getCurrentUser()->id,
                'creationDate'         =>  new \DateTime(),
                'modificationDate'     =>  $contentCreateStruct->modificationDate,
                'languageCodes'        =>  $languageCodes,
                'initialLanguageCode'  =>  $contentCreateStruct->mainLanguageCode,

                'repository'           =>  $this->repository
            )
        );

        $this->content[]                            = $content;
        $this->contentInfo[$contentInfo->id]        = $contentInfo;
        $this->versionInfo[$versionInfo->id]        = $versionInfo;

        $this->index[$contentInfo->id]['versionId'][$versionInfo->id] = $versionInfo->id;
        $this->index[$contentInfo->id]['contentId'][count( $this->content ) - 1] = count( $this->content ) - 1;

        $locationService = $this->repository->getLocationService();
        foreach ( $locationCreateStructs as $locationCreateStruct )
        {
            $locationService->createLocation(
                $contentInfo,
                $locationCreateStruct
            );
        }

        return $content;
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
    public function updateContentMetadata( ContentInfo $contentInfo, ContentMetaDataUpdateStruct $contentMetadataUpdateStruct )
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
                'id'                =>  $contentInfo->id,
                'contentTypeId'     =>  $this->contentInfo[$contentInfo->id]->contentTypeId,
                'remoteId'          =>  $contentMetadataUpdateStruct->remoteId ?: $contentInfo->remoteId,
                'sectionId'         =>  $contentInfo->sectionId,
                'alwaysAvailable'   =>  is_null( $contentMetadataUpdateStruct->alwaysAvailable ) ? $contentInfo->alwaysAvailable : $contentMetadataUpdateStruct->alwaysAvailable,
                'currentVersionNo'  =>  $contentInfo->currentVersionNo,
                'mainLanguageCode'  =>  $contentMetadataUpdateStruct->mainLanguageCode ?: $contentInfo->mainLanguageCode,
                'modificationDate'  =>  $contentMetadataUpdateStruct->modificationDate ?: $contentInfo->modificationDate,
                'ownerId'           =>  $contentMetadataUpdateStruct->ownerId ?: $contentInfo->ownerId,
                'published'         =>  $contentInfo->published,
                'publishedDate'     =>  $contentMetadataUpdateStruct->publishedDate ?: $contentInfo->publishedDate,
                'mainLocationId'    =>  $contentInfo->mainLocationId,

                'repository'      =>  $this->repository
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

        // Select the greatest version number
        foreach ( $this->versionInfo as $versionInfo )
        {
            if ( $versionInfo->contentId !== $contentInfo->id )
            {
                continue;
            }
            $versionNo = max( $versionNo, $versionInfo->versionNo );
        }

        $contentDraft = new ContentStub(
            array(
                'id'      =>  $content->id,
                'fields'         =>  $content->getFields(),
                'relations'      =>  $content->getRelations(),

                'contentTypeId'  =>  $contentInfo->getContentType()->id,
                'versionNo'      =>  $versionNo + 1,
                'repository'     =>  $this->repository
            )
        );

        $versionDraft = new VersionInfoStub(
            array(
                'id'                   =>  ++$this->versionNextId,
                'status'               =>  VersionInfo::STATUS_DRAFT,
                'versionNo'            =>  $versionNo + 1,
                'creatorId'            =>  $this->repository->getCurrentUser()->id,
                'creationDate'         =>  new \DateTime(),
                'modificationDate'     =>  new \DateTime(),
                'languageCodes'        =>  $versionInfo->languageCodes,
                'initialLanguageCode'  =>  $versionInfo->initialLanguageCode,

                'contentId'            =>  $content->id,
                'repository'           =>  $this->repository
            )
        );

        $this->content[]                      = $contentDraft;
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

        $content     = $this->loadContentByVersionInfo( $versionInfo );
        $contentType = $content->contentType;

        $fieldIds = array();
        $fields   = array();
        foreach ( $contentUpdateStruct->fields as $field )
        {
            $fieldIds[$field->fieldDefIdentifier] = true;

            $fieldDefinition = $contentType->getFieldDefinition( $field->fieldDefIdentifier );

            if ( null === $field->languageCode &&
                 null === $contentUpdateStruct->initialLanguageCode &&
                $fieldDefinition->isTranslatable )
            {
                throw new ContentValidationExceptionStub( 'What error code should be used?' );
            }
            if ( '' === trim( $field->value ) && $fieldDefinition->isRequired )
            {
                throw new ContentValidationExceptionStub( 'What error code should be used?' );
            }

            $fields[] = new Field(
                array(
                    'id'                  =>  ++$this->fieldNextId,
                    'value'               =>  $field->value,
                    'languageCode'        =>  $field->languageCode ?: $contentUpdateStruct->initialLanguageCode,
                    'fieldDefIdentifier'  =>  $field->fieldDefIdentifier
                )
            );
        }

        foreach ( $content->getFields() as $field )
        {
            if ( isset( $fieldIds[$field->fieldDefIdentifier] ) )
            {
                continue;
            }
            $fields[] = $field;
        }

        $draftedContent = new ContentStub(
            array(
                'id'      =>  $content->id,
                'fields'         =>  $fields,
                'relations'      =>  $content->getRelations(),

                'contentTypeId'  =>  $content->contentTypeId,
                'versionNo'      =>  $versionInfo->versionNo,
                'repository'     =>  $this->repository
            )
        );

        $draftedVersionInfo = new VersionInfoStub(
            array(
                'id'                   =>  $versionInfo->id,
                'contentId'            =>  $content->id,
                'status'               =>  $versionInfo->status,
                'versionNo'            =>  $versionInfo->versionNo,
                'creatorId'            =>  $versionInfo->creatorId,
                'creationDate'         =>  $versionInfo->creationDate,
                'modificationDate'     =>  new \DateTime(),
                'languageCodes'        =>  $versionInfo->languageCodes,
                'initialLanguageCode'  =>  $contentUpdateStruct->initialLanguageCode ?: $versionInfo->initialLanguageCode,

                'repository'           =>  $this->repository
            )
        );

        if ( false === ( $index = array_search( $content, $this->content ) ) )
        {
            throw new \ErrorException( "An implementation error..." );
        }

        $this->versionInfo[$versionInfo->id] = $draftedVersionInfo;
        $this->content[$index]               = $draftedContent;

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

        $versionNo = max( $versionInfo->versionNo, $contentInfo->currentVersionNo );

        $publishedContentInfo = new ContentInfoStub(
            array(
                'id'         =>  $contentInfo->id,
                'remoteId'          =>  $contentInfo->remoteId,
                'sectionId'         =>  $contentInfo->sectionId,
                'alwaysAvailable'   =>  $contentInfo->alwaysAvailable,
                'currentVersionNo'  =>  $versionNo,
                'mainLanguageCode'  =>  $contentInfo->mainLanguageCode,
                'modificationDate'  =>  $contentInfo->modificationDate,
                'ownerId'           =>  $contentInfo->ownerId,
                'published'         =>  true,
                'publishedDate'     =>  new \DateTime(),
                'mainLocationId'    =>  $contentInfo->mainLocationId,

                'contentTypeId'     =>  $contentInfo->getContentType()->id,
                'repository'        =>  $this->repository
            )
        );

        $publishedVersionInfo = new VersionInfoStub(
            array(
                'id'                   =>  $versionInfo->id,
                'status'               =>  VersionInfo::STATUS_PUBLISHED,
                'versionNo'            =>  $versionNo,
                'creatorId'            =>  $versionInfo->creatorId,
                'initialLanguageCode'  =>  $versionInfo->initialLanguageCode,
                'languageCodes'        =>  $versionInfo->languageCodes,
                'modificationDate'     =>  new \DateTime(),

                'contentId'            =>  $contentInfo->id,
                'repository'           =>  $this->repository
            )
        );

        // Set all published versions of this content object to ARCHIVED
        foreach ( $this->versionInfo as $versionId => $versionInfo )
        {
            if ( $versionInfo->contentId !== $contentInfo->id )
            {
                continue;
            }
            if ( $versionInfo->status !== VersionInfo::STATUS_PUBLISHED )
            {
                continue;
            }

            $this->versionInfo[$versionId] = new VersionInfoStub(
                array(
                    'id'                   =>  $versionInfo->id,
                    'status'               =>  VersionInfo::STATUS_ARCHIVED,
                    'versionNo'            =>  $versionInfo->versionNo,
                    'creatorId'            =>  $versionInfo->creatorId,
                    'initialLanguageCode'  =>  $versionInfo->initialLanguageCode,
                    'languageCodes'        =>  $versionInfo->languageCodes,
                    'modificationDate'     =>  new \DateTime(),

                    'contentId'            =>  $contentInfo->id,
                    'repository'           =>  $this->repository
                )
            );
        }

        $this->contentInfo[$contentInfo->id] = $publishedContentInfo;
        $this->versionInfo[$versionInfo->id] = $publishedVersionInfo;

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
                'id'         =>  $this->contentNextId,
                'remoteId'          =>  md5( uniqid( $contentInfo->remoteId, true ) ),
                'sectionId'         =>  $contentInfo->sectionId,
                'alwaysAvailable'   =>  $contentInfo->alwaysAvailable,
                'currentVersionNo'  =>  $versionNo ? 1 : $contentInfo->currentVersionNo,
                'mainLanguageCode'  =>  $contentInfo->mainLanguageCode,
                'modificationDate'  =>  new \DateTime(),
                'ownerId'           =>  $contentInfo->ownerId,
                'published'         =>  $contentInfo->published,
                'publishedDate'     =>  new \DateTime(),
                'mainLocationId'    =>  $contentInfo->mainLocationId,

                'contentTypeId'     =>  $contentInfo->getContentType()->id,
                'repository'        =>  $this->repository
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
                    'id'                   =>  $this->versionNextId,
                    'status'               =>  VersionInfo::STATUS_DRAFT,
                    'versionNo'            =>  $versionNo ? 1 : $versionInfoStub->versionNo,
                    'creatorId'            =>  $versionInfoStub->creatorId,
                    'creationDate'         =>  new \DateTime(),
                    'modificationDate'     =>  new \DateTime(),
                    'languageCodes'        =>  $versionInfoStub->languageCodes,
                    'initialLanguageCode'  =>  $versionInfoStub->initialLanguageCode,

                    'contentId'            =>  $this->contentNextId,
                    'repository'           =>  $this->repository
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
                    'id'  =>  $this->contentNextId,
                    'versionNo'  =>  $versionNo ? 1 : $content->versionNo
                )
            );
        }

        $locationService = $this->repository->getLocationService();
        $locationService->createLocation(
            $this->contentInfo[$this->contentNextId],
            $destinationLocationCreateStruct
        );

        return $this->loadContent( $this->contentNextId );
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
        $callbacks = array();
        foreach ( $query->criterion->criteria as $criteria )
        {
            if ( $criteria->operator === Criterion\Operator::LIKE )
            {
                $regexp     = '(' . str_replace( '\*', '.*', preg_quote( $criteria->value ) ) . ')i';
                $identifier = $criteria->target;

                $callbacks[] = function( Content $content ) use ( $identifier, $regexp ) {
                    foreach ( $content->getFields() as $field )
                    {
                        if ( $field->fieldDefIdentifier !== $identifier )
                        {
                            continue;
                        }

                        if ( preg_match( $regexp, $field->value ) )
                        {

                            return true;
                        }
                    }
                    return false;
                };
            }
        }

        if ( false === $filterOnUserPermissions )
        {
            $this->repository->disableUserPermissions();
        }

        $result = array();
        foreach ( $this->content as $content )
        {
            if ( $filterOnUserPermissions && false === $this->repository->canUser( 'content', 'read', $content ) )
            {
                continue;
            }
            if ( $content->getVersionInfo()->status !== VersionInfo::STATUS_PUBLISHED )
            {
                continue;
            }

            foreach ( $callbacks as $callback )
            {
                if ( false === $callback( $content ) )
                {
                    continue 2;
                }
            }

            $result[] = $content;
        }

        if ( isset( $fieldFilters['languages'] ) )
        {
            foreach ( $result as $i => $content )
            {
                $result[$i] = $this->filterFieldsByLanguages(
                    $content,
                    $fieldFilters['languages']
                );
            }
        }

        $this->repository->enableUserPermissions();

        return new SearchResult(
            array(
                'query'  =>  $query,
                'count'  =>  count( $result ),
                'items'  =>  $result
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

        if ( $searchResult->count === 1 )
        {
            return reset( $searchResult->items );
        }
        if ( $searchResult->count > 1 )
        {
            throw new InvalidArgumentExceptionStub( 'What error code should be used?' );
        }
        throw new NotFoundExceptionStub( 'What error code should be used?' );
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
                'id'                      =>  23,
                'sourceContentInfo'       =>  $sourceVersion->contentInfo,
                'destinationContentInfo'  =>  $destinationContent,
                'type'                    =>  Relation::COMMON
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

        $content   = $this->loadContentByVersionInfo( $sourceVersion );
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
                    'relations'  =>  $relations
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
                'contentType'       =>  $contentType,
                'mainLanguageCode'  =>  $mainLanguageCode,
                'modificationDate'  =>  new \DateTime(),
                'remoteId'          =>  md5( uniqid( __CLASS__, true ) ),
                'ownerId'           =>  $this->repository->getCurrentUser()->id
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
        return new ContentMetaDataUpdateStruct();
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
