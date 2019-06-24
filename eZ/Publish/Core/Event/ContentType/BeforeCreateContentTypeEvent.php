<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft;
use eZ\Publish\Core\Event\BeforeEvent;

final class BeforeCreateContentTypeEvent extends BeforeEvent
{
    /**
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct
     */
    private $contentTypeCreateStruct;

    /**
     * @var array
     */
    private $contentTypeGroups;

    /**
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft|null
     */
    private $contentTypeDraft;

    public function __construct(ContentTypeCreateStruct $contentTypeCreateStruct, array $contentTypeGroups)
    {
        $this->contentTypeCreateStruct = $contentTypeCreateStruct;
        $this->contentTypeGroups = $contentTypeGroups;
    }

    public function getContentTypeCreateStruct(): ContentTypeCreateStruct
    {
        return $this->contentTypeCreateStruct;
    }

    public function getContentTypeGroups(): array
    {
        return $this->contentTypeGroups;
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
