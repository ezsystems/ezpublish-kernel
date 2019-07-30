<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events;

use eZ\Publish\API\Repository\ContentService as ContentServiceInterface;
use eZ\Publish\API\Repository\Events\Content\AddRelationEvent as AddRelationEventInterface;
use eZ\Publish\API\Repository\Events\Content\BeforeAddRelationEvent as BeforeAddRelationEventInterface;
use eZ\Publish\API\Repository\Events\Content\BeforeCopyContentEvent as BeforeCopyContentEventInterface;
use eZ\Publish\API\Repository\Events\Content\BeforeCreateContentDraftEvent as BeforeCreateContentDraftEventInterface;
use eZ\Publish\API\Repository\Events\Content\BeforeCreateContentEvent as BeforeCreateContentEventInterface;
use eZ\Publish\API\Repository\Events\Content\BeforeDeleteContentEvent as BeforeDeleteContentEventInterface;
use eZ\Publish\API\Repository\Events\Content\BeforeDeleteRelationEvent as BeforeDeleteRelationEventInterface;
use eZ\Publish\API\Repository\Events\Content\BeforeDeleteTranslationEvent as BeforeDeleteTranslationEventInterface;
use eZ\Publish\API\Repository\Events\Content\BeforeDeleteVersionEvent as BeforeDeleteVersionEventInterface;
use eZ\Publish\API\Repository\Events\Content\BeforeHideContentEvent as BeforeHideContentEventInterface;
use eZ\Publish\API\Repository\Events\Content\BeforePublishVersionEvent as BeforePublishVersionEventInterface;
use eZ\Publish\API\Repository\Events\Content\BeforeRevealContentEvent as BeforeRevealContentEventInterface;
use eZ\Publish\API\Repository\Events\Content\BeforeUpdateContentEvent as BeforeUpdateContentEventInterface;
use eZ\Publish\API\Repository\Events\Content\BeforeUpdateContentMetadataEvent as BeforeUpdateContentMetadataEventInterface;
use eZ\Publish\API\Repository\Events\Content\CopyContentEvent as CopyContentEventInterface;
use eZ\Publish\API\Repository\Events\Content\CreateContentDraftEvent as CreateContentDraftEventInterface;
use eZ\Publish\API\Repository\Events\Content\CreateContentEvent as CreateContentEventInterface;
use eZ\Publish\API\Repository\Events\Content\DeleteContentEvent as DeleteContentEventInterface;
use eZ\Publish\API\Repository\Events\Content\DeleteRelationEvent as DeleteRelationEventInterface;
use eZ\Publish\API\Repository\Events\Content\DeleteTranslationEvent as DeleteTranslationEventInterface;
use eZ\Publish\API\Repository\Events\Content\DeleteVersionEvent as DeleteVersionEventInterface;
use eZ\Publish\API\Repository\Events\Content\HideContentEvent as HideContentEventInterface;
use eZ\Publish\API\Repository\Events\Content\PublishVersionEvent as PublishVersionEventInterface;
use eZ\Publish\API\Repository\Events\Content\RevealContentEvent as RevealContentEventInterface;
use eZ\Publish\API\Repository\Events\Content\UpdateContentEvent as UpdateContentEventInterface;
use eZ\Publish\API\Repository\Events\Content\UpdateContentMetadataEvent as UpdateContentMetadataEventInterface;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\Relation;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Events\Content\AddRelationEvent;
use eZ\Publish\API\Repository\Events\Content\BeforeAddRelationEvent;
use eZ\Publish\API\Repository\Events\Content\BeforeCopyContentEvent;
use eZ\Publish\API\Repository\Events\Content\BeforeCreateContentDraftEvent;
use eZ\Publish\API\Repository\Events\Content\BeforeCreateContentEvent;
use eZ\Publish\API\Repository\Events\Content\BeforeDeleteContentEvent;
use eZ\Publish\API\Repository\Events\Content\BeforeDeleteRelationEvent;
use eZ\Publish\API\Repository\Events\Content\BeforeDeleteTranslationEvent;
use eZ\Publish\API\Repository\Events\Content\BeforeDeleteVersionEvent;
use eZ\Publish\API\Repository\Events\Content\BeforeHideContentEvent;
use eZ\Publish\API\Repository\Events\Content\BeforePublishVersionEvent;
use eZ\Publish\API\Repository\Events\Content\BeforeRevealContentEvent;
use eZ\Publish\API\Repository\Events\Content\BeforeUpdateContentEvent;
use eZ\Publish\API\Repository\Events\Content\BeforeUpdateContentMetadataEvent;
use eZ\Publish\API\Repository\Events\Content\CopyContentEvent;
use eZ\Publish\API\Repository\Events\Content\CreateContentDraftEvent;
use eZ\Publish\API\Repository\Events\Content\CreateContentEvent;
use eZ\Publish\API\Repository\Events\Content\DeleteContentEvent;
use eZ\Publish\API\Repository\Events\Content\DeleteRelationEvent;
use eZ\Publish\API\Repository\Events\Content\DeleteTranslationEvent;
use eZ\Publish\API\Repository\Events\Content\DeleteVersionEvent;
use eZ\Publish\API\Repository\Events\Content\HideContentEvent;
use eZ\Publish\API\Repository\Events\Content\PublishVersionEvent;
use eZ\Publish\API\Repository\Events\Content\RevealContentEvent;
use eZ\Publish\API\Repository\Events\Content\UpdateContentEvent;
use eZ\Publish\API\Repository\Events\Content\UpdateContentMetadataEvent;
use eZ\Publish\SPI\Repository\Decorator\ContentServiceDecorator;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ContentService extends ContentServiceDecorator
{
    /** @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface */
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

        $this->eventDispatcher->dispatch($beforeEvent, BeforeCreateContentEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getContent();
        }

        $content = $beforeEvent->hasContent()
            ? $beforeEvent->getContent()
            : $this->innerService->createContent($contentCreateStruct, $locationCreateStructs);

        $this->eventDispatcher->dispatch(
            new CreateContentEvent($content, ...$eventData),
            CreateContentEventInterface::class
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

        $this->eventDispatcher->dispatch($beforeEvent, BeforeUpdateContentMetadataEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getContent();
        }

        $content = $beforeEvent->hasContent()
            ? $beforeEvent->getContent()
            : $this->innerService->updateContentMetadata($contentInfo, $contentMetadataUpdateStruct);

        $this->eventDispatcher->dispatch(
            new UpdateContentMetadataEvent($content, ...$eventData),
            UpdateContentMetadataEventInterface::class
        );

        return $content;
    }

    public function deleteContent(ContentInfo $contentInfo): array
    {
        $eventData = [$contentInfo];

        $beforeEvent = new BeforeDeleteContentEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeDeleteContentEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getLocations();
        }

        $locations = $beforeEvent->hasLocations()
            ? $beforeEvent->getLocations()
            : $this->innerService->deleteContent($contentInfo);

        $this->eventDispatcher->dispatch(
            new DeleteContentEvent($locations, ...$eventData),
            DeleteContentEventInterface::class
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

        $this->eventDispatcher->dispatch($beforeEvent, BeforeCreateContentDraftEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getContentDraft();
        }

        $contentDraft = $beforeEvent->hasContentDraft()
            ? $beforeEvent->getContentDraft()
            : $this->innerService->createContentDraft($contentInfo, $versionInfo, $creator);

        $this->eventDispatcher->dispatch(
            new CreateContentDraftEvent($contentDraft, ...$eventData),
            CreateContentDraftEventInterface::class
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

        $this->eventDispatcher->dispatch($beforeEvent, BeforeUpdateContentEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getContent();
        }

        $content = $beforeEvent->hasContent()
            ? $beforeEvent->getContent()
            : $this->innerService->updateContent($versionInfo, $contentUpdateStruct);

        $this->eventDispatcher->dispatch(
            new UpdateContentEvent($content, ...$eventData),
            UpdateContentEventInterface::class
        );

        return $content;
    }

    public function publishVersion(VersionInfo $versionInfo, array $translations = Language::ALL): Content
    {
        $eventData = [
            $versionInfo,
            $translations,
        ];

        $beforeEvent = new BeforePublishVersionEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforePublishVersionEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getContent();
        }

        $content = $beforeEvent->hasContent()
            ? $beforeEvent->getContent()
            : $this->innerService->publishVersion($versionInfo, $translations);

        $this->eventDispatcher->dispatch(
            new PublishVersionEvent($content, ...$eventData),
            PublishVersionEventInterface::class
        );

        return $content;
    }

    public function deleteVersion(VersionInfo $versionInfo): void
    {
        $eventData = [$versionInfo];

        $beforeEvent = new BeforeDeleteVersionEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeDeleteVersionEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->deleteVersion($versionInfo);

        $this->eventDispatcher->dispatch(
            new DeleteVersionEvent(...$eventData),
            DeleteVersionEventInterface::class
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

        $this->eventDispatcher->dispatch($beforeEvent, BeforeCopyContentEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getContent();
        }

        $content = $beforeEvent->hasContent()
            ? $beforeEvent->getContent()
            : $this->innerService->copyContent($contentInfo, $destinationLocationCreateStruct, $versionInfo);

        $this->eventDispatcher->dispatch(
            new CopyContentEvent($content, ...$eventData),
            CopyContentEventInterface::class
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

        $this->eventDispatcher->dispatch($beforeEvent, BeforeAddRelationEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getRelation();
        }

        $relation = $beforeEvent->hasRelation()
            ? $beforeEvent->getRelation()
            : $this->innerService->addRelation($sourceVersion, $destinationContent);

        $this->eventDispatcher->dispatch(
            new AddRelationEvent($relation, ...$eventData),
            AddRelationEventInterface::class
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

        $this->eventDispatcher->dispatch($beforeEvent, BeforeDeleteRelationEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->deleteRelation($sourceVersion, $destinationContent);

        $this->eventDispatcher->dispatch(
            new DeleteRelationEvent(...$eventData),
            DeleteRelationEventInterface::class
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

        $this->eventDispatcher->dispatch($beforeEvent, BeforeDeleteTranslationEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->deleteTranslation($contentInfo, $languageCode);

        $this->eventDispatcher->dispatch(
            new DeleteTranslationEvent(...$eventData),
            DeleteTranslationEventInterface::class
        );
    }

    public function hideContent(ContentInfo $contentInfo): void
    {
        $eventData = [$contentInfo];

        $beforeEvent = new BeforeHideContentEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeHideContentEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->hideContent($contentInfo);

        $this->eventDispatcher->dispatch(
            new HideContentEvent(...$eventData),
            HideContentEventInterface::class
        );
    }

    public function revealContent(ContentInfo $contentInfo): void
    {
        $eventData = [$contentInfo];

        $beforeEvent = new BeforeRevealContentEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeRevealContentEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->revealContent($contentInfo);

        $this->eventDispatcher->dispatch(
            new RevealContentEvent(...$eventData),
            RevealContentEventInterface::class
        );
    }
}
