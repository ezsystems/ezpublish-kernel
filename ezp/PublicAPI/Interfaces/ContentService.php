<?php
/**
 * @package ezp\PublicAPI\Interfaces
 */
namespace ezp\PublicAPI\Interfaces;

use ezp\PublicAPI\Values\Content\Struct;
use ezp\PublicAPI\Values\Content\ContentUpdateStruct;
use ezp\PublicAPI\Values\Content\Content;
use ezp\PublicAPI\Values\Content\LocationCreateStruct;
use ezp\PublicAPI\Values\Content\Version;
use ezp\PublicAPI\Values\Content\VersionInfo;
use ezp\PublicAPI\Values\Content\VersionUpdateStruct;
use ezp\PublicAPI\Values\Content\Query;
use ezp\PublicAPI\Values\Content\SearchResult;
use ezp\PublicAPI\Values\Content\Relation;
use ezp\PublicAPI\Values\Content\TranslationInfo;
use ezp\PublicAPI\Values\ContentType\ContentType;
use ezp\PublicAPI\Values\Content\Translation;

/**
 * This class provides service methods for managing content
 *
 * @example Examples/content.php
 *
 * @package ezp\PublicAPI\Interfaces
 */
interface ContentService {

    /**
     * Loads a content object - to load fields use loadVersion
     *
     * @param int $contentId
     *
     * @return Content
     *
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to read the content
     * @throws ezp\PublicAPI\Interfaces\NotFoundExceptoin - if the content with the given id does not exist
     */
    public function loadContent($contentId);

    /**
     * Loads a content info object for the given remoteId - to load fields use loadVersion
     *
     * @param string $remoteId
     *
     * @return Content
     *
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowd to create the content in the given location
     * @throws ezp\PublicAPI\Interfaces\NotFoundExceptoin - if the content with the given remote id does not exist
     */
    public function loadContentByRemoteId($remoteId);

    /**
     * loads a version info of the given content object. If no version number is given, the method returns the current version
     *
     * @param Content $content
     * @param int $versionNo the version number. If not given the current version is returned.
     *
     * @return VersionInfo
     *
     * @throws ezp\PublicAPI\Interfaces\NotFoundExceptoin - if the version with the given number does not exist
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to load this version
     */
    public function loadVersionInfo(/*Content*/ $content, $versionNo = null);

    /**
     * loads a version info of the given content object id. If no version number is given, the method returns the current version
     *
     * @param int $contentId
     * @param int $versionNo the version number. If not given the current version is returned.
     *
     * @return VersionInfo
     *
     * @throws ezp\PublicAPI\Interfaces\NotFoundExceptoin - if the version with the given number does not exist
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to load this version
     */
    public function loadVersionInfoById($contentId, $versionNo = null);

    /**
     * loads a version of the given content object. If no version number is given, the method returns the current version
     *
     * @param Content $content
     * @param array $languages A language filter for fields. If not given all languages are returned
     * @param int $versionNo the version number. If not given the current version is returned.
     *
     * @return Version
     *
     * @throws ezp\PublicAPI\Interfaces\NotFoundExceptoin - if version with the given number does not exist
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to load this version
     */
    public function loadVersionByContent(/*Content*/ $content, array $languages = null, $versionNo = null);

    /**
     * loads a version of the given version info.
     *
     * @param VersionInfo $versionInfo
     * @param array $languages A language filter for fields. If not given all languages are returned
     *
     * @return Version
     *
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to load this version
     */
    public function loadVersionByVersionInfo(/*VersionInfo*/ $versionInfo, array $languages = null);

    /**
     * loads a version of the given content object. If no version number is given, the method returns the current version
     *
     * @param int $contentId
     * @param array $languages A language filter for fields. If not given all languages are returned
     * @param int $versionNo the version number. If not given the current version is returned.
     *
     * @return Version
     *
     * @throws ezp\PublicAPI\Interfaces\NotFoundExceptoin - if the content or version with the given id does not exist
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to load this version
     */
    public function loadVersion($contentId, array $languages = null, $versionNo = null);

    /**
     * loads a version of the given content object reference by a remote id.
     * If no version is given, the method returns the current version
     *
     * @param string $remoteId
     * @param array $languages A language filter for fields. If not given all languages are returned
     * @param int $versionNo the version number. If not given the current version is returned.
     *
     * @return Version
     *
     * @throws ezp\PublicAPI\Interfaces\NotFoundExceptoin - if the content or version with the given remote id does not exist
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to load this version
     */
    public function loadVersionByRemoteId($remoteId, array $languages = null, $versionNo = null);

