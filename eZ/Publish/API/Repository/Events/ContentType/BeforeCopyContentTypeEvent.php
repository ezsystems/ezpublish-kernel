<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\User\User;

interface BeforeCopyContentTypeEvent
{
    public function getContentType(): ContentType;

    public function getCreator(): ?User;

    public function getContentTypeCopy(): ContentType;

    public function setContentTypeCopy(?ContentType $contentTypeCopy): void;

    public function hasContentTypeCopy(): bool;
}
