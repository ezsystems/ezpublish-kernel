<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository;

use eZ\Publish\API\Repository\Values\Content\ContentDraftList;
use eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Values\Content\RelationList;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Relation;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;

/**
 * This class provides service methods for managing content.
 */
interface ContentService
{
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
    public function loadContentInfo(int $contentId): ContentInfo;

    /**
     * Bulk-load ContentInfo items by id's.
     *
     * Note: It does not throw exceptions on load, just skips erroneous (NotFound or Unauthorized) ContentInfo items.
     *
     * @param int[] $contentIds
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentInfo[] list of ContentInfo with Content Ids as keys
     */
    public function loadContentInfoList(array $contentIds): iterable;

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
    public function loadContentInfoByRemoteId(string $remoteId): ContentInfo;

    /**
     * Loads a version info of the given content object.
     *
     * If no version number is given, the method returns the current version
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException - if the version with the given number does not exist
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to load this version
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param int|null $versionNo the version number. If not given the current version is returned.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    public function loadVersionInfo(ContentInfo $contentInfo, ?int $versionNo = null): VersionInfo;

    /**
     * Loads a version info of the given content object id.
     *
     * If no version number is given, the method returns the current version
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException - if the version with the given number does not exist
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to load this version
     *
     * @param int $contentId
     * @param int|null $versionNo the version number. If not given the current version is returned.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo
     */
    public function loadVersionInfoById(int $contentId, ?int $versionNo = null): VersionInfo;

    /**
     * Loads content in a version for the given content info object.
     *
     * If no version number is given, the method returns the current version
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException - if version with the given number does not exist
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to load this version
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param array $languages A language priority, filters returned fields and is used as prioritized language code on
     *                         returned value object. If not given all languages are returned.
     * @param int|null $versionNo the version number. If not given the current version is returned from $contentInfo
     * @param bool $useAlwaysAvailable Add Main language to \$languages if true (default) and if alwaysAvailable is true
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function loadContentByContentInfo(ContentInfo $contentInfo, array $languages = null, ?int $versionNo = null, bool $useAlwaysAvailable = true): Content;

    /**
     * Loads content in the version given by version info.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to load this version
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     * @param string[] $languages A language priority, filters returned fields and is used as prioritized language code on
     *                         returned value object. If not given all languages are returned.
     * @param bool $useAlwaysAvailable Add Main language to \$languages if true (default) and if alwaysAvailable is true
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function loadContentByVersionInfo(VersionInfo $versionInfo, array $languages = null, bool $useAlwaysAvailable = true): Content;

    /**
     * Loads content in a version of the given content object.
     *
     * If no version number is given, the method returns the current version
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if the content or version with the given id and languages does not exist
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the user has no access to read content and in case of un-published content: read versions
     *
     * @param mixed $contentId
     * @param string[] $languages A language priority, filters returned fields and is used as prioritized language code on
     *                         returned value object. If not given all languages are returned.
     * @param int|null $versionNo the version number. If not given the current version is returned
     * @param bool $useAlwaysAvailable Add Main language to \$languages if true (default) and if alwaysAvailable is true
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function loadContent(int $contentId, array $languages = null, ?int $versionNo = null, bool $useAlwaysAvailable = true): Content;

    /**
     * Loads content in a version for the content object reference by the given remote id.
     *
     * If no version is given, the method returns the current version
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException - if the content or version with the given remote id does not exist
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the user has no access to read content and in case of un-published content: read versions
     *
     * @param string $remoteId
     * @param string[] $languages A language priority, filters returned fields and is used as prioritized language code on
     *                         returned value object. If not given all languages are returned.
     * @param int|null $versionNo the version number. If not given the current version is returned
     * @param bool $useAlwaysAvailable Add Main language to \$languages if true (default) and if alwaysAvailable is true
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function loadContentByRemoteId(string $remoteId, array $languages = null, ?int $versionNo = null, bool $useAlwaysAvailable = true): Content;

    /**
     * Bulk-load Content items by the list of ContentInfo Value Objects.
     *
     * Note: it does not throw exceptions on load, just ignores erroneous Content item.
     * Moreover, since the method works on pre-loaded ContentInfo list, it is assumed that user is
     * allowed to access every Content on the list.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo[] $contentInfoList
     * @param string[] $languages A language priority, filters returned fields and is used as prioritized language code on
     *                            returned value object. If not given all languages are returned.
     * @param bool $useAlwaysAvailable Add Main language to \$languages if true (default) and if alwaysAvailable is true,
     *                                 unless all languages have been asked for.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content[] list of Content items with Content Ids as keys
     */
    public function loadContentListByContentInfo(array $contentInfoList, array $languages = [], bool $useAlwaysAvailable = true): iterable;

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
     * @param \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct[] $locationCreateStructs an array of {@link \eZ\Publish\API\Repository\Values\Content\LocationCreateStruct} for each location parent under which a location should be created for the content
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content - the newly created content draft
     */
    public function createContent(ContentCreateStruct $contentCreateStruct, array $locationCreateStructs = []): Content;

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
    public function updateContentMetadata(ContentInfo $contentInfo, ContentMetadataUpdateStruct $contentMetadataUpdateStruct): Content;

