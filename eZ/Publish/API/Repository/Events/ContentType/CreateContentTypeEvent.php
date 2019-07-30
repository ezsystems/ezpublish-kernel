<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft;
use eZ\Publish\SPI\Repository\Event\AfterEvent;

final class CreateContentTypeEvent extends AfterEvent
{
    /** @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft */
    private $contentTypeDraft;

    /** @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct */
    private $contentTypeCreateStruct;

    /** @var array */
    private $contentTypeGroups;

    public function __construct(
        ContentTypeDraft $contentTypeDraft,
        ContentTypeCreateStruct $contentTypeCreateStruct,
        array $contentTypeGroups
    ) {
        $this->contentTypeDraft = $contentTypeDraft;
        $this->contentTypeCreateStruct = $contentTypeCreateStruct;
        $this->contentTypeGroups = $contentTypeGroups;
    }

    public function getContentTypeDraft(): ContentTypeDraft
    {
        return $this->contentTypeDraft;
    }

    public function getContentTypeCreateStruct(): ContentTypeCreateStruct
    {
        return $this->contentTypeCreateStruct;
    }

    public function getContentTypeGroups(): array
    {
        return $this->contentTypeGroups;
    }
}
