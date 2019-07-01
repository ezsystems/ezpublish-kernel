<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\ContentType;

use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct;
use eZ\Publish\Core\Event\AfterEvent;

final class CreateContentTypeGroupEvent extends AfterEvent
{
    /** @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup */
    private $contentTypeGroup;

    /** @var \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct */
    private $contentTypeGroupCreateStruct;

    public function __construct(
        ContentTypeGroup $contentTypeGroup,
        ContentTypeGroupCreateStruct $contentTypeGroupCreateStruct
    ) {
        $this->contentTypeGroup = $contentTypeGroup;
        $this->contentTypeGroupCreateStruct = $contentTypeGroupCreateStruct;
    }

    public function getReturnValue(): ContentTypeGroup
    {
        return $this->contentTypeGroup;
    }

    public function getContentTypeGroupCreateStruct(): ContentTypeGroupCreateStruct
    {
        return $this->contentTypeGroupCreateStruct;
    }
}