    /**
     * Deletes a content object including all its versions and locations including their subtrees.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to delete the content (in one of the locations of the given content object)
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     *
     * @return int[] Affected Location Id's (List of Locations of the Content that was deleted)
     */
    public function deleteContent(ContentInfo $contentInfo): iterable;

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
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo|null $versionInfo
     * @param \eZ\Publish\API\Repository\Values\User\User|null $creator Used as creator of the draft if given - otherwise uses current-user
     * @param \eZ\Publish\API\Repository\Values\Content\Language|null if not set the draft is created with the initialLanguage code of the source version or if not present with the main language.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content - the newly created content draft
     */
    public function createContentDraft(
        ContentInfo $contentInfo,
        ?VersionInfo $versionInfo = null,
        ?User $creator = null,
        ?Language $language = null
    ): Content;

    /**
     * Counts drafts for a user.
     *
     * If no user is given the number of drafts for the authenticated user are returned
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user The user to load drafts for, if defined, otherwise drafts for current-user
     *
     * @return int The number of drafts ({@link VersionInfo}) owned by the given user
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function countContentDrafts(?User $user = null): int;

    /**
     * Loads drafts for a user.
     *
     * If no user is given the drafts for the authenticated user are returned
     *
     * @deprecated Please use {@see loadContentDraftList()} instead to avoid risking loading too much data.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the current-user is not allowed to load the draft list
     *
     * @param \eZ\Publish\API\Repository\Values\User\User $user The user to load drafts for, if defined, otherwise drafts for current-user
     *
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo[] the drafts ({@link VersionInfo}) owned by the given user
     */
    public function loadContentDrafts(?User $user = null): iterable;

    /**
     * Loads drafts for a user when content is not in the trash. The list is sorted by modification date.
     *
     * If no user is given the drafts for the authenticated user are returned
     *
     * @since 7.5.5
     *
     * @param \eZ\Publish\API\Repository\Values\User\User|null $user The user to load drafts for, if defined, otherwise drafts for current-user
     * @param int $offset
     * @param int $limit
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentDraftList
     */
    public function loadContentDraftList(?User $user = null, int $offset = 0, int $limit = -1): ContentDraftList;

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
    public function updateContent(VersionInfo $versionInfo, ContentUpdateStruct $contentUpdateStruct): Content;

    /**
     * Publishes a content version.
     *
     * Publishes a content version and deletes archive versions if they overflow max archive versions.
     * Max archive versions are currently a configuration for default max limit, by default set to 5.
     * @todo Introduce null|int ContentType->versionArchiveLimit to be able to let admins override this per type.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to publish this version
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException if the version is not a draft
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     * @param string[] $translations List of language codes of translations which will be included
     *                               in a published version.
     *                               By default all translations from the current version will be published.
     *                               If the list is provided but does not cover all currently published translations,
     *                               the missing ones will be copied from the currently published version,
     *                               overriding those in the current version.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function publishVersion(VersionInfo $versionInfo, array $translations = Language::ALL): Content;

    /**
     * Removes the given version.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException if the version is in
     *         published state or is a last version of Content in non draft state
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to remove this version
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     */
    public function deleteVersion(VersionInfo $versionInfo): void;

    /**
     * Loads all versions for the given content.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to list versions
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the given status is invalid
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param int|null $status
     *
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo[] an array of {@link \eZ\Publish\API\Repository\Values\Content\VersionInfo} sorted by creation date
     */
    public function loadVersions(ContentInfo $contentInfo, ?int $status = null): iterable;

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
    public function copyContent(ContentInfo $contentInfo, LocationCreateStruct $destinationLocationCreateStruct, ?VersionInfo $versionInfo = null): Content;

