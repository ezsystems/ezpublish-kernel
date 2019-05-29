<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct;
use eZ\Publish\Core\Event\BeforeEvent;

final class BeforeCreateContentTypeGroupEvent extends BeforeEvent
{
    public const NAME = 'ezplatform.event.content_type_group.create.before';

    /**
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct
     */
    private $contentTypeGroupCreateStruct;

    /**
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup|null
     */
    private $contentTypeGroup;

    public function __construct(ContentTypeGroupCreateStruct $contentTypeGroupCreateStruct)
    {
        $this->contentTypeGroupCreateStruct = $contentTypeGroupCreateStruct;
    }

    public function getContentTypeGroupCreateStruct(): ContentTypeGroupCreateStruct
    {
        return $this->contentTypeGroupCreateStruct;
    }

    public function getContentTypeGroup(): ?ContentTypeGroup
    {
        return $this->contentTypeGroup;
    }

    public function setContentTypeGroup(?ContentTypeGroup $contentTypeGroup): void
    {
        $this->contentTypeGroup = $contentTypeGroup;
    }

    public function hasContentTypeGroup(): bool
    {
        return $this->contentTypeGroup instanceof ContentTypeGroup;
    }
}
