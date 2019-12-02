<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct;
use eZ\Publish\SPI\Repository\Event\BeforeEvent;
use UnexpectedValueException;

final class BeforeCreateContentTypeGroupEvent extends BeforeEvent
{
    /** @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct */
    private $contentTypeGroupCreateStruct;

    /** @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup|null */
    private $contentTypeGroup;

    public function __construct(ContentTypeGroupCreateStruct $contentTypeGroupCreateStruct)
    {
        $this->contentTypeGroupCreateStruct = $contentTypeGroupCreateStruct;
    }

    public function getContentTypeGroupCreateStruct(): ContentTypeGroupCreateStruct
    {
        return $this->contentTypeGroupCreateStruct;
    }

    public function getContentTypeGroup(): ContentTypeGroup
    {
        if (!$this->hasContentTypeGroup()) {
            throw new UnexpectedValueException(sprintf('Return value is not set or not of type %s. Check hasContentTypeGroup() or set it using setContentTypeGroup() before you call the getter.', ContentTypeGroup::class));
        }

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
