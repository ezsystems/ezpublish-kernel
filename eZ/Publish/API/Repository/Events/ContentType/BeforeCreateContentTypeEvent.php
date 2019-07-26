<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft;

interface BeforeCreateContentTypeEvent
{
    public function getContentTypeCreateStruct(): ContentTypeCreateStruct;

    public function getContentTypeGroups(): array;

    public function getContentTypeDraft(): ContentTypeDraft;

    public function setContentTypeDraft(?ContentTypeDraft $contentTypeDraft): void;

    public function hasContentTypeDraft(): bool;
}
