<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event;

use eZ\Publish\SPI\Repository\Decorator\ContentTypeServiceDecorator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use eZ\Publish\API\Repository\ContentTypeService as ContentTypeServiceInterface;
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
use eZ\Publish\Core\Event\ContentType\ContentTypeEvents;
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

class ContentTypeService extends ContentTypeServiceDecorator
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
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
        if ($this->eventDispatcher->dispatch(ContentTypeEvents::BEFORE_CREATE_CONTENT_TYPE_GROUP, $beforeEvent)->isPropagationStopped()) {
            return $beforeEvent->getContentTypeGroup();
        }

        $contentTypeGroup = $beforeEvent->hasContentTypeGroup()
            ? $beforeEvent->getContentTypeGroup()
            : parent::createContentTypeGroup($contentTypeGroupCreateStruct);

        $this->eventDispatcher->dispatch(
            ContentTypeEvents::CREATE_CONTENT_TYPE_GROUP,
            new CreateContentTypeGroupEvent($contentTypeGroup, ...$eventData)
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
        if ($this->eventDispatcher->dispatch(ContentTypeEvents::BEFORE_UPDATE_CONTENT_TYPE_GROUP, $beforeEvent)->isPropagationStopped()) {
            return;
        }

        parent::updateContentTypeGroup($contentTypeGroup, $contentTypeGroupUpdateStruct);

        $this->eventDispatcher->dispatch(
            ContentTypeEvents::UPDATE_CONTENT_TYPE_GROUP,
            new UpdateContentTypeGroupEvent(...$eventData)
        );
    }

    public function deleteContentTypeGroup(ContentTypeGroup $contentTypeGroup): void
    {
        $eventData = [$contentTypeGroup];

        $beforeEvent = new BeforeDeleteContentTypeGroupEvent(...$eventData);
        if ($this->eventDispatcher->dispatch(ContentTypeEvents::BEFORE_DELETE_CONTENT_TYPE_GROUP, $beforeEvent)->isPropagationStopped()) {
            return;
        }

        parent::deleteContentTypeGroup($contentTypeGroup);

        $this->eventDispatcher->dispatch(
            ContentTypeEvents::DELETE_CONTENT_TYPE_GROUP,
            new DeleteContentTypeGroupEvent(...$eventData)
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
        if ($this->eventDispatcher->dispatch(ContentTypeEvents::BEFORE_CREATE_CONTENT_TYPE, $beforeEvent)->isPropagationStopped()) {
            return $beforeEvent->getContentTypeDraft();
        }

        $contentTypeDraft = $beforeEvent->hasContentTypeDraft()
            ? $beforeEvent->getContentTypeDraft()
            : parent::createContentType($contentTypeCreateStruct, $contentTypeGroups);

        $this->eventDispatcher->dispatch(
            ContentTypeEvents::CREATE_CONTENT_TYPE,
            new CreateContentTypeEvent($contentTypeDraft, ...$eventData)
        );

        return $contentTypeDraft;
    }

    public function createContentTypeDraft(ContentType $contentType)
    {
        $eventData = [$contentType];

        $beforeEvent = new BeforeCreateContentTypeDraftEvent(...$eventData);
        if ($this->eventDispatcher->dispatch(ContentTypeEvents::BEFORE_CREATE_CONTENT_TYPE_DRAFT, $beforeEvent)->isPropagationStopped()) {
            return $beforeEvent->getContentTypeDraft();
        }

        $contentTypeDraft = $beforeEvent->hasContentTypeDraft()
            ? $beforeEvent->getContentTypeDraft()
            : parent::createContentTypeDraft($contentType);

        $this->eventDispatcher->dispatch(
            ContentTypeEvents::CREATE_CONTENT_TYPE_DRAFT,
            new CreateContentTypeDraftEvent($contentTypeDraft, ...$eventData)
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
        if ($this->eventDispatcher->dispatch(ContentTypeEvents::BEFORE_UPDATE_CONTENT_TYPE_DRAFT, $beforeEvent)->isPropagationStopped()) {
            return;
        }

        parent::updateContentTypeDraft($contentTypeDraft, $contentTypeUpdateStruct);

        $this->eventDispatcher->dispatch(
            ContentTypeEvents::UPDATE_CONTENT_TYPE_DRAFT,
            new UpdateContentTypeDraftEvent(...$eventData)
        );
    }

    public function deleteContentType(ContentType $contentType): void
    {
        $eventData = [$contentType];

        $beforeEvent = new BeforeDeleteContentTypeEvent(...$eventData);
        if ($this->eventDispatcher->dispatch(ContentTypeEvents::BEFORE_DELETE_CONTENT_TYPE, $beforeEvent)->isPropagationStopped()) {
            return;
        }

        parent::deleteContentType($contentType);

        $this->eventDispatcher->dispatch(
            ContentTypeEvents::DELETE_CONTENT_TYPE,
            new DeleteContentTypeEvent(...$eventData)
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
        if ($this->eventDispatcher->dispatch(ContentTypeEvents::BEFORE_COPY_CONTENT_TYPE, $beforeEvent)->isPropagationStopped()) {
            return $beforeEvent->getContentTypeCopy();
        }

        $contentTypeCopy = $beforeEvent->hasContentTypeCopy()
            ? $beforeEvent->getContentTypeCopy()
            : parent::copyContentType($contentType, $creator);

        $this->eventDispatcher->dispatch(
            ContentTypeEvents::COPY_CONTENT_TYPE,
            new CopyContentTypeEvent($contentTypeCopy, ...$eventData)
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
        if ($this->eventDispatcher->dispatch(ContentTypeEvents::BEFORE_ASSIGN_CONTENT_TYPE_GROUP, $beforeEvent)->isPropagationStopped()) {
            return;
        }

        parent::assignContentTypeGroup($contentType, $contentTypeGroup);

        $this->eventDispatcher->dispatch(
            ContentTypeEvents::ASSIGN_CONTENT_TYPE_GROUP,
            new AssignContentTypeGroupEvent(...$eventData)
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
        if ($this->eventDispatcher->dispatch(ContentTypeEvents::BEFORE_UNASSIGN_CONTENT_TYPE_GROUP, $beforeEvent)->isPropagationStopped()) {
            return;
        }

        parent::unassignContentTypeGroup($contentType, $contentTypeGroup);

        $this->eventDispatcher->dispatch(
            ContentTypeEvents::UNASSIGN_CONTENT_TYPE_GROUP,
            new UnassignContentTypeGroupEvent(...$eventData)
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
        if ($this->eventDispatcher->dispatch(ContentTypeEvents::BEFORE_ADD_FIELD_DEFINITION, $beforeEvent)->isPropagationStopped()) {
            return;
        }

        parent::addFieldDefinition($contentTypeDraft, $fieldDefinitionCreateStruct);

        $this->eventDispatcher->dispatch(
            ContentTypeEvents::ADD_FIELD_DEFINITION,
            new AddFieldDefinitionEvent(...$eventData)
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
        if ($this->eventDispatcher->dispatch(ContentTypeEvents::BEFORE_REMOVE_FIELD_DEFINITION, $beforeEvent)->isPropagationStopped()) {
            return;
        }

        parent::removeFieldDefinition($contentTypeDraft, $fieldDefinition);

        $this->eventDispatcher->dispatch(
            ContentTypeEvents::REMOVE_FIELD_DEFINITION,
            new RemoveFieldDefinitionEvent(...$eventData)
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
        if ($this->eventDispatcher->dispatch(ContentTypeEvents::BEFORE_UPDATE_FIELD_DEFINITION, $beforeEvent)->isPropagationStopped()) {
            return;
        }

        parent::updateFieldDefinition($contentTypeDraft, $fieldDefinition, $fieldDefinitionUpdateStruct);

        $this->eventDispatcher->dispatch(
            ContentTypeEvents::UPDATE_FIELD_DEFINITION,
            new UpdateFieldDefinitionEvent(...$eventData)
        );
    }

    public function publishContentTypeDraft(ContentTypeDraft $contentTypeDraft): void
    {
        $eventData = [$contentTypeDraft];

        $beforeEvent = new BeforePublishContentTypeDraftEvent(...$eventData);
        if ($this->eventDispatcher->dispatch(ContentTypeEvents::BEFORE_PUBLISH_CONTENT_TYPE_DRAFT, $beforeEvent)->isPropagationStopped()) {
            return;
        }

        parent::publishContentTypeDraft($contentTypeDraft);

        $this->eventDispatcher->dispatch(
            ContentTypeEvents::PUBLISH_CONTENT_TYPE_DRAFT,
            new PublishContentTypeDraftEvent(...$eventData)
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
        if ($this->eventDispatcher->dispatch(ContentTypeEvents::BEFORE_REMOVE_CONTENT_TYPE_TRANSLATION, $beforeEvent)->isPropagationStopped()) {
            return $beforeEvent->getNewContentTypeDraft();
        }

        $newContentTypeDraft = $beforeEvent->hasNewContentTypeDraft()
            ? $beforeEvent->getNewContentTypeDraft()
            : parent::removeContentTypeTranslation($contentTypeDraft, $languageCode);

        $this->eventDispatcher->dispatch(
            ContentTypeEvents::REMOVE_CONTENT_TYPE_TRANSLATION,
            new RemoveContentTypeTranslationEvent($newContentTypeDraft, ...$eventData)
        );

        return $newContentTypeDraft;
    }
}
