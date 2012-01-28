<?php
/**
 * @package ezp\PublicAPI\Interfaces
 */
namespace ezp\PublicAPI\Interfaces;

use ezp\PublicAPI\Values\ContentType\ContentType;
use ezp\PublicAPI\Values\Content\Query;
use ezp\PublicAPI\Values\Content\TranslationInfo;
use ezp\PublicAPI\Values\Content\TranslationValues;
use ezp\PublicAPI\Values\Content\ContentCreateStruct;
use ezp\PublicAPI\Values\Content\ContentMetaDataUpdateStruct;
use ezp\PublicAPI\Values\Content\VersionInfo;
use ezp\PublicAPI\Values\Content\Content;
use ezp\PublicAPI\Values\Content\ContentInfo;


/**
 * This class provides service methods for managing content
 *
 * @example Examples/content.php
 *
 * @package ezp\PublicAPI\Interfaces
 */
interface ContentService
{

    /**
     * Loads a content info object. 
     * 
     * To load fields use loadContent
     *
     * @param int $contentId
     *
     * @return \ezp\PublicAPI\Values\Content\ContentInfo
     *
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to read the content
     * @throws \ezp\PublicAPI\Interfaces\NotFoundExceptoin - if the content with the given id does not exist
     */
    public function loadContentInfo( $contentId );

    /**
     * Loads a content info object for the given remoteId. 
     * 
     * To load fields use loadContent
     *
     * @param string $remoteId
     *
     * @return \ezp\PublicAPI\Values\Content\ContentInfo
     *
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowd to create the content in the given location
     * @throws \ezp\PublicAPI\Interfaces\NotFoundExceptoin - if the content with the given remote id does not exist
     */
    public function loadContenInfotByRemoteId( $remoteId );

    /**
     * loads a version info of the given content object. 
     * 
     * If no version number is given, the method returns the current version
     *
     * @param \ezp\PublicAPI\Values\Content\ContentInfo $contentInfo
     * @param int $versionNo the version number. If not given the current version is returned.
     *
     * @return \ezp\PublicAPI\Values\Content\VersionInfo
     *
     * @throws \ezp\PublicAPI\Interfaces\NotFoundExceptoin - if the version with the given number does not exist
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to load this version
     */
    public function loadVersionInfo( ContentInfo $contentInfo, $versionNo = null );

    /**
     * loads a version info of the given content object id. 
     * 
     * If no version number is given, the method returns the current version
     *
     * @param int $contentId
     * @param int $versionNo the version number. If not given the current version is returned.
     *
     * @return \ezp\PublicAPI\Values\Content\VersionInfo
     *
     * @throws \ezp\PublicAPI\Interfaces\NotFoundExceptoin - if the version with the given number does not exist
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to load this version
     */
    public function loadVersionInfoById( $contentId, $versionNo = null );

    /**
     * loads content in a version for the given content info object. 
     * 
     * If no version number is given, the method returns the current version
     *
     * @param \ezp\PublicAPI\Values\Content\ContentInfo $contentInfo
     * @param array $languages A language filter for fields. If not given all languages are returned
     * @param int $versionNo the version number. If not given the current version is returned.
     *
     * @return \ezp\PublicAPI\Values\Content\Content
     *
     * @throws \ezp\PublicAPI\Interfaces\NotFoundException - if version with the given number does not exist
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to load this version
     */
    public function loadContentByContentInfo( ContentInfo $contentInfo, array $languages = null, $versionNo = null );

    /**
     * loads content in the version given by version info.
     *
     * @param \ezp\PublicAPI\Values\Content\VersionInfo $versionInfo
     * @param array $languages A language filter for fields. If not given all languages are returned
     *
     * @return \ezp\PublicAPI\Values\Content\Content
     *
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to load this version
     */
    public function loadContentByVersionInfo( VersionInfo $versionInfo, array $languages = null );

