<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\ContentType;

use eZ\Publish\API\Repository\Events\ContentType\CreateContentTypeDraftEvent as CreateContentTypeDraftEventInterface;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft;
use Symfony\Contracts\EventDispatcher\Event;

final class CreateContentTypeDraftEvent extends Event implements CreateContentTypeDraftEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft */
    private $contentTypeDraft;

    /** @var \eZ\Publish\API\Repository\Values\ContentType\ContentType */
    private $contentType;

    public function __construct(
        ContentTypeDraft $contentTypeDraft,
        ContentType $contentType
    ) {
        $this->contentTypeDraft = $contentTypeDraft;
        $this->contentType = $contentType;
    }

    public function getContentTypeDraft(): ContentTypeDraft
    {
        return $this->contentTypeDraft;
    }

    public function getContentType(): ContentType
    {
        return $this->contentType;
    }
}
