<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\ContentType;

use eZ\Publish\API\Repository\Events\ContentType\BeforeDeleteContentTypeGroupEvent as BeforeDeleteContentTypeGroupEventInterface;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;
use Symfony\Contracts\EventDispatcher\Event;

final class BeforeDeleteContentTypeGroupEvent extends Event implements BeforeDeleteContentTypeGroupEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup */
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
