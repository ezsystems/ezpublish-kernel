<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft;
use eZ\Publish\Core\Event\BeforeEvent;

final class BeforePublishContentTypeDraftEvent extends BeforeEvent
{
    public const NAME = 'ezplatform.event.content_type.draft_publish.before';

    /**
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft
     */
    private $contentTypeDraft;

    public function __construct(ContentTypeDraft $contentTypeDraft)
    {
        $this->contentTypeDraft = $contentTypeDraft;
    }

    public function getContentTypeDraft(): ContentTypeDraft
    {
        return $this->contentTypeDraft;
    }
}
