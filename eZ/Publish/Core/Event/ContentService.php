<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Values\Content\Relation;
use eZ\Publish\SPI\Repository\Decorator\ContentServiceDecorator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use eZ\Publish\API\Repository\ContentService as ContentServiceInterface;
use eZ\Publish\API\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\Core\Event\Content\AddRelationEvent;
use eZ\Publish\Core\Event\Content\BeforeAddRelationEvent;
use eZ\Publish\Core\Event\Content\BeforeCopyContentEvent;
use eZ\Publish\Core\Event\Content\BeforeCreateContentDraftEvent;
use eZ\Publish\Core\Event\Content\BeforeCreateContentEvent;
use eZ\Publish\Core\Event\Content\BeforeDeleteContentEvent;
use eZ\Publish\Core\Event\Content\BeforeDeleteRelationEvent;
use eZ\Publish\Core\Event\Content\BeforeDeleteTranslationEvent;
use eZ\Publish\Core\Event\Content\BeforeDeleteVersionEvent;
use eZ\Publish\Core\Event\Content\BeforeHideContentEvent;
use eZ\Publish\Core\Event\Content\BeforePublishVersionEvent;
use eZ\Publish\Core\Event\Content\BeforeRevealContentEvent;
use eZ\Publish\Core\Event\Content\BeforeUpdateContentEvent;
use eZ\Publish\Core\Event\Content\BeforeUpdateContentMetadataEvent;
use eZ\Publish\Core\Event\Content\ContentEvents;
use eZ\Publish\Core\Event\Content\CopyContentEvent;
use eZ\Publish\Core\Event\Content\CreateContentDraftEvent;
use eZ\Publish\Core\Event\Content\CreateContentEvent;
use eZ\Publish\Core\Event\Content\DeleteContentEvent;
use eZ\Publish\Core\Event\Content\DeleteRelationEvent;
use eZ\Publish\Core\Event\Content\DeleteTranslationEvent;
use eZ\Publish\Core\Event\Content\DeleteVersionEvent;
use eZ\Publish\Core\Event\Content\HideContentEvent;
use eZ\Publish\Core\Event\Content\PublishVersionEvent;
use eZ\Publish\Core\Event\Content\RevealContentEvent;
use eZ\Publish\Core\Event\Content\UpdateContentEvent;
use eZ\Publish\Core\Event\Content\UpdateContentMetadataEvent;

