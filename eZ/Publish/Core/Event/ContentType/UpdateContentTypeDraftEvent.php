<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\ContentType;

use eZ\Publish\API\Repository\Events\ContentType\UpdateContentTypeDraftEvent as UpdateContentTypeDraftEventInterface;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct;
use eZ\Publish\SPI\Repository\Event\AfterEvent;

final class UpdateContentTypeDraftEvent extends AfterEvent implements UpdateContentTypeDraftEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft */
    private $contentTypeDraft;

    /** @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeUpdateStruct */
    private $contentTypeUpdateStruct;

    public function __construct(
        ContentTypeDraft $contentTypeDraft,
        ContentTypeUpdateStruct $contentTypeUpdateStruct
    ) {
        $this->contentTypeDraft = $contentTypeDraft;
        $this->contentTypeUpdateStruct = $contentTypeUpdateStruct;
    }

    public function getContentTypeDraft(): ContentTypeDraft
    {
        return $this->contentTypeDraft;
    }

    public function getContentTypeUpdateStruct(): ContentTypeUpdateStruct
    {
        return $this->contentTypeUpdateStruct;
    }
}
