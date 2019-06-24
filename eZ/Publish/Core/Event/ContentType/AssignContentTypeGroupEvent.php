<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;
use eZ\Publish\Core\Event\AfterEvent;

final class AssignContentTypeGroupEvent extends AfterEvent
{
    /**
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentType
     */
    private $contentType;

    /**
     * @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup
     */
    private $contentTypeGroup;

    public function __construct(
        ContentType $contentType,
        ContentTypeGroup $contentTypeGroup
    ) {
        $this->contentType = $contentType;
        $this->contentTypeGroup = $contentTypeGroup;
    }

    public function getContentType(): ContentType
    {
        return $this->contentType;
    }

    public function getContentTypeGroup(): ContentTypeGroup
    {
        return $this->contentTypeGroup;
    }
}