class ContentService extends ContentServiceDecorator
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    public function __construct(
        ContentServiceInterface $innerService,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($innerService);

        $this->eventDispatcher = $eventDispatcher;
    }

    public function createContent(
        ContentCreateStruct $contentCreateStruct,
        array $locationCreateStructs = []
    ): Content {
        $eventData = [
            $contentCreateStruct,
            $locationCreateStructs,
        ];

        $beforeEvent = new BeforeCreateContentEvent(...$eventData);
        if ($this->eventDispatcher->dispatch(ContentEvents::BEFORE_CREATE_CONTENT, $beforeEvent)->isPropagationStopped()) {
            return $beforeEvent->getContent();
        }

        $content = $beforeEvent->hasContent()
            ? $beforeEvent->getContent()
            : parent::createContent($contentCreateStruct, $locationCreateStructs);

        $this->eventDispatcher->dispatch(
            ContentEvents::CREATE_CONTENT,
            new CreateContentEvent($content, ...$eventData)
        );

        return $content;
    }

    public function updateContentMetadata(
        ContentInfo $contentInfo,
        ContentMetadataUpdateStruct $contentMetadataUpdateStruct
    ): Content {
        $eventData = [
            $contentInfo,
            $contentMetadataUpdateStruct,
        ];

        $beforeEvent = new BeforeUpdateContentMetadataEvent(...$eventData);
        if ($this->eventDispatcher->dispatch(ContentEvents::BEFORE_UPDATE_CONTENT_METADATA, $beforeEvent)->isPropagationStopped()) {
            return $beforeEvent->getContent();
        }

        $content = $beforeEvent->hasContent()
            ? $beforeEvent->getContent()
            : parent::updateContentMetadata($contentInfo, $contentMetadataUpdateStruct);

        $this->eventDispatcher->dispatch(
            ContentEvents::UPDATE_CONTENT_METADATA,
            new UpdateContentMetadataEvent($content, ...$eventData)
        );

        return $content;
    }

    public function deleteContent(ContentInfo $contentInfo): array
    {
        $eventData = [$contentInfo];

        $beforeEvent = new BeforeDeleteContentEvent(...$eventData);
        if ($this->eventDispatcher->dispatch(ContentEvents::BEFORE_DELETE_CONTENT, $beforeEvent)->isPropagationStopped()) {
            return $beforeEvent->getLocations();
        }

        $locations = $beforeEvent->hasLocations()
            ? $beforeEvent->getLocations()
            : parent::deleteContent($contentInfo);

        $this->eventDispatcher->dispatch(
            ContentEvents::DELETE_CONTENT,
            new DeleteContentEvent($locations, ...$eventData)
        );

        return $locations;
    }

    public function createContentDraft(
        ContentInfo $contentInfo,
        VersionInfo $versionInfo = null,
        User $creator = null
    ): Content {
        $eventData = [
            $contentInfo,
            $versionInfo,
            $creator,
        ];

        $beforeEvent = new BeforeCreateContentDraftEvent(...$eventData);
        if ($this->eventDispatcher->dispatch(ContentEvents::BEFORE_CREATE_CONTENT_DRAFT, $beforeEvent)->isPropagationStopped()) {
            return $beforeEvent->getContentDraft();
        }

        $contentDraft = $beforeEvent->hasContentDraft()
            ? $beforeEvent->getContentDraft()
            : parent::createContentDraft($contentInfo, $versionInfo, $creator);

        $this->eventDispatcher->dispatch(
            ContentEvents::CREATE_CONTENT_DRAFT,
            new CreateContentDraftEvent($contentDraft, ...$eventData)
        );

        return $contentDraft;
    }

    public function updateContent(
        VersionInfo $versionInfo,
        ContentUpdateStruct $contentUpdateStruct
    ): Content {
        $eventData = [
            $versionInfo,
            $contentUpdateStruct,
        ];

        $beforeEvent = new BeforeUpdateContentEvent(...$eventData);
        if ($this->eventDispatcher->dispatch(ContentEvents::BEFORE_UPDATE_CONTENT, $beforeEvent)->isPropagationStopped()) {
            return $beforeEvent->getContent();
        }

        $content = $beforeEvent->hasContent()
            ? $beforeEvent->getContent()
            : parent::updateContent($versionInfo, $contentUpdateStruct);

        $this->eventDispatcher->dispatch(
            ContentEvents::UPDATE_CONTENT,
            new UpdateContentEvent($content, ...$eventData)
        );

        return $content;
    }

    public function publishVersion(VersionInfo $versionInfo, array $translations = Language::ALL): Content
    {
        $eventData = [$versionInfo];

        $beforeEvent = new BeforePublishVersionEvent(...$eventData);
        if ($this->eventDispatcher->dispatch(ContentEvents::BEFORE_PUBLISH_VERSION, $beforeEvent)->isPropagationStopped()) {
            return $beforeEvent->getContent();
        }

        $content = $beforeEvent->hasContent()
            ? $beforeEvent->getContent()
            : parent::publishVersion($versionInfo);

        $this->eventDispatcher->dispatch(
            ContentEvents::PUBLISH_VERSION,
            new PublishVersionEvent($content, ...$eventData)
        );

        return $content;
    }

    public function deleteVersion(VersionInfo $versionInfo): void
    {
        $eventData = [$versionInfo];

        $beforeEvent = new BeforeDeleteVersionEvent(...$eventData);
        if ($this->eventDispatcher->dispatch(ContentEvents::BEFORE_DELETE_VERSION, $beforeEvent)->isPropagationStopped()) {
            return;
        }

        parent::deleteVersion($versionInfo);

        $this->eventDispatcher->dispatch(
            ContentEvents::DELETE_VERSION,
            new DeleteVersionEvent(...$eventData)
        );
    }

    public function copyContent(
        ContentInfo $contentInfo,
        LocationCreateStruct $destinationLocationCreateStruct,
        VersionInfo $versionInfo = null
    ): Content {
        $eventData = [
            $contentInfo,
            $destinationLocationCreateStruct,
            $versionInfo,
        ];

        $beforeEvent = new BeforeCopyContentEvent(...$eventData);
        if ($this->eventDispatcher->dispatch(ContentEvents::BEFORE_COPY_CONTENT, $beforeEvent)->isPropagationStopped()) {
            return $beforeEvent->getContent();
        }

        $content = $beforeEvent->hasContent()
            ? $beforeEvent->getContent()
            : parent::copyContent($contentInfo, $destinationLocationCreateStruct, $versionInfo);

        $this->eventDispatcher->dispatch(
            ContentEvents::COPY_CONTENT,
            new CopyContentEvent($content, ...$eventData)
        );

        return $content;
    }

    public function addRelation(
        VersionInfo $sourceVersion,
        ContentInfo $destinationContent
    ): Relation {
        $eventData = [
            $sourceVersion,
            $destinationContent,
        ];

        $beforeEvent = new BeforeAddRelationEvent(...$eventData);
        if ($this->eventDispatcher->dispatch(ContentEvents::BEFORE_ADD_RELATION, $beforeEvent)->isPropagationStopped()) {
            return $beforeEvent->getRelation();
        }

        $relation = $beforeEvent->hasRelation()
            ? $beforeEvent->getRelation()
            : parent::addRelation($sourceVersion, $destinationContent);

        $this->eventDispatcher->dispatch(
            ContentEvents::ADD_RELATION,
            new AddRelationEvent($relation, ...$eventData)
        );

        return $relation;
    }

    public function deleteRelation(
        VersionInfo $sourceVersion,
        ContentInfo $destinationContent
    ): void {
        $eventData = [
            $sourceVersion,
            $destinationContent,
        ];

        $beforeEvent = new BeforeDeleteRelationEvent(...$eventData);
        if ($this->eventDispatcher->dispatch(ContentEvents::BEFORE_DELETE_RELATION, $beforeEvent)->isPropagationStopped()) {
            return;
        }

        parent::deleteRelation($sourceVersion, $destinationContent);

        $this->eventDispatcher->dispatch(
            ContentEvents::DELETE_RELATION,
            new DeleteRelationEvent(...$eventData)
        );
    }

    public function deleteTranslation(
        ContentInfo $contentInfo,
        $languageCode
    ): void {
        $eventData = [
            $contentInfo,
            $languageCode,
        ];

        $beforeEvent = new BeforeDeleteTranslationEvent(...$eventData);
        if ($this->eventDispatcher->dispatch(ContentEvents::BEFORE_DELETE_TRANSLATION, $beforeEvent)->isPropagationStopped()) {
            return;
        }

        parent::deleteTranslation($contentInfo, $languageCode);

        $this->eventDispatcher->dispatch(
            ContentEvents::DELETE_TRANSLATION,
            new DeleteTranslationEvent(...$eventData)
        );
    }

    public function hideContent(ContentInfo $contentInfo): void
    {
        $eventData = [$contentInfo];

        $beforeEvent = new BeforeHideContentEvent(...$eventData);
        if ($this->eventDispatcher->dispatch(ContentEvents::BEFORE_HIDE_CONTENT, $beforeEvent)->isPropagationStopped()) {
            return;
        }

        parent::hideContent($contentInfo);

        $this->eventDispatcher->dispatch(
            ContentEvents::HIDE_CONTENT,
            new HideContentEvent(...$eventData)
        );
    }

    public function revealContent(ContentInfo $contentInfo): void
    {
        $eventData = [$contentInfo];

        $beforeEvent = new BeforeRevealContentEvent(...$eventData);
        if ($this->eventDispatcher->dispatch(ContentEvents::BEFORE_REVEAL_CONTENT, $beforeEvent)->isPropagationStopped()) {
            return;
        }

        parent::revealContent($contentInfo);

        $this->eventDispatcher->dispatch(
            ContentEvents::REVEAL_CONTENT,
            new RevealContentEvent(...$eventData)
        );
    }
}
