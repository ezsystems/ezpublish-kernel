<?php
/**
 * ContentService class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot;
use \eZ\Publish\API\Repository\ContentService as ContentServiceInterface,

/**
 * ContentService class
 * @package eZ\Publish\Core\SignalSlot
 */
class ContentService implements ContentServiceInterface
{
    /**
     * Aggregated service
     *
     * @var \eZ\Publish\API\Repository\ContentService
     */
    protected $service;

    /**
     * SignalDispatcher
     *
     * @var \eZ\Publish\Core\SignalSlot\SignalDispatcher
     */
    protected $signalDispatcher;

    /**
     * Constructor
     *
     * Construct service object from aggregated service and signal
     * dispatcher
     *
     * @param \eZ\Publish\API\Repository\ContentService $service
     * @param \eZ\Publish\Core\SignalSlot\SignalDispatcher $signalDispatcher
     */
    public function __construct( ContentServiceInterface $service, SignalDispatcher $signalDispatcher )
    {
        $this->service          = $service;
        $this->signalDispatcher = $signalDispatcher;
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
        $returnValue = $this->service->loadContentInfo( $contentId );
        $this->signalDispatcher()->emit(
            new Signal\ContentService\LoadContentInfoSignal( $contentId )
        );
        return $returnValue;
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
        $returnValue = $this->service->loadContentInfoByRemoteId( $remoteId );
        $this->signalDispatcher()->emit(
            new Signal\ContentService\LoadContentInfoByRemoteIdSignal( $remoteId )
        );
        return $returnValue;
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
    public function loadVersionInfo( eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo, $versionNo = null )
    {
        $returnValue = $this->service->loadVersionInfo( $contentInfo, $versionNo );
        $this->signalDispatcher()->emit(
            new Signal\ContentService\LoadVersionInfoSignal( $contentInfo, $versionNo )
        );
        return $returnValue;
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
        $returnValue = $this->service->loadVersionInfoById( $contentId, $versionNo );
        $this->signalDispatcher()->emit(
            new Signal\ContentService\LoadVersionInfoByIdSignal( $contentId, $versionNo )
        );
        return $returnValue;
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
    public function loadContentByContentInfo( eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo, $languages = null, $versionNo = null )
    {
        $returnValue = $this->service->loadContentByContentInfo( $contentInfo, $languages, $versionNo );
        $this->signalDispatcher()->emit(
            new Signal\ContentService\LoadContentByContentInfoSignal( $contentInfo, $languages, $versionNo )
        );
        return $returnValue;
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
    public function loadContentByVersionInfo( eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo, $languages = null )
    {
        $returnValue = $this->service->loadContentByVersionInfo( $versionInfo, $languages );
        $this->signalDispatcher()->emit(
            new Signal\ContentService\LoadContentByVersionInfoSignal( $versionInfo, $languages )
        );
        return $returnValue;
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
     * @param array $languages A language filter for fields. If not given all languages are returned
     * @param int $versionNo the version number. If not given the current version is returned.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function loadContent( $contentId, $languages = null, $versionNo = null )
    {
        $returnValue = $this->service->loadContent( $contentId, $languages, $versionNo );
        $this->signalDispatcher()->emit(
            new Signal\ContentService\LoadContentSignal( $contentId, $languages, $versionNo )
        );
        return $returnValue;
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
    public function loadContentByRemoteId( $remoteId, $languages = null, $versionNo = null )
    {
        $returnValue = $this->service->loadContentByRemoteId( $remoteId, $languages, $versionNo );
        $this->signalDispatcher()->emit(
            new Signal\ContentService\LoadContentByRemoteIdSignal( $remoteId, $languages, $versionNo )
        );
        return $returnValue;
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
     * @param array $locationCreateStructs an array of {@link \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct} for each location parent under which a location should be created for the content
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content - the newly created content draft
     */
    public function createContent( eZ\Publish\API\Repository\Values\Content\ContentCreateStruct $contentCreateStruct, $locationCreateStructs = array() )
    {
        $returnValue = $this->service->createContent( $contentCreateStruct, $locationCreateStructs );
        $this->signalDispatcher()->emit(
            new Signal\ContentService\CreateContentSignal( $contentCreateStruct, $locationCreateStructs )
        );
        return $returnValue;
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
    public function updateContentMetadata( eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo, eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct $contentMetadataUpdateStruct )
    {
        $returnValue = $this->service->updateContentMetadata( $contentInfo, $contentMetadataUpdateStruct );
        $this->signalDispatcher()->emit(
            new Signal\ContentService\UpdateContentMetadataSignal( $contentInfo, $contentMetadataUpdateStruct )
        );
        return $returnValue;
    }

    /**
     * deletes a content object including all its versions and locations including their subtrees.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to delete the content (in one of the locations of the given content object)
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     */
    public function deleteContent( eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo )
    {
        $returnValue = $this->service->deleteContent( $contentInfo );
        $this->signalDispatcher()->emit(
            new Signal\ContentService\DeleteContentSignal( $contentInfo )
        );
        return $returnValue;
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
    public function createContentDraft( eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo, eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo = null, eZ\Publish\API\Repository\Values\User\User $user = null )
    {
        $returnValue = $this->service->createContentDraft( $contentInfo, $versionInfo, $user );
        $this->signalDispatcher()->emit(
            new Signal\ContentService\CreateContentDraftSignal( $contentInfo, $versionInfo, $user )
        );
        return $returnValue;
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
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo[] the drafts ({@link VersionInfo}) owned by the given user
     */
    public function loadContentDrafts( eZ\Publish\API\Repository\Values\User\User $user = null )
    {
        $returnValue = $this->service->loadContentDrafts( $user );
        $this->signalDispatcher()->emit(
            new Signal\ContentService\LoadContentDraftsSignal( $user )
        );
        return $returnValue;
    }

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
    public function translateVersion( eZ\Publish\API\Repository\Values\Content\TranslationInfo $translationInfo, eZ\Publish\API\Repository\Values\Content\TranslationValues $translationValues, eZ\Publish\API\Repository\Values\User\User $user = null )
    {
        $returnValue = $this->service->translateVersion( $translationInfo, $translationValues, $user );
        $this->signalDispatcher()->emit(
            new Signal\ContentService\TranslateVersionSignal( $translationInfo, $translationValues, $user )
        );
        return $returnValue;
    }

    /**
     * Updates the fields of a draft.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to update this version
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException if the version is not a draft
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException if a field in the $contentUpdateStruct is not valid
     * @throws \eZ\Publish\API\Repository\Exceptions\ContentValidationException if a required field is set to an empty value
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if a field value is not accepted by the field type
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct $contentUpdateStruct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content the content draft with the updated fields
     */
    public function updateContent( eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo, eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct $contentUpdateStruct )
    {
        $returnValue = $this->service->updateContent( $versionInfo, $contentUpdateStruct );
        $this->signalDispatcher()->emit(
            new Signal\ContentService\UpdateContentSignal( $versionInfo, $contentUpdateStruct )
        );
        return $returnValue;
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
    public function publishVersion( eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo )
    {
        $returnValue = $this->service->publishVersion( $versionInfo );
        $this->signalDispatcher()->emit(
            new Signal\ContentService\PublishVersionSignal( $versionInfo )
        );
        return $returnValue;
    }

    /**
     * removes the given version
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException if the version is in state published
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to remove this version
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     */
    public function deleteVersion( eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo )
    {
        $returnValue = $this->service->deleteVersion( $versionInfo );
        $this->signalDispatcher()->emit(
            new Signal\ContentService\DeleteVersionSignal( $versionInfo )
        );
        return $returnValue;
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
    public function loadVersions( eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo )
    {
        $returnValue = $this->service->loadVersions( $contentInfo );
        $this->signalDispatcher()->emit(
            new Signal\ContentService\LoadVersionsSignal( $contentInfo )
        );
        return $returnValue;
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
    public function copyContent( eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo, eZ\Publish\API\Repository\Values\Content\LocationCreateStruct $destinationLocationCreateStruct, eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo = null )
    {
        $returnValue = $this->service->copyContent( $contentInfo, $destinationLocationCreateStruct, $versionInfo );
        $this->signalDispatcher()->emit(
            new Signal\ContentService\CopyContentSignal( $contentInfo, $destinationLocationCreateStruct, $versionInfo )
        );
        return $returnValue;
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
    public function loadRelations( eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo )
    {
        $returnValue = $this->service->loadRelations( $versionInfo );
        $this->signalDispatcher()->emit(
            new Signal\ContentService\LoadRelationsSignal( $versionInfo )
        );
        return $returnValue;
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
    public function loadReverseRelations( eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo )
    {
        $returnValue = $this->service->loadReverseRelations( $contentInfo );
        $this->signalDispatcher()->emit(
            new Signal\ContentService\LoadReverseRelationsSignal( $contentInfo )
        );
        return $returnValue;
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
    public function addRelation( eZ\Publish\API\Repository\Values\Content\VersionInfo $sourceVersion, eZ\Publish\API\Repository\Values\Content\ContentInfo $destinationContent )
    {
        $returnValue = $this->service->addRelation( $sourceVersion, $destinationContent );
        $this->signalDispatcher()->emit(
            new Signal\ContentService\AddRelationSignal( $sourceVersion, $destinationContent )
        );
        return $returnValue;
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
    public function deleteRelation( eZ\Publish\API\Repository\Values\Content\VersionInfo $sourceVersion, eZ\Publish\API\Repository\Values\Content\ContentInfo $destinationContent )
    {
        $returnValue = $this->service->deleteRelation( $sourceVersion, $destinationContent );
        $this->signalDispatcher()->emit(
            new Signal\ContentService\DeleteRelationSignal( $sourceVersion, $destinationContent )
        );
        return $returnValue;
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
    public function addTranslationInfo( eZ\Publish\API\Repository\Values\Content\TranslationInfo $translationInfo )
    {
        $returnValue = $this->service->addTranslationInfo( $translationInfo );
        $this->signalDispatcher()->emit(
            new Signal\ContentService\AddTranslationInfoSignal( $translationInfo )
        );
        return $returnValue;
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
    public function loadTranslationInfos( eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo, $filter = array() )
    {
        $returnValue = $this->service->loadTranslationInfos( $contentInfo, $filter );
        $this->signalDispatcher()->emit(
            new Signal\ContentService\LoadTranslationInfosSignal( $contentInfo, $filter )
        );
        return $returnValue;
    }

    /**
     * Instantiates a new content create struct object
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param string $mainLanguageCode
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentCreateStruct
     */
    public function newContentCreateStruct( eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType, $mainLanguageCode )
    {
        $returnValue = $this->service->newContentCreateStruct( $contentType, $mainLanguageCode );
        $this->signalDispatcher()->emit(
            new Signal\ContentService\NewContentCreateStructSignal( $contentType, $mainLanguageCode )
        );
        return $returnValue;
    }

    /**
     * Instantiates a new content meta data update struct
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct
     */
    public function newContentMetadataUpdateStruct()
    {
        $returnValue = $this->service->newContentMetadataUpdateStruct();
        $this->signalDispatcher()->emit(
            new Signal\ContentService\NewContentMetadataUpdateStructSignal()
        );
        return $returnValue;
    }

    /**
     * Instantiates a new content update struct
     * @return \eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct
     */
    public function newContentUpdateStruct()
    {
        $returnValue = $this->service->newContentUpdateStruct();
        $this->signalDispatcher()->emit(
            new Signal\ContentService\NewContentUpdateStructSignal()
        );
        return $returnValue;
    }

    /**
     * Instantiates a new TranslationInfo object
     * @return \eZ\Publish\API\Repository\Values\Content\TranslationInfo
     */
    public function newTranslationInfo()
    {
        $returnValue = $this->service->newTranslationInfo();
        $this->signalDispatcher()->emit(
            new Signal\ContentService\NewTranslationInfoSignal()
        );
        return $returnValue;
    }

    /**
     * Instantiates a Translation object
     * @return \eZ\Publish\API\Repository\Values\Content\TranslationValues
     */
    public function newTranslationValues()
    {
        $returnValue = $this->service->newTranslationValues();
        $this->signalDispatcher()->emit(
            new Signal\ContentService\NewTranslationValuesSignal()
        );
        return $returnValue;
    }

}

