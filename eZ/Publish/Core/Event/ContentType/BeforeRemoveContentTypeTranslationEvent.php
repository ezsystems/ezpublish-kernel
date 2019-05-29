<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft;
use eZ\Publish\Core\Event\BeforeEvent;

final class BeforeRemoveContentTypeTranslationEvent extends BeforeEvent
{
    public const NAME = 'ezplatform.event.content_type.translation_remove.before';

    /**
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     */
    private $contentTypeDraft;

    /**
     * @var string
     */
    private $languageCode;

    /**
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft|null
     */
    private $newContentTypeDraft;

    public function __construct(ContentTypeDraft $contentTypeDraft, string $languageCode)
    {
        $this->contentTypeDraft = $contentTypeDraft;
        $this->languageCode = $languageCode;
    }

    public function getContentTypeDraft(): ContentTypeDraft
    {
        return $this->contentTypeDraft;
    }

    public function getLanguageCode(): string
    {
        return $this->languageCode;
    }

    public function getNewContentTypeDraft(): ?ContentTypeDraft
    {
        return $this->newContentTypeDraft;
    }

    public function setNewContentTypeDraft(?ContentTypeDraft $newContentTypeDraft): void
    {
        $this->newContentTypeDraft = $newContentTypeDraft;
    }

    public function hasNewContentTypeDraft(): bool
    {
        return $this->newContentTypeDraft instanceof ContentTypeDraft;
    }
}
