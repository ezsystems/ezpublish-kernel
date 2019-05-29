<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft;
use eZ\Publish\Core\Event\BeforeEvent;

final class BeforeCreateContentTypeDraftEvent extends BeforeEvent
{
    public const NAME = 'ezplatform.event.content_type.draft_create.before';

    /**
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    private $contentType;

    /**
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft|null
     */
    private $contentTypeDraft;

    public function __construct(ContentType $contentType)
    {
        $this->contentType = $contentType;
    }

    public function getContentType(): ContentType
    {
        return $this->contentType;
    }

    public function getContentTypeDraft(): ?ContentTypeDraft
    {
        return $this->contentTypeDraft;
    }

    public function setContentTypeDraft(?ContentTypeDraft $contentTypeDraft): void
    {
        $this->contentTypeDraft = $contentTypeDraft;
    }

    public function hasContentTypeDraft(): bool
    {
        return $this->contentTypeDraft instanceof ContentTypeDraft;
    }
}
