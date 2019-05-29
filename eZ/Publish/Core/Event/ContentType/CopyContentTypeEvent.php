<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\Core\Event\AfterEvent;

final class CopyContentTypeEvent extends AfterEvent
{
    public const NAME = 'ezplatform.event.content_type.copy';

    /**
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    private $contentTypeCopy;

    /**
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    private $contentType;

    /**
     * @var \eZ\Publish\API\Repository\Values\User\User
     */
    private $creator;

    public function __construct(
        ContentType $contentTypeCopy,
        ContentType $contentType,
        User $creator
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

    public function getCreator(): User
    {
        return $this->creator;
    }
}
