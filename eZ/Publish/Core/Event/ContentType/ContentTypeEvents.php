<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\ContentType;

final class ContentTypeEvents
{
    public const CREATE_CONTENT_TYPE_GROUP = CreateContentTypeGroupEvent::NAME;
    public const BEFORE_CREATE_CONTENT_TYPE_GROUP = BeforeCreateContentTypeGroupEvent::NAME;
    public const UPDATE_CONTENT_TYPE_GROUP = UpdateContentTypeGroupEvent::NAME;
    public const BEFORE_UPDATE_CONTENT_TYPE_GROUP = BeforeUpdateContentTypeGroupEvent::NAME;
    public const DELETE_CONTENT_TYPE_GROUP = DeleteContentTypeGroupEvent::NAME;
    public const BEFORE_DELETE_CONTENT_TYPE_GROUP = BeforeDeleteContentTypeGroupEvent::NAME;
    public const CREATE_CONTENT_TYPE = CreateContentTypeEvent::NAME;
    public const BEFORE_CREATE_CONTENT_TYPE = BeforeCreateContentTypeEvent::NAME;
    public const CREATE_CONTENT_TYPE_DRAFT = CreateContentTypeDraftEvent::NAME;
    public const BEFORE_CREATE_CONTENT_TYPE_DRAFT = BeforeCreateContentTypeDraftEvent::NAME;
    public const UPDATE_CONTENT_TYPE_DRAFT = UpdateContentTypeDraftEvent::NAME;
    public const BEFORE_UPDATE_CONTENT_TYPE_DRAFT = BeforeUpdateContentTypeDraftEvent::NAME;
    public const DELETE_CONTENT_TYPE = DeleteContentTypeEvent::NAME;
    public const BEFORE_DELETE_CONTENT_TYPE = BeforeDeleteContentTypeEvent::NAME;
    public const COPY_CONTENT_TYPE = CopyContentTypeEvent::NAME;
    public const BEFORE_COPY_CONTENT_TYPE = BeforeCopyContentTypeEvent::NAME;
    public const ASSIGN_CONTENT_TYPE_GROUP = AssignContentTypeGroupEvent::NAME;
    public const BEFORE_ASSIGN_CONTENT_TYPE_GROUP = BeforeAssignContentTypeGroupEvent::NAME;
    public const UNASSIGN_CONTENT_TYPE_GROUP = UnassignContentTypeGroupEvent::NAME;
    public const BEFORE_UNASSIGN_CONTENT_TYPE_GROUP = BeforeUnassignContentTypeGroupEvent::NAME;
    public const ADD_FIELD_DEFINITION = AddFieldDefinitionEvent::NAME;
    public const BEFORE_ADD_FIELD_DEFINITION = BeforeAddFieldDefinitionEvent::NAME;
    public const REMOVE_FIELD_DEFINITION = RemoveFieldDefinitionEvent::NAME;
    public const BEFORE_REMOVE_FIELD_DEFINITION = BeforeRemoveFieldDefinitionEvent::NAME;
    public const UPDATE_FIELD_DEFINITION = UpdateFieldDefinitionEvent::NAME;
    public const BEFORE_UPDATE_FIELD_DEFINITION = BeforeUpdateFieldDefinitionEvent::NAME;
    public const PUBLISH_CONTENT_TYPE_DRAFT = PublishContentTypeDraftEvent::NAME;
    public const BEFORE_PUBLISH_CONTENT_TYPE_DRAFT = BeforePublishContentTypeDraftEvent::NAME;
    public const REMOVE_CONTENT_TYPE_TRANSLATION = RemoveContentTypeTranslationEvent::NAME;
    public const BEFORE_REMOVE_CONTENT_TYPE_TRANSLATION = BeforeRemoveContentTypeTranslationEvent::NAME;
}