    /**
     * Loads all outgoing relations for the given version.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to read this version
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Relation[]
     */
    public function loadRelations(VersionInfo $versionInfo): iterable;

    /**
     * Counts all incoming relations for the given content object.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     *
     * @return int The number of reverse relations ({@link Relation})
     */
    public function countReverseRelations(ContentInfo $contentInfo): int;

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
    public function loadReverseRelations(ContentInfo $contentInfo): iterable;

    /**
     * Loads all incoming relations for a content object.
     *
     * The relations come only from published versions of the source content objects.
     * If the user is not allowed to read specific version then UnauthorizedRelationListItem is returned
     * {@link \eZ\Publish\API\Repository\Values\Content\RelationList\Item\UnauthorizedRelationListItem}
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param int $offset
     * @param int $limit
     *
     * @return \eZ\Publish\API\Repository\Values\Content\RelationList
     */
    public function loadReverseRelationList(ContentInfo $contentInfo, int $offset = 0, int $limit = -1): RelationList;

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
    public function addRelation(VersionInfo $sourceVersion, ContentInfo $destinationContent): Relation;

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
    public function deleteRelation(VersionInfo $sourceVersion, ContentInfo $destinationContent): void;

    /**
     * Remove Content Object translation from all Versions (including archived ones) of a Content Object.
     *
     * NOTE: this operation is risky and permanent, so user interface (ideally CLI) should provide
     *       a warning before performing it.
     *
     * @deprecated since 6.13, use {@see deleteTranslation} instead
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException if the specified translation
     *         is the only one a Version has or it is the main translation of a Content Object.
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed
     *         to delete the content (in one of the locations of the given Content Object).
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if languageCode argument
     *         is invalid for the given content.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param string $languageCode
     *
     * @since 6.11
     */
    public function removeTranslation(ContentInfo $contentInfo, string $languageCode): void;

    /**
     * Delete Content item Translation from all Versions (including archived ones) of a Content Object.
     *
     * NOTE: this operation is risky and permanent, so user interface should provide a warning before performing it.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException if the specified Translation
     *         is the Main Translation of a Content Item.
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed
     *         to delete the content (in one of the locations of the given Content Item).
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if languageCode argument
     *         is invalid for the given content.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     * @param string $languageCode
     *
     * @since 6.13
     */
    public function deleteTranslation(ContentInfo $contentInfo, string $languageCode): void;

    /**
     * Delete specified Translation from a Content Draft.
     *
     * When using together with ContentService::publishVersion() method, make sure to not provide deleted translation
     * in translations array, as it is going to be copied again from published version.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException if the specified Translation
     *         is the only one the Content Draft has or it is the main Translation of a Content Object.
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed
     *         to edit the Content (in one of the locations of the given Content Object).
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if languageCode argument
     *         is invalid for the given Draft.
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException if specified Version was not found
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo Content Version Draft
     * @param string $languageCode Language code of the Translation to be removed
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content Content Draft w/o the specified Translation
     *
     * @since 6.12
     */
    public function deleteTranslationFromDraft(VersionInfo $versionInfo, string $languageCode): Content;

    /**
     * Hides Content by making all the Locations appear hidden.
     * It does not persist hidden state on Location object itself.
     *
     * Content hidden by this API can be revealed by revealContent API.
     *
     * @see revealContent
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     */
    public function hideContent(ContentInfo $contentInfo): void;

    /**
     * Reveals Content hidden by hideContent API.
     * Locations which were hidden before hiding Content will remain hidden.
     *
     * @see hideContent
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     */
    public function revealContent(ContentInfo $contentInfo): void;

    /**
     * Instantiates a new content create struct object.
     *
     * alwaysAvailable is set to the ContentType's defaultAlwaysAvailable
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param string $mainLanguageCode
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentCreateStruct
     */
    public function newContentCreateStruct(ContentType $contentType, string $mainLanguageCode): ContentCreateStruct;

    /**
     * Instantiates a new content meta data update struct.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct
     */
    public function newContentMetadataUpdateStruct(): ContentMetadataUpdateStruct;

    /**
     * Instantiates a new content update struct.
     *
     * @return \eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct
     */
    public function newContentUpdateStruct(): ContentUpdateStruct;
}
