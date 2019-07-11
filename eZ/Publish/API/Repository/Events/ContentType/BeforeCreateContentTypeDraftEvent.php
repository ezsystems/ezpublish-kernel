<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\ContentType;

use eZ\Publish\SPI\Repository\Event\BeforeEvent;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft;

interface BeforeCreateContentTypeDraftEvent extends BeforeEvent
{
    public function getContentType(): ContentType;

    public function getContentTypeDraft(): ContentTypeDraft;

    public function setContentTypeDraft(?ContentTypeDraft $contentTypeDraft): void;

    public function hasContentTypeDraft(): bool;
}
