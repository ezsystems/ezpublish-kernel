<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\ContentType;

use eZ\Publish\API\Repository\Events\BeforeEvent;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft;

interface BeforePublishContentTypeDraftEvent extends BeforeEvent
{
    public function getContentTypeDraft(): ContentTypeDraft;
}