    /**
     * loads content in a version of the given content object. 
     * 
     * If no version number is given, the method returns the current version
     *
     * @param int $contentId
     * @param array $languages A language filter for fields. If not given all languages are returned
     * @param int $versionNo the version number. If not given the current version is returned.
     *
     * @return \ezp\PublicAPI\Values\Content\Content
     *
     * @throws \ezp\PublicAPI\Interfaces\NotFoundExceptoin - if the content or version with the given id does not exist
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to load this version
     */
    public function loadContent( $contentId, array $languages = null, $versionNo = null );

    /**
     * loads content in a version for the content object reference by the given remote id.
     * 
     * If no version is given, the method returns the current version
     *
     * @param string $remoteId
     * @param array $languages A language filter for fields. If not given all languages are returned
     * @param int $versionNo the version number. If not given the current version is returned.
     *
     * @return \ezp\PublicAPI\Values\Content\Content
     *
     * @throws \ezp\PublicAPI\Interfaces\NotFoundExceptoin - if the content or version with the given remote id does not exist
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to load this version
     */
    public function loadVersionByRemoteId( $remoteId, array $languages = null, $versionNo = null );

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
     * @param \ezp\PublicAPI\Values\Content\ContentCreateStruct $contentCreateStruct
     * @param array $locationCreateStructs an array of {@link \ezp\PublicAPI\Values\Content\LocationCreateStruct} for each location parent under which a location should be created for the content
     *
     * @return \ezp\PublicAPI\Values\Content\Content - the newly created content draft
     *
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to create the content in the given location
     * @throws \ezp\PublicAPI\Interfaces\InvalidArgumentException if the input is not valid or
     *         if the provided remoteId exists in the system or (4.x) there is no location provided
     */
    public function createContent( ContentCreateStruct $contentCreateStruct, array $locationCreateStructs = array() );

    /**
     * Updates the metadata. 
     * 
     * (see {@link ContentMetadataUpdateStruct}) of a content object - to update fields use updateContent
     *
     * @param \ezp\PublicAPI\Values\Content\ContentInfo $contentInfo
     * @param \ezp\PublicAPI\Values\Content\ContentMetadataUpdateStruct $contentMetadataUpdateStruct
     *
     * @return \ezp\PublicAPI\Values\Content\Content the content with the updated attributes
     *
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowd to update the content meta data
     * @throws \ezp\PublicAPI\Interfaces\InvalidArgumentException if the input is not valid
     */
    public function updateContentMetadata( ContentInfo $contentInfo, ContentMetaDataUpdateStruct $contentMetadataUpdateStruct );

    /**
     * deletes a content object including all its versions and locations including their subtrees.
     *
     * @param \ezp\PublicAPI\Values\Content\ContentInfo $contentInfo
     *
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowd to delete the content (in one of the locations of the given content object)
     */
    public function deleteContent( ContentInfo $contentInfo );

    /**
     * creates a draft from a publshed or archived version. 
     * 
     * If no version is given, the current published version is used.
     * 4.x: The draft is created with the initialLanguge code of the source version or if not present with the main language.
     * It can be changed on updating the version.
     *
     * @param \ezp\PublicAPI\Values\Content\ContentInfo $contentInfo
     * @param \ezp\PublicAPI\Values\Content\VersionInfo $versionInfo
     *
     * @return \ezp\PublicAPI\Values\Content\Content - the newly created content draft
     *
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to create the draft
     * @throws \ezp\PublicAPI\Interfaces\BadStateException if there is no published version or the version info points to a draft
     */
    public function createDraftFromContent( ContentInfo $contentInfo, VersionInfo $versionInfo = null );

    /**
     * Load drafts for a user. 
     * 
     * If no user is given the drafts for the authenticated user a returned
     * 
     * @param User $user
     *
     * @return \ezp\PublicAPI\Values\Content\VersionInfo the drafts ({@link VersionInfo}) owned by the given user
     *
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to load the draft list
     */
    public function loadContentDrafts( User $user = null );


