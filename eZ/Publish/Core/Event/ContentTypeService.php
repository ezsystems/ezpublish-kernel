<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event;

use eZ\Publish\API\Repository\ContentTypeService as ContentTypeServiceInterface;
use eZ\Publish\API\Repository\Events\ContentType\AddFieldDefinitionEvent as AddFieldDefinitionEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\AssignContentTypeGroupEvent as AssignContentTypeGroupEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\BeforeAddFieldDefinitionEvent as BeforeAddFieldDefinitionEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\BeforeAssignContentTypeGroupEvent as BeforeAssignContentTypeGroupEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\BeforeCopyContentTypeEvent as BeforeCopyContentTypeEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\BeforeCreateContentTypeDraftEvent as BeforeCreateContentTypeDraftEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\BeforeCreateContentTypeEvent as BeforeCreateContentTypeEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\BeforeCreateContentTypeGroupEvent as BeforeCreateContentTypeGroupEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\BeforeDeleteContentTypeEvent as BeforeDeleteContentTypeEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\BeforeDeleteContentTypeGroupEvent as BeforeDeleteContentTypeGroupEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\BeforePublishContentTypeDraftEvent as BeforePublishContentTypeDraftEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\BeforeRemoveContentTypeTranslationEvent as BeforeRemoveContentTypeTranslationEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\BeforeRemoveFieldDefinitionEvent as BeforeRemoveFieldDefinitionEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\BeforeUnassignContentTypeGroupEvent as BeforeUnassignContentTypeGroupEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\BeforeUpdateContentTypeDraftEvent as BeforeUpdateContentTypeDraftEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\BeforeUpdateContentTypeGroupEvent as BeforeUpdateContentTypeGroupEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\BeforeUpdateFieldDefinitionEvent as BeforeUpdateFieldDefinitionEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\CopyContentTypeEvent as CopyContentTypeEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\CreateContentTypeDraftEvent as CreateContentTypeDraftEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\CreateContentTypeEvent as CreateContentTypeEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\CreateContentTypeGroupEvent as CreateContentTypeGroupEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\DeleteContentTypeEvent as DeleteContentTypeEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\DeleteContentTypeGroupEvent as DeleteContentTypeGroupEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\PublishContentTypeDraftEvent as PublishContentTypeDraftEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\RemoveContentTypeTranslationEvent as RemoveContentTypeTranslationEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\RemoveFieldDefinitionEvent as RemoveFieldDefinitionEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\UnassignContentTypeGroupEvent as UnassignContentTypeGroupEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\UpdateContentTypeDraftEvent as UpdateContentTypeDraftEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\UpdateContentTypeGroupEvent as UpdateContentTypeGroupEventInterface;
use eZ\Publish\API\Repository\Events\ContentType\UpdateFieldDefinitionEvent as UpdateFieldDefinitionEventInterface;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupUpdateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\Core\Event\ContentType\AddFieldDefinitionEvent;
use eZ\Publish\Core\Event\ContentType\AssignContentTypeGroupEvent;
use eZ\Publish\Core\Event\ContentType\BeforeAddFieldDefinitionEvent;
use eZ\Publish\Core\Event\ContentType\BeforeAssignContentTypeGroupEvent;
use eZ\Publish\Core\Event\ContentType\BeforeCopyContentTypeEvent;
use eZ\Publish\Core\Event\ContentType\BeforeCreateContentTypeDraftEvent;
use eZ\Publish\Core\Event\ContentType\BeforeCreateContentTypeEvent;
use eZ\Publish\Core\Event\ContentType\BeforeCreateContentTypeGroupEvent;
use eZ\Publish\Core\Event\ContentType\BeforeDeleteContentTypeEvent;
use eZ\Publish\Core\Event\ContentType\BeforeDeleteContentTypeGroupEvent;
use eZ\Publish\Core\Event\ContentType\BeforePublishContentTypeDraftEvent;
use eZ\Publish\Core\Event\ContentType\BeforeRemoveContentTypeTranslationEvent;
use eZ\Publish\Core\Event\ContentType\BeforeRemoveFieldDefinitionEvent;
use eZ\Publish\Core\Event\ContentType\BeforeUnassignContentTypeGroupEvent;
use eZ\Publish\Core\Event\ContentType\BeforeUpdateContentTypeDraftEvent;
use eZ\Publish\Core\Event\ContentType\BeforeUpdateContentTypeGroupEvent;
use eZ\Publish\Core\Event\ContentType\BeforeUpdateFieldDefinitionEvent;
use eZ\Publish\Core\Event\ContentType\CopyContentTypeEvent;
use eZ\Publish\Core\Event\ContentType\CreateContentTypeDraftEvent;
use eZ\Publish\Core\Event\ContentType\CreateContentTypeEvent;
use eZ\Publish\Core\Event\ContentType\CreateContentTypeGroupEvent;
use eZ\Publish\Core\Event\ContentType\DeleteContentTypeEvent;
use eZ\Publish\Core\Event\ContentType\DeleteContentTypeGroupEvent;
use eZ\Publish\Core\Event\ContentType\PublishContentTypeDraftEvent;
use eZ\Publish\Core\Event\ContentType\RemoveContentTypeTranslationEvent;
use eZ\Publish\Core\Event\ContentType\RemoveFieldDefinitionEvent;
use eZ\Publish\Core\Event\ContentType\UnassignContentTypeGroupEvent;
use eZ\Publish\Core\Event\ContentType\UpdateContentTypeDraftEvent;
use eZ\Publish\Core\Event\ContentType\UpdateContentTypeGroupEvent;
use eZ\Publish\Core\Event\ContentType\UpdateFieldDefinitionEvent;
use eZ\Publish\SPI\Repository\Decorator\ContentTypeServiceDecorator;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ContentTypeService extends ContentTypeServiceDecorator
{
    /** @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface */
    protected $eventDispatcher;

    public function __construct(
        ContentTypeServiceInterface $innerService,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($innerService);

        $this->eventDispatcher = $eventDispatcher;
    }

    public function createContentTypeGroup(ContentTypeGroupCreateStruct $contentTypeGroupCreateStruct)
    {
        $eventData = [$contentTypeGroupCreateStruct];

        $beforeEvent = new BeforeCreateContentTypeGroupEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeCreateContentTypeGroupEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getContentTypeGroup();
        }

        $contentTypeGroup = $beforeEvent->hasContentTypeGroup()
            ? $beforeEvent->getContentTypeGroup()
            : $this->innerService->createContentTypeGroup($contentTypeGroupCreateStruct);

        $this->eventDispatcher->dispatch(
            new CreateContentTypeGroupEvent($contentTypeGroup, ...$eventData),
            CreateContentTypeGroupEventInterface::class
        );

        return $contentTypeGroup;
    }

    public function updateContentTypeGroup(
        ContentTypeGroup $contentTypeGroup,
        ContentTypeGroupUpdateStruct $contentTypeGroupUpdateStruct
    ): void {
        $eventData = [
            $contentTypeGroup,
            $contentTypeGroupUpdateStruct,
        ];

        $beforeEvent = new BeforeUpdateContentTypeGroupEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeUpdateContentTypeGroupEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->updateContentTypeGroup($contentTypeGroup, $contentTypeGroupUpdateStruct);

        $this->eventDispatcher->dispatch(
            new UpdateContentTypeGroupEvent(...$eventData),
            UpdateContentTypeGroupEventInterface::class
        );
    }

    public function deleteContentTypeGroup(ContentTypeGroup $contentTypeGroup): void
    {
        $eventData = [$contentTypeGroup];

        $beforeEvent = new BeforeDeleteContentTypeGroupEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeDeleteContentTypeGroupEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->deleteContentTypeGroup($contentTypeGroup);

        $this->eventDispatcher->dispatch(
            new DeleteContentTypeGroupEvent(...$eventData),
            DeleteContentTypeGroupEventInterface::class
        );
    }

    public function createContentType(
        ContentTypeCreateStruct $contentTypeCreateStruct,
        array $contentTypeGroups
    ) {
        $eventData = [
            $contentTypeCreateStruct,
            $contentTypeGroups,
        ];

        $beforeEvent = new BeforeCreateContentTypeEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeCreateContentTypeEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getContentTypeDraft();
        }

        $contentTypeDraft = $beforeEvent->hasContentTypeDraft()
            ? $beforeEvent->getContentTypeDraft()
            : $this->innerService->createContentType($contentTypeCreateStruct, $contentTypeGroups);

        $this->eventDispatcher->dispatch(
            new CreateContentTypeEvent($contentTypeDraft, ...$eventData),
            CreateContentTypeEventInterface::class
        );

        return $contentTypeDraft;
    }

    public function createContentTypeDraft(ContentType $contentType)
    {
        $eventData = [$contentType];

        $beforeEvent = new BeforeCreateContentTypeDraftEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeCreateContentTypeDraftEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getContentTypeDraft();
        }

        $contentTypeDraft = $beforeEvent->hasContentTypeDraft()
            ? $beforeEvent->getContentTypeDraft()
            : $this->innerService->createContentTypeDraft($contentType);

        $this->eventDispatcher->dispatch(
            new CreateContentTypeDraftEvent($contentTypeDraft, ...$eventData),
            CreateContentTypeDraftEventInterface::class
        );

        return $contentTypeDraft;
    }

    public function updateContentTypeDraft(
        ContentTypeDraft $contentTypeDraft,
        ContentTypeUpdateStruct $contentTypeUpdateStruct
    ): void {
        $eventData = [
            $contentTypeDraft,
            $contentTypeUpdateStruct,
        ];

        $beforeEvent = new BeforeUpdateContentTypeDraftEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeUpdateContentTypeDraftEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->updateContentTypeDraft($contentTypeDraft, $contentTypeUpdateStruct);

        $this->eventDispatcher->dispatch(
            new UpdateContentTypeDraftEvent(...$eventData),
            UpdateContentTypeDraftEventInterface::class
        );
    }

    public function deleteContentType(ContentType $contentType): void
    {
        $eventData = [$contentType];

        $beforeEvent = new BeforeDeleteContentTypeEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeDeleteContentTypeEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->deleteContentType($contentType);

        $this->eventDispatcher->dispatch(
            new DeleteContentTypeEvent(...$eventData),
            DeleteContentTypeEventInterface::class
        );
    }

    public function copyContentType(
        ContentType $contentType,
        User $creator = null
    ) {
        $eventData = [
            $contentType,
            $creator,
        ];

        $beforeEvent = new BeforeCopyContentTypeEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeCopyContentTypeEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getContentTypeCopy();
        }

        $contentTypeCopy = $beforeEvent->hasContentTypeCopy()
            ? $beforeEvent->getContentTypeCopy()
            : $this->innerService->copyContentType($contentType, $creator);

        $this->eventDispatcher->dispatch(
            new CopyContentTypeEvent($contentTypeCopy, ...$eventData),
            CopyContentTypeEventInterface::class
        );

        return $contentTypeCopy;
    }

    public function assignContentTypeGroup(
        ContentType $contentType,
        ContentTypeGroup $contentTypeGroup
    ): void {
        $eventData = [
            $contentType,
            $contentTypeGroup,
        ];

        $beforeEvent = new BeforeAssignContentTypeGroupEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeAssignContentTypeGroupEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->assignContentTypeGroup($contentType, $contentTypeGroup);

        $this->eventDispatcher->dispatch(
            new AssignContentTypeGroupEvent(...$eventData),
            AssignContentTypeGroupEventInterface::class
        );
    }

    public function unassignContentTypeGroup(
        ContentType $contentType,
        ContentTypeGroup $contentTypeGroup
    ): void {
        $eventData = [
            $contentType,
            $contentTypeGroup,
        ];

        $beforeEvent = new BeforeUnassignContentTypeGroupEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeUnassignContentTypeGroupEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->unassignContentTypeGroup($contentType, $contentTypeGroup);

        $this->eventDispatcher->dispatch(
            new UnassignContentTypeGroupEvent(...$eventData),
            UnassignContentTypeGroupEventInterface::class
        );
    }

    public function addFieldDefinition(
        ContentTypeDraft $contentTypeDraft,
        FieldDefinitionCreateStruct $fieldDefinitionCreateStruct
    ): void {
        $eventData = [
            $contentTypeDraft,
            $fieldDefinitionCreateStruct,
        ];

        $beforeEvent = new BeforeAddFieldDefinitionEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeAddFieldDefinitionEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->addFieldDefinition($contentTypeDraft, $fieldDefinitionCreateStruct);

        $this->eventDispatcher->dispatch(
            new AddFieldDefinitionEvent(...$eventData),
            AddFieldDefinitionEventInterface::class
        );
    }

    public function removeFieldDefinition(
        ContentTypeDraft $contentTypeDraft,
        FieldDefinition $fieldDefinition
    ): void {
        $eventData = [
            $contentTypeDraft,
            $fieldDefinition,
        ];

        $beforeEvent = new BeforeRemoveFieldDefinitionEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeRemoveFieldDefinitionEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->removeFieldDefinition($contentTypeDraft, $fieldDefinition);

        $this->eventDispatcher->dispatch(
            new RemoveFieldDefinitionEvent(...$eventData),
            RemoveFieldDefinitionEventInterface::class
        );
    }

    public function updateFieldDefinition(
        ContentTypeDraft $contentTypeDraft,
        FieldDefinition $fieldDefinition,
        FieldDefinitionUpdateStruct $fieldDefinitionUpdateStruct
    ): void {
        $eventData = [
            $contentTypeDraft,
            $fieldDefinition,
            $fieldDefinitionUpdateStruct,
        ];

        $beforeEvent = new BeforeUpdateFieldDefinitionEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeUpdateFieldDefinitionEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->updateFieldDefinition($contentTypeDraft, $fieldDefinition, $fieldDefinitionUpdateStruct);

        $this->eventDispatcher->dispatch(
            new UpdateFieldDefinitionEvent(...$eventData),
            UpdateFieldDefinitionEventInterface::class
        );
    }

    public function publishContentTypeDraft(ContentTypeDraft $contentTypeDraft): void
    {
        $eventData = [$contentTypeDraft];

        $beforeEvent = new BeforePublishContentTypeDraftEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforePublishContentTypeDraftEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return;
        }

        $this->innerService->publishContentTypeDraft($contentTypeDraft);

        $this->eventDispatcher->dispatch(
            new PublishContentTypeDraftEvent(...$eventData),
            PublishContentTypeDraftEventInterface::class
        );
    }

    public function removeContentTypeTranslation(
        ContentTypeDraft $contentTypeDraft,
        string $languageCode
    ): ContentTypeDraft {
        $eventData = [
            $contentTypeDraft,
            $languageCode,
        ];

        $beforeEvent = new BeforeRemoveContentTypeTranslationEvent(...$eventData);

        $this->eventDispatcher->dispatch($beforeEvent, BeforeRemoveContentTypeTranslationEventInterface::class);
        if ($beforeEvent->isPropagationStopped()) {
            return $beforeEvent->getNewContentTypeDraft();
        }

        $newContentTypeDraft = $beforeEvent->hasNewContentTypeDraft()
            ? $beforeEvent->getNewContentTypeDraft()
            : $this->innerService->removeContentTypeTranslation($contentTypeDraft, $languageCode);

        $this->eventDispatcher->dispatch(
            new RemoveContentTypeTranslationEvent($newContentTypeDraft, ...$eventData),
            RemoveContentTypeTranslationEventInterface::class
        );

        return $newContentTypeDraft;
    }
}