    /**
     * Creates a new content draft assigned to the authenticated user.
     * If a different userId is given in the input it is assigned to the given user
     * but this required special rights for the authenticated user
     * (this is useful for content staging where the transfer process does not
     * have to authenticate with the user which created the content object in the source server).
     * The user has to publish the draft if it should be visible.
     * In 4.x at least one location has to be provided in the location creation array.
     *
     * @param ContentCreateStruct $contentCreateStruct
     * @param array $locationCreateStructs an array of {@link LocationCreateStruct} for each location parent under which a location should be created for the nontent
     *
     * @return Version - the newly created content draft
     *
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowd to create the content in the given location
     * @throws ezp\PublicAPI\Interfaces\InvalidArgumentException if the input is not valid or
     *         if the provided remoteId existis in the system or (4.x) there is no location provided
     */
    public function createContentDraft(/*ContentCreateStruct*/ $contentCreateStruct, array $locationCreateStructs = array());

    /**
     * Updates the metadata (see {@link ContentUpdateStruct}) of a content object - to update fields use updateVersion
     *
     * @param Content $content
     * @param ContentUpdateStruct $contentUpdateStruct
     *
     * @return Content the content with the updated attributes
     *
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowd to update the content meta data
     * @throws ezp\PublicAPI\Interfaces\InvalidArgumentException if the input is not valid
     */
    public function updateContent(/*Content*/ $content, /*ContentUpdateStruct*/ $contentUpdateStruct);

    /**
     * deletes a content object including all its versions and locations including their subtrees.
     *
     * @param Content $content
     *
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowd to delete the content (in one of the locations of the given content object)
     */
    public function deleteContent(/*Content*/ $content);

    /**
     * creates a draft from a publshed or archived version. If no version is given, the current published version is used.
     * 4.x: The draft is created with the initialLanguge code of the source version or if not present with the main language.
     * It can be changed on updating the version.
     *
     * @param Content $content
     * @param VersionInfo $versionInfo
     *
     * @return VersionInfo - the newly created version
     *
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to create the draft
     * @throws ezp\PublicAPI\Interfaces\BadStateException if there is no published version or the version info points to a draft
     */
    public function createDraftFromContent(/*Content*/ $content, /*VersionInfo*/ $versionInfo = null);

    /**
     * Load drafts for the given user or if null for the authenticated user
     * @param User $user
     *
     * @return array the drafts ({@link VersionInfo}) owned by the given user
     *
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to load the draft list
     */
    public function loadContentDrafts(User $user = null);


    /**
     * 5.x updates the destination version given in $translation->translationInfo with the provided tranlated fields
     *
     * @example Examples/translation_5x.php
     *
     * @param Translation $translation
     *
     * @return Version the version with the translated fields
     *
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to update this version
     * @throws ezp\PublicAPI\Interfaces\BadStateException if the given destiantioon version is not a draft
     */
    public function translateVersion( /*Translation*/ $translation);

    /**
     * Updates the fields of a draft.
     *
     * @param VersionInfo $versionInfo
     * @param VersionUpdateStruct $versionUpdateStruct
     *
     * @return Version the version with the updated fields
     *
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to update this version
     * @throws ezp\PublicAPI\Interfaces\BadStateException if the version is not a draft
     */
    public function updateVersion(/*VersionInfo*/ $versionInfo, /*VersionUpdateStruct*/ $versionUpdateStruct);

    /**
     * Publishes a draft
     *
     * @param VersionInfo $versionInfo
     *
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to publish this version
     * @throws ezp\PublicAPI\Interfaces\BadStateException if the version is not a draft
     */
    public function publishDraft( /*VersionInfo*/ $versionInfo );

    /**
     * removes the given version
     *
     * @param VersionInfo $versionInfo
     *
     * @throws ezp\PublicAPI\Interfaces\BadStateException if the version is in state published
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to remove this version
     */
    public function deleteVersion(/*VersionInfo*/ $versionInfo);

    /**
     * Loads all versions for the given content
     *
     * @param Content $content
     *
     * @return array an array of {@link VersionInfo} sorted by creation date
     *
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to list versions
     */
    public function loadVersions(/*Content*/ $content);

