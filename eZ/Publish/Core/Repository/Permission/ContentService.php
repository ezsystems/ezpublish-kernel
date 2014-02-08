<?php
/**
 * File containing the eZ\Publish\Core\Repository\Permission\ContentService class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package eZ\Publish\Core\Repository\Permission
 */

namespace eZ\Publish\Core\Repository\Permission;

use eZ\Publish\API\Repository\ContentService as ContentServiceInterface;
use eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\Content\TranslationInfo;
use eZ\Publish\API\Repository\Values\Content\TranslationValues;
use eZ\Publish\API\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use eZ\Publish\Core\Repository\Permission\PermissionsService;

/**
 * This class provides service methods for managing content
 *
 * @example Examples/content.php
 *
 * @package eZ\Publish\Core\Repository\Permission
 */
class ContentService implements ContentServiceInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    protected $permissionsService;

    /**
     * @var \eZ\Publish\API\Repository\ContentService
     */
    protected $innerContentService;

    /**
     * Setups service with reference to repository object that created it & corresponding handler
     *
     * @param \eZ\Publish\API\Repository\ContentService $innerContentService
     * @param PermissionsService $permissionsService
     */
    public function __construct(
        ContentServiceInterface $innerContentService,
        PermissionsService $permissionsService
    )
    {
        $this->innerContentService = $innerContentService;
        $this->permissionsService = $permissionsService;
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
        $contentInfo = $this->innerContentService->loadContentInfo( $contentId );

        if ( !$this->permissionsService->canUser( 'content', 'read', $contentInfo ) )
            throw new UnauthorizedException( 'content', 'read' );

        return $contentInfo;
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
        $contentInfo = $this->innerContentService->loadContentInfoByRemoteId( $remoteId );

        if ( !$this->permissionsService->canUser( 'content', 'read', $contentInfo ) )
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
        $versionInfo = $this->innerContentService->loadVersionInfoById( $contentId, $versionNo );

        if ( $versionInfo->status === VersionInfo::STATUS_PUBLISHED )
        {
            $function = "read";
        }
        else
        {
            $function = "versionread";
        }

        if ( !$this->permissionsService->canUser( 'content', $function, $versionInfo ) )
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
    public function loadContentByVersionInfo( VersionInfo $versionInfo, array $languages = null )
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
        $content = $this->innerContentService->loadContent( $contentId, $languages, $versionNo );

        if ( !$this->permissionsService->canUser( 'content', 'read', $content ) )
            throw new UnauthorizedException( 'content', 'read' );

        if (
            $content->getVersionInfo()->status !== VersionInfo::STATUS_PUBLISHED
            && !$this->permissionsService->canUser( 'content', 'versionread', $content )
        )
            throw new UnauthorizedException( 'content', 'versionread' );

        return $content;
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
        $content = $this->innerContentService->loadContentByRemoteId( $remoteId, $languages, $versionNo );

        if ( !$this->permissionsService->canUser( 'content', 'read', $content ) )
            throw new UnauthorizedException( 'content', 'read' );

        if (
            $content->getVersionInfo()->status !== VersionInfo::STATUS_PUBLISHED
            && !$this->permissionsService->canUser( 'content', 'versionread', $content )
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
    public function createContent( ContentCreateStruct $contentCreateStruct, array $locationCreateStructs = array() )
    {
        // A few validations duplicating what is in DomainLogic because they are needed by permissions
        if ( $contentCreateStruct->mainLanguageCode === null )
        {
            throw new InvalidArgumentException( "\$contentCreateStruct", "'mainLanguageCode' property must be set" );
        }

        if ( $contentCreateStruct->contentType === null )
        {
            throw new InvalidArgumentException( "\$contentCreateStruct", "'contentType' property must be set" );
        }

        // Helper conventions
        $contentCreateStruct = clone $contentCreateStruct;
        if ( $contentCreateStruct->ownerId === null )
        {
            $contentCreateStruct->ownerId = $this->permissionsService->getCurrentUser()->id;
        }

        if ( $contentCreateStruct->alwaysAvailable === null )
        {
            $contentCreateStruct->alwaysAvailable = false;
        }

        if ( empty( $contentCreateStruct->sectionId ) )
        {
            /*
             * @todo Change location create struct to take parent location instead of just Id
             * or make this service depend on inner LocationService so we have this for permissions
            if ( isset( $locationCreateStructs[0] ) )
            {

                $location = $this->innerRepository->getLocationService()->loadLocation(
                    $locationCreateStructs[0]->parentLocationId
                );
                $contentCreateStruct->sectionId = $location->contentInfo->sectionId;
            }
            else*/
            {
                $contentCreateStruct->sectionId = 1;
            }
        }

        if ( !$this->permissionsService->canUser( 'content', 'create', $contentCreateStruct, $locationCreateStructs ) )
        {
            throw new UnauthorizedException( 'content', 'create' );
        }

        return $this->innerContentService->createContent( $contentCreateStruct, $locationCreateStructs );
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
        $contentInfo = $this->innerContentService->loadContentInfo( $contentInfo->id );

        if ( !$this->permissionsService->canUser( 'content', 'edit', $contentInfo ) )
            throw new UnauthorizedException( 'content', 'edit' );

        return $this->innerContentService->updateContentMetadata( $contentInfo, $contentMetadataUpdateStruct );
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
        if ( !$this->permissionsService->canUser( 'content', 'remove', $contentInfo ) )
            throw new UnauthorizedException( 'content', 'remove' );

        $this->innerContentService->deleteContent( $contentInfo );
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
    public function createContentDraft( ContentInfo $contentInfo, VersionInfo $versionInfo = null, User $creator = null )
    {
        // Helper convention
        if ( $creator === null )
        {
            $creator = $this->permissionsService->getCurrentUser();
        }

        if ( !$this->permissionsService->canUser( 'content', 'edit', $contentInfo ) )
            throw new UnauthorizedException( 'content', 'edit', array( 'name' => $contentInfo->name ) );

        return $this->innerContentService->createContentDraft( $contentInfo, $versionInfo, $creator );
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
        // Helper convention
        if ( $user === null )
        {
            $user = $this->permissionsService->getCurrentUser();
        }

        // throw early if user has absolutely no access to versionread
        $versionReadAccess = $this->permissionsService->hasAccess( 'content', 'versionread' );
        if ( $versionReadAccess === false )
            throw new UnauthorizedException( 'content', 'versionread' );

        $versionInfoList = $this->innerContentService->loadContentDrafts( $user );

        // return early if user has full access
        if ( $versionReadAccess === true )
            return $versionInfoList;

        // check each item as current user has limited access
        foreach ( $versionInfoList as $versionInfo )
        {
            if ( !$this->permissionsService->canUser( 'content', 'versionread', $versionInfo ) )
                throw new UnauthorizedException( 'content', 'versionread' );
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
    public function translateVersion( TranslationInfo $translationInfo, TranslationValues $translationValues, User $modifier = null )
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
    public function updateContent( VersionInfo $versionInfo, ContentUpdateStruct $contentUpdateStruct )
    {
        if ( $contentUpdateStruct->creatorId === null )
        {
            $contentUpdateStruct->creatorId = $this->permissionsService->getCurrentUser()->id;
        }

        if ( !$this->permissionsService->canUser( 'content', 'edit', $versionInfo ) )
            throw new UnauthorizedException( 'content', 'edit' );

        return $this->innerContentService->updateContent( $versionInfo, $contentUpdateStruct );
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
    public function publishVersion( VersionInfo $versionInfo )
    {
        if ( !$versionInfo->getContentInfo()->published )
        {
            if ( !$this->permissionsService->canUser( "content", "create", $versionInfo ) )
            {
                throw new UnauthorizedException( 'content', 'create' );
            }
        }
        else if ( !$this->permissionsService->canUser( 'content', 'edit', $versionInfo ) )
        {
            throw new UnauthorizedException( 'content', 'edit' );
        }

        return $this->innerContentService->publishVersion( $versionInfo );
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
        if ( !$this->permissionsService->canUser( 'content', 'versionremove', $versionInfo ) )
            throw new UnauthorizedException( 'content', 'versionremove' );

        $this->innerContentService->publishVersion( $versionInfo );
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
        // throw early if user has absolutely no access to versionread
        $versionReadAccess = $this->permissionsService->hasAccess( 'content', 'versionread' );
        if ( $versionReadAccess === false )
            throw new UnauthorizedException( 'content', 'versionread' );

        $versionInfoList = $this->innerContentService->loadVersions( $contentInfo );

        // return early if user has full access
        if ( $versionReadAccess === true )
            return $versionInfoList;

        // check each item as current user has limited access
        foreach ( $versionInfoList as $versionInfo )
        {
            if ( !$this->permissionsService->canUser( 'content', 'versionread', $versionInfo ) )
                throw new UnauthorizedException( 'content', 'versionread' );
        }

        return $versionInfoList;
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
    public function copyContent( ContentInfo $contentInfo, LocationCreateStruct $destinationLocationCreateStruct, VersionInfo $versionInfo = null)
    {
        if ( !$this->permissionsService->canUser( 'content', 'create', $contentInfo, $destinationLocationCreateStruct ) )
            throw new UnauthorizedException( 'content', 'create' );

        return $this->innerContentService->copyContent( $contentInfo, $destinationLocationCreateStruct, $versionInfo );
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
    public function loadRelations( VersionInfo $versionInfo )
    {
        if ( $versionInfo->status === VersionInfo::STATUS_PUBLISHED )
        {
            $function = "read";
        }
        else
        {
            $function = "versionread";
        }

        if ( !$this->permissionsService->canUser( 'content', $function, $versionInfo ) )
        {
            throw new UnauthorizedException( 'content', $function );
        }

        $relationList = $this->innerContentService->loadRelations( $versionInfo );

        // check each item for read access
        $returnList = array();
        foreach ( $relationList as $relation )
        {
            if ( $this->permissionsService->canUser( 'content', 'read', $relation->getDestinationContentInfo() ) )
                $returnList[] = $relation;
        }

        return $returnList;
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
        if ( $this->permissionsService->hasAccess( 'content', 'reverserelatedlist' ) !== true )
            throw new UnauthorizedException( 'content', 'reverserelatedlist' );

        $relationList = $this->innerContentService->loadReverseRelations( $contentInfo );

        // check each item for read access and filter
        $returnList = array();
        foreach ( $relationList as $relation )
        {
            if ( $this->permissionsService->canUser( 'content', 'read', $relation->getSourceContentInfo() ) )
                $returnList[] = $relation;
        }

        return $returnList;
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
    public function addRelation( VersionInfo $sourceVersion, ContentInfo $destinationContent )
    {
        if ( !$this->permissionsService->canUser( 'content', 'edit', $sourceVersion ) )
            throw new UnauthorizedException( 'content', 'edit' );

        return $this->innerContentService->addRelation( $sourceVersion, $destinationContent );
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
        if ( !$this->permissionsService->canUser( 'content', 'edit', $sourceVersion ) )
            throw new UnauthorizedException( 'content', 'edit' );

        $this->innerContentService->deleteRelation( $sourceVersion, $destinationContent );
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
        return $this->innerContentService->newContentCreateStruct( $contentType, $mainLanguageCode );
    }

    /**
     * Instantiates a new content meta data update struct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct
     */
    public function newContentMetadataUpdateStruct()
    {
        return $this->innerContentService->newContentMetadataUpdateStruct();
    }

    /**
     * Instantiates a new content update struct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct
     */
    public function newContentUpdateStruct()
    {
        return $this->innerContentService->newContentUpdateStruct();
    }

    /**
     * Instantiates a new TranslationInfo object
     * @return \eZ\Publish\API\Repository\Values\Content\TranslationInfo
     */
    public function newTranslationInfo()
    {
        return $this->innerContentService->newTranslationInfo();
    }

    /**
     * Instantiates a Translation object
     * @return \eZ\Publish\API\Repository\Values\Content\TranslationValues
     */
    public function newTranslationValues()
    {
        return $this->innerContentService->newTranslationValues();
    }
}
