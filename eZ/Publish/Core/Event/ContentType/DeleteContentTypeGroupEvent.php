<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;
use eZ\Publish\Core\Event\AfterEvent;

final class DeleteContentTypeGroupEvent extends AfterEvent
{
    public const NAME = 'ezplatform.event.content_type_group.delete';

    /**
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup
     */
    private $contentTypeGroup;

    public function __construct(ContentTypeGroup $contentTypeGroup)
    {
        $this->contentTypeGroup = $contentTypeGroup;
    }

    public function getContentTypeGroup(): ContentTypeGroup
    {
        return $this->contentTypeGroup;
    }
}