    /**
     * copies the the content to a new location. If no version is given,
     * all versions are copied, otherwise only the given version.
     *
     * @param Content $content
     * @param LocationCreateStruct $locationCreateStruct the target location where the content is copied to
     * @param VersionInfo $versionInfo
     *
     * @return Version
     *
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to copy the content to the given location
     */
    public function copyContent(/*Content*/ $content,/*LocationCreate*/ $locationCreateStruct,/*VersionInfo*/ $versionInfo = null);

    /**
     *
     * finds content objects for the given query.
     * @TODO define structs for the field filters
     *
     * @param Query $query
     * @param array  $fieldFilters - a map of filters for the returned fields.
     *        Currently supported: <code>array("languages" => aaray(<language1>,..))</code>.
     * @param boolean $filterOnUserPermissions if true only the objects which is the user allowed to read are returned.
     *
     * @return SearchResult
     */
    public function findContent( /*Query*/ $query, array $fieldFilters,  $filterOnUserPermissions = true );

    /**
     * @TODO define structs for the field filters
     * @param Query $query
     * @param array  $fieldFilters - a map of filters for the returned fields.
     *        Currently supported: <code>array("languages" => aaray(<language1>,..))</code>.
     * @param boolean $filterOnUserPermissions if true only the objects which is the user allowed to read are returned.
     *
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to read the found content object
     * @TODO throw an exception if the found object count is > 1
     *
     * @return SearchResult
     */
    public function findSingle( /*Query*/ $query, array $fieldFilters, $filterOnUserPermissions = true );

    /**
     * load all outgoing relations for the given version
     *
     * @param $versionInfo
     *
     * @return array an array of {@link Relation}
     */
    public function loadOutgoingRelations(/*VersionInfo*/ $versionInfo);

    /**
     * Loads all incoming relations for a content object. The  relations come only
     * from published versions of the source content objects
     *
     * @param Content $content
     *
     * @return array an array of {@link Relation}
     */
    public function loadIncomingRelations(/*Content*/ $content);

    /**
     * Adds a relation of type common
     *
     * @param VersionInfo $versionInfo
     * @param int $destinationId the destination of the relation
     *
     * @return Relation the newly created relation
     *
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed to edit this version
     * @throws ezp\PublicAPI\Interfaces\BadStateException if the version is not a draft
     */
    public function addRelation(/*VersionInfo*/ $versionInfo, $destinationId);

    /**
     * Removes a relation of type COMMON from a draft.
     *
     * @param VersionInfo $versionInfo
     * @param int $destinationId
     *
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed edit this version
     * @throws ezp\PublicAPI\Interfaces\BadStateException if the version is not a draft
     */
    public function deleteRelation(/*VersionInfo*/ $versionInfo, $destinationId);

    /**
     * 5.x add translation information to the content object
     *  
     * @example Examples/translation_5x.php
     *
     * @param TranslationInfo $translatio9nInfo
     *
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed add a trnaslation info
     */
    public function addTranslationInfo(/*TranslationInfo*/ $translatio9nInfo);

    /**
     * 5.x lists the translations done on this content object
     *
     * @param Content $content
     * @param array $filter TBD - filter by sourceversion destination version and languages
     *
     * @throws ezp\PublicAPI\Interfaces\UnauthorizedException if the user is not allowed read trnaslation infos
     *
     * @return TranslationInfo
     */
    public function loadTranslationInfos(/*Content*/ $content, array $filter = array() );


    /**
     * instanciates a new content type creat class
     *
     * @param  ContentType $contentType
     * @param string $mainLanguageCode
     *
     * @return ContentCreateStruct
     */
    public function newContentCreateStruct(/*ContentType*/ $contentType, $mainLanguageCode);

    /**
     * instanciates a new version update class
     *
     * @return VersionUpdateStruct
     */
    public function newVersionUpdateStruct();

    /**
     * instanciates a new content update struct
     * @return ContentUpdateStruct
     */
    public function newContentUpdateStruct();

    /**
     * instanciating a new TranslationInfo
     * @return TranslationInfo
     */
    public function newTranlationInfo();
    
    /**
     * instanciates a Translation class
     * @return Translation
     */
    public function newTranslation($translationInfo);


}