    /**
     * Translate a version
     * 
     * updates the destination version given in $translationInfo with the provided translated fields in $translationValues
     *
     * @example Examples/translation_5x.php
     *
     * @param \ezp\PublicAPI\Values\Content\TranslationInfo $translationInfo 
     * @param \ezp\PublicAPI\Values\Content\TranslationValues $translationValues 
     * @param \ezp\PublicAPI\Values\User\User $user If set, this user is taken as modifier of the version 
     *
     * @return \ezp\PublicAPI\Values\Content\Content the content draft with the translated fields
     *
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to update this version
     * @throws \ezp\PublicAPI\Interfaces\BadStateException if the given destiantioon version is not a draft
     * 
     * @since 5.0
     */
    public function translateVersion( TranslationInfo $translationInfo, TranslationValues $translationValues, User $user = null );

    /**
     * Updates the fields of a draft.
     *
     * @param \ezp\PublicAPI\Values\Content\VersionInfo $versionInfo
     * @param \ezp\PublicAPI\Values\Content\ContentUpdateStruct $contnetUpdateStruct
     *
     * @return \ezp\PublicAPI\Values\Content\Content the content draft with the updated fields
     *
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to update this version
     * @throws \ezp\PublicAPI\Interfaces\BadStateException if the version is not a draft
     */
    public function updateContent( VersionInfo $versionInfo, ContentUpdateStruct $contnetUpdateStruct );

    /**
     * Publishes a content version
     *
     * @param \ezp\PublicAPI\Values\Content\VersionInfo $versionInfo
     *
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to publish this version
     * @throws \ezp\PublicAPI\Interfaces\BadStateException if the version is already in published state
     */
    public function publishVersion( VersionInfo $versionInfo );

    /**
     * removes the given version
     *
     * @param \ezp\PublicAPI\Values\Content\VersionInfo $versionInfo
     *
     * @throws \ezp\PublicAPI\Interfaces\BadStateException if the version is in state published
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to remove this version
     */
    public function deleteVersion( VersionInfo $versionInfo );

    /**
     * Loads all versions for the given content
     *
     * @param \ezp\PublicAPI\Values\Content\ContentInfo $contentInfo
     *
     * @return \ezp\PublicAPI\Values\Content\VersionInfo[] an array of {@link \ezp\PublicAPI\Values\Content\VersionInfo} sorted by creation date
     *
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to list versions
     */
    public function loadVersions( ContentInfo $contentInfo );

    /**
     * copies the content to a new location. If no version is given,
     * all versions are copied, otherwise only the given version.
     *
     * @param \ezp\PublicAPI\Values\Content\ContentInfo $contentInfo
     * @param \ezp\PublicAPI\Values\Content\LocationCreateStruct $destinationLocationCreateStruct the target location where the content is copied to
     * @param \ezp\PublicAPI\Values\Content\ersionInfo $versionInfo
     *
     * @return \ezp\PublicAPI\Values\Content\Content
     *
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to copy the content to the given location
     */
    public function copyContent( ContentInfo $contentInfo, LocationCreateStruct $destinationLocationCreateStruct, VersionInfo $versionInfo = null);

    /**
     * finds content objects for the given query.
     * 
     * @TODO define structs for the field filters
     *
     * @param \ezp\PublicAPI\Values\Content\Query $query
     * @param array  $fieldFilters - a map of filters for the returned fields.
     *        Currently supported: <code>array("languages" => array(<language1>,..))</code>.
     * @param boolean $filterOnUserPermissions if true only the objects which is the user allowed to read are returned.
     *
     * @return \ezp\PublicAPI\Values\Content\SearchResult
     */
    public function findContent( Query $query, array $fieldFilters,  $filterOnUserPermissions = true );

    /**
     * Performs a query for a single content object
     * 
     * @TODO define structs for the field filters
     * @param \ezp\PublicAPI\Values\Content\Query $query
     * @param array  $fieldFilters - a map of filters for the returned fields.
     *        Currently supported: <code>array("languages" => array(<language1>,..))</code>.
     * @param boolean $filterOnUserPermissions if true only the objects which is the user allowed to read are returned.
     *
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to read the found content object
     * @TODO throw an exception if the found object count is > 1
     *
     * @return \ezp\PublicAPI\Values\Content\SearchResult
     */
    public function findSingle( Query $query, array $fieldFilters, $filterOnUserPermissions = true );

