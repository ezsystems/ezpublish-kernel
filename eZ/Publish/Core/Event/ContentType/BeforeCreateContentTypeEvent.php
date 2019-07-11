<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\ContentType;

use eZ\Publish\API\Repository\Events\ContentType\BeforeCreateContentTypeEvent as BeforeCreateContentTypeEventInterface;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft;
use Symfony\Contracts\EventDispatcher\Event;
use UnexpectedValueException;

final class BeforeCreateContentTypeEvent extends Event implements BeforeCreateContentTypeEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct */
    private $contentTypeCreateStruct;

    /** @var array */
    private $contentTypeGroups;

    /** @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft|null */
    private $contentTypeDraft;

    public function __construct(ContentTypeCreateStruct $contentTypeCreateStruct, array $contentTypeGroups)
    {
        $this->contentTypeCreateStruct = $contentTypeCreateStruct;
        $this->contentTypeGroups = $contentTypeGroups;
    }

    public function getContentTypeCreateStruct(): ContentTypeCreateStruct
    {
        return $this->contentTypeCreateStruct;
    }

    public function getContentTypeGroups(): array
    {
        return $this->contentTypeGroups;
    }

    public function getContentTypeDraft(): ContentTypeDraft
    {
        if (!$this->hasContentTypeDraft()) {
            throw new UnexpectedValueException(sprintf('Return value is not set or not a type of %s. Check hasContentTypeDraft() or set it by setContentTypeDraft() before you call getter.', ContentTypeDraft::class));
        }

        return $this->contentTypeDraft;
    }

    public function setContentTypeDraft(?ContentTypeDraft $contentTypeDraft): void
    {
        $this->contentTypeDraft = $contentTypeDraft;
    }

    public function hasContentTypeDraft(): bool
    {
        return $this->contentTypeDraft instanceof ContentTypeDraft;
    }
}
