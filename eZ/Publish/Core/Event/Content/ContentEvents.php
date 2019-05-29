<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Content;

final class ContentEvents
{
    public const CREATE_CONTENT = CreateContentEvent::NAME;
    public const BEFORE_CREATE_CONTENT = BeforeCreateContentEvent::NAME;
    public const UPDATE_CONTENT_METADATA = UpdateContentMetadataEvent::NAME;
    public const BEFORE_UPDATE_CONTENT_METADATA = BeforeUpdateContentMetadataEvent::NAME;
    public const DELETE_CONTENT = DeleteContentEvent::NAME;
    public const BEFORE_DELETE_CONTENT = BeforeDeleteContentEvent::NAME;
    public const CREATE_CONTENT_DRAFT = CreateContentDraftEvent::NAME;
    public const BEFORE_CREATE_CONTENT_DRAFT = BeforeCreateContentDraftEvent::NAME;
    public const UPDATE_CONTENT = UpdateContentEvent::NAME;
    public const BEFORE_UPDATE_CONTENT = BeforeUpdateContentEvent::NAME;
    public const PUBLISH_VERSION = PublishVersionEvent::NAME;
    public const BEFORE_PUBLISH_VERSION = BeforePublishVersionEvent::NAME;
    public const DELETE_VERSION = DeleteVersionEvent::NAME;
    public const BEFORE_DELETE_VERSION = BeforeDeleteVersionEvent::NAME;
    public const COPY_CONTENT = CopyContentEvent::NAME;
    public const BEFORE_COPY_CONTENT = BeforeCopyContentEvent::NAME;
    public const ADD_RELATION = AddRelationEvent::NAME;
    public const BEFORE_ADD_RELATION = BeforeAddRelationEvent::NAME;
    public const DELETE_RELATION = DeleteRelationEvent::NAME;
    public const BEFORE_DELETE_RELATION = BeforeDeleteRelationEvent::NAME;
    public const DELETE_TRANSLATION = DeleteTranslationEvent::NAME;
    public const BEFORE_DELETE_TRANSLATION = BeforeDeleteTranslationEvent::NAME;
    public const HIDE_CONTENT = HideContentEvent::NAME;
    public const BEFORE_HIDE_CONTENT = BeforeHideContentEvent::NAME;
    public const REVEAL_CONTENT = RevealContentEvent::NAME;
    public const BEFORE_REVEAL_CONTENT = BeforeRevealContentEvent::NAME;
}
