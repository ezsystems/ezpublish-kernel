<?php

/**
 * ContentService class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\SignalSlot;

use eZ\Publish\API\Repository\ContentService as ContentServiceInterface;
use eZ\Publish\API\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\Content\TranslationInfo;
use eZ\Publish\API\Repository\Values\Content\TranslationValues;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\Core\Repository\Decorator\ContentServiceDecorator;
use eZ\Publish\Core\SignalSlot\Signal\ContentService\CreateContentSignal;
use eZ\Publish\Core\SignalSlot\Signal\ContentService\DeleteTranslationSignal;
use eZ\Publish\Core\SignalSlot\Signal\ContentService\RemoveTranslationSignal;
use eZ\Publish\Core\SignalSlot\Signal\ContentService\UpdateContentMetadataSignal;
use eZ\Publish\Core\SignalSlot\Signal\ContentService\DeleteContentSignal;
use eZ\Publish\Core\SignalSlot\Signal\ContentService\CreateContentDraftSignal;
use eZ\Publish\Core\SignalSlot\Signal\ContentService\TranslateVersionSignal;
use eZ\Publish\Core\SignalSlot\Signal\ContentService\UpdateContentSignal;
use eZ\Publish\Core\SignalSlot\Signal\ContentService\PublishVersionSignal;
use eZ\Publish\Core\SignalSlot\Signal\ContentService\DeleteVersionSignal;
use eZ\Publish\Core\SignalSlot\Signal\ContentService\CopyContentSignal;
use eZ\Publish\Core\SignalSlot\Signal\ContentService\AddRelationSignal;
use eZ\Publish\Core\SignalSlot\Signal\ContentService\DeleteRelationSignal;
use eZ\Publish\Core\SignalSlot\Signal\ContentService\AddTranslationInfoSignal;

/**
 * ContentService class.
 */
class ContentService extends ContentServiceDecorator
{
    /**
     * SignalDispatcher.
     *
     * @var \eZ\Publish\Core\SignalSlot\SignalDispatcher
     */
    protected $signalDispatcher;

