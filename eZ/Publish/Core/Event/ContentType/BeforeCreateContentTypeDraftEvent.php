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
use UnexpectedValueException;

final class BeforeCreateContentTypeDraftEvent extends BeforeEvent
{
    /** @var \eZ\Publish\API\Repository\Values\ContentType\ContentType */
    private $contentType;

    /** @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft|null */
    private $contentTypeDraft;

    public function __construct(ContentType $contentType)
    {
        $this->contentType = $contentType;
    }

    public function getContentType(): ContentType
    {
        return $this->contentType;
    }

    public function getContentTypeDraft(): ContentTypeDraft
    {
        if (!$this->hasContentTypeDraft()) {
            throw new UnexpectedValueException(sprintf('Return value is not set or not a type of %s. Check hasContentTypeDraft() or set it by setContentTypeDraft() before you call getter.', ContentTypeDraft::class));
        }

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
