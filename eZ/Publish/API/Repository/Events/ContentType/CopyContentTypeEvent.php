<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\SPI\Repository\Event\AfterEvent;

final class CopyContentTypeEvent extends AfterEvent
{
    /** @var \eZ\Publish\API\Repository\Values\ContentType\ContentType */
    private $contentTypeCopy;

    /** @var \eZ\Publish\API\Repository\Values\ContentType\ContentType */
    private $contentType;

    /** @var \eZ\Publish\API\Repository\Values\User\User */
    private $creator;

    public function __construct(
        ContentType $contentTypeCopy,
        ContentType $contentType,
        ?User $creator = null
    ) {
        $this->contentTypeCopy = $contentTypeCopy;
        $this->contentType = $contentType;
        $this->creator = $creator;
    }

    public function getContentTypeCopy(): ContentType
    {
        return $this->contentTypeCopy;
    }

    public function getContentType(): ContentType
    {
        return $this->contentType;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }
}