    /**
     * Constructor.
     *
     * Construct service object from aggregated service and signal
     * dispatcher
     *
     * @param \eZ\Publish\API\Repository\ContentService $service
     * @param \eZ\Publish\Core\SignalSlot\SignalDispatcher $signalDispatcher
     */
    public function __construct(ContentServiceInterface $service, SignalDispatcher $signalDispatcher)
    {
        parent::__construct($service);

        $this->signalDispatcher = $signalDispatcher;
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
    public function createContent(ContentCreateStruct $contentCreateStruct, array $locationCreateStructs = array())
    {
        $returnValue = $this->service->createContent($contentCreateStruct, $locationCreateStructs);
        $this->signalDispatcher->emit(
            new CreateContentSignal(
                array(
                    'contentId' => $returnValue->getVersionInfo()->getContentInfo()->id,
                    'versionNo' => $returnValue->getVersionInfo()->versionNo,
                )
            )
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
    public function updateContentMetadata(ContentInfo $contentInfo, ContentMetadataUpdateStruct $contentMetadataUpdateStruct)
    {
        $returnValue = $this->service->updateContentMetadata($contentInfo, $contentMetadataUpdateStruct);
        $this->signalDispatcher->emit(
            new UpdateContentMetadataSignal(
                array(
                    'contentId' => $contentInfo->id,
                )
            )
        );

        return $returnValue;
    }

    /**
     * Deletes a content object including all its versions and locations including their subtrees.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to delete the content (in one of the locations of the given content object)
     *
     * @param \eZ\Publish\API\Repository\Values\Content\ContentInfo $contentInfo
     *
     * @return mixed[] Affected Location Id's
     */
    public function deleteContent(ContentInfo $contentInfo)
    {
        $returnValue = $this->service->deleteContent($contentInfo);
        $this->signalDispatcher->emit(
            new DeleteContentSignal(
                array(
                    'contentId' => $contentInfo->id,
                    'affectedLocationIds' => $returnValue,
                )
            )
        );

        return $returnValue;
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
    public function createContentDraft(ContentInfo $contentInfo, VersionInfo $versionInfo = null, User $user = null)
    {
        $returnValue = $this->service->createContentDraft($contentInfo, $versionInfo, $user);
        $this->signalDispatcher->emit(
            new CreateContentDraftSignal(
                array(
                    'contentId' => $contentInfo->id,
                    'versionNo' => ($versionInfo !== null ? $versionInfo->versionNo : null),
                    'newVersionNo' => $returnValue->getVersionInfo()->versionNo,
                    'userId' => ($user !== null ? $user->id : null),
                )
            )
        );

        return $returnValue;
    }

    /**
     * Translate a version.
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
    public function translateVersion(TranslationInfo $translationInfo, TranslationValues $translationValues, User $user = null)
    {
        $returnValue = $this->service->translateVersion($translationInfo, $translationValues, $user);
        $this->signalDispatcher->emit(
            new TranslateVersionSignal(
                array(
                    'contentId' => $translationInfo->srcVersionInfo->contentInfo->id,
                    'versionNo' => $translationInfo->srcVersionInfo->versionNo,
                    'userId' => ($user !== null ? $user->id : null),
                )
            )
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
    public function updateContent(VersionInfo $versionInfo, ContentUpdateStruct $contentUpdateStruct)
    {
        $returnValue = $this->service->updateContent($versionInfo, $contentUpdateStruct);
        $this->signalDispatcher->emit(
            new UpdateContentSignal(
                array(
                    'contentId' => $versionInfo->getContentInfo()->id,
                    'versionNo' => $versionInfo->versionNo,
                )
            )
        );

        return $returnValue;
    }

    /**
     * Publishes a content version.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to publish this version
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException if the version is not a draft
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     *
     * @return \eZ\Publish\API\Repository\Values\Content\Content
     */
    public function publishVersion(VersionInfo $versionInfo)
    {
        $returnValue = $this->service->publishVersion($versionInfo);
        $this->signalDispatcher->emit(
            new PublishVersionSignal(
                array(
                    'contentId' => $versionInfo->getContentInfo()->id,
                    'versionNo' => $versionInfo->versionNo,
                )
            )
        );

        return $returnValue;
    }

    /**
     * Removes the given version.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException if the version is in
     *         published state or is the last version of the Content
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed to remove this version
     *
     * @param \eZ\Publish\API\Repository\Values\Content\VersionInfo $versionInfo
     */
    public function deleteVersion(VersionInfo $versionInfo)
    {
        $returnValue = $this->service->deleteVersion($versionInfo);
        $this->signalDispatcher->emit(
            new DeleteVersionSignal(
                array(
                    'contentId' => $versionInfo->contentInfo->id,
                    'versionNo' => $versionInfo->versionNo,
                )
            )
        );

        return $returnValue;
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
    public function copyContent(ContentInfo $contentInfo, LocationCreateStruct $destinationLocationCreateStruct, VersionInfo $versionInfo = null)
    {
        $returnValue = $this->service->copyContent($contentInfo, $destinationLocationCreateStruct, $versionInfo);
        $this->signalDispatcher->emit(
            new CopyContentSignal(
                array(
                    'srcContentId' => $contentInfo->id,
                    'srcVersionNo' => ($versionInfo !== null ? $versionInfo->versionNo : null),
                    'dstContentId' => $returnValue->getVersionInfo()->getContentInfo()->id,
                    'dstVersionNo' => $returnValue->getVersionInfo()->versionNo,
                    'dstParentLocationId' => $destinationLocationCreateStruct->parentLocationId,
                )
            )
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
    public function addRelation(VersionInfo $sourceVersion, ContentInfo $destinationContent)
    {
        $returnValue = $this->service->addRelation($sourceVersion, $destinationContent);
        $this->signalDispatcher->emit(
            new AddRelationSignal(
                array(
                    'srcContentId' => $sourceVersion->contentInfo->id,
                    'srcVersionNo' => $sourceVersion->versionNo,
                    'dstContentId' => $destinationContent->id,
                )
            )
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
    public function deleteRelation(VersionInfo $sourceVersion, ContentInfo $destinationContent)
    {
        $returnValue = $this->service->deleteRelation($sourceVersion, $destinationContent);
        $this->signalDispatcher->emit(
            new DeleteRelationSignal(
                array(
                    'srcContentId' => $sourceVersion->contentInfo->id,
                    'srcVersionNo' => $sourceVersion->versionNo,
                    'dstContentId' => $destinationContent->id,
                )
            )
        );

        return $returnValue;
    }

    /**
     * Adds translation information to the content object.
     *
     * @example Examples/translation_5x.php
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException if the user is not allowed add a translation info
     *
     * @param \eZ\Publish\API\Repository\Values\Content\TranslationInfo $translationInfo
     *
     * @since 5.0
     */
    public function addTranslationInfo(TranslationInfo $translationInfo)
    {
        $returnValue = $this->service->addTranslationInfo($translationInfo);
        $this->signalDispatcher->emit(
            new AddTranslationInfoSignal(array())
        );

        return $returnValue;
    }

    /**
     * {@inheritdoc}
     */
    public function removeTranslation(ContentInfo $contentInfo, $languageCode)
    {
        @trigger_error(
            __METHOD__ . ' is deprecated, use deleteTranslation instead',
            E_USER_DEPRECATED
        );
        $this->deleteTranslation($contentInfo, $languageCode);
    }

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
    public function deleteTranslation(ContentInfo $contentInfo, $languageCode)
    {
        $this->service->deleteTranslation($contentInfo, $languageCode);
        $this->signalDispatcher->emit(
            new RemoveTranslationSignal(['contentId' => $contentInfo->id, 'languageCode' => $languageCode])
        );
        $this->signalDispatcher->emit(
            new DeleteTranslationSignal(['contentId' => $contentInfo->id, 'languageCode' => $languageCode])
        );
    }
}