    /**
     * load all outgoing relations for the given version
     *
     * @param \ezp\PublicAPI\Values\Content\VersionInfo $versionInfo
     *
     * @return \ezp\PublicAPI\Values\Content\Relation[] an array of {@link Relation}
     */
    public function loadRelations( VersionInfo $versionInfo );

    /**
     * Loads all incoming relations for a content object. 
     * 
     * The relations come only
     * from published versions of the source content objects
     *
     * @param \ezp\PublicAPI\Values\Content\ContentInfo $contentInfo
     *
     * @return \ezp\PublicAPI\Values\Content\Relation[] an array of {@link Relation}
     */
    public function loadReverseRelations( ContentInfo $content );

    /**
     * Adds a relation of type common. 
     * 
     * The source of the relation is the content and version
     * referenced by $versionInfo.
     *
     * @param \ezp\PublicAPI\Values\Content\VersionInfo $versionInfo
     * @param \ezp\PublicAPI\Values\Content\ContentInfo $destination the destination of the relation
     *
     * @return \ezp\PublicAPI\Values\Content\Relation the newly created relation
     *
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to edit this version
     * @throws \ezp\PublicAPI\Interfaces\BadStateException if the version is not a draft
     */
    public function addRelation( VersionInfo $versionInfo, ContentInfo $destination );

    /**
     * Removes a relation of type COMMON from a draft.
     *
     * @param \ezp\PublicAPI\Values\Content\VersionInfo $versionInfo
     * @param \ezp\PublicAPI\Values\Content\ContentInfo $destination
     *
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed edit this version
     * @throws \ezp\PublicAPI\Interfaces\BadStateException if the version is not a draft
     * @throws \ezp\PublicAPI\Interfaces\IllegalArgumentException if there is no relation of type COMMON for the given destination
     */
    public function deleteRelation( VersionInfo $versionInfo, ContentInfo $destination);

    /**
     * add translation information to the content object
     * 
     * @example Examples/translation_5x.php
     *
     * @param \ezp\PublicAPI\Values\Content\TranslationInfo $translationInfo
     *
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed add a translation info
     *
     * @since 5.0
     */
    public function addTranslationInfo( TranslationInfo $translationInfo );

    /**
     * lists the translations done on this content object
     *
     * @param \ezp\PublicAPI\Values\Content\ContentInfo $contentInfo
     * @param array $filter 
     * @todo TBD - filter by sourceversion destination version and languages
     *
     * @throws \ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed read trnaslation infos
     *
     * @return \ezp\PublicAPI\Values\Content\TranslationInfo[] an array of {@link TranslationInfo}
     * 
     * @since 5.0
     */
    public function loadTranslationInfos( ContentInfo $contentInfo, array $filter = array() );


    /**
     * Instantiates a new content create struct object
     *
     * @param \ezp\PublicAPI\Values\ContentType\ContentType $contentType
     * @param string $mainLanguageCode
     *
     * @return \ezp\PublicAPI\Values\Content\ContentCreateStruct
     */
    public function newContentCreateStruct( ContentType $contentType, $mainLanguageCode );

    /**
     * Instantiates a new content meta data update struct
     *
     * @return \ezp\PublicAPI\Values\Content\ContentMetadataUpdateStruct
     */
    public function newContentMetadataUpdateStruct();

    /**
     * Instantiates a new content update struct
     * @return \ezp\PublicAPI\Values\Content\ContentUpdateStruct
     */
    public function newContentUpdateStruct();

    /**
     * Instantiates a new TranslationInfo object
     * @return \ezp\PublicAPI\Values\Content\TranslationInfo
     */
    public function newTranslationInfo();

    /**
     * Instantiates a Translation object
     * @return \ezp\PublicAPI\Values\Content\Translation
     */
    public function newTranslation();
}
