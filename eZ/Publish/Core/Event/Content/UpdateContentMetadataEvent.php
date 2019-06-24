<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Content;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct;
use eZ\Publish\Core\Event\AfterEvent;

final class UpdateContentMetadataEvent extends AfterEvent
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Content
     */
    private $content;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    private $contentInfo;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct
     */
    private $contentMetadataUpdateStruct;

    public function __construct(
        Content $content,
        ContentInfo $contentInfo,
        ContentMetadataUpdateStruct $contentMetadataUpdateStruct
    ) {
        $this->content = $content;
        $this->contentInfo = $contentInfo;
        $this->contentMetadataUpdateStruct = $contentMetadataUpdateStruct;
    }

    public function getContent(): Content
    {
        return $this->content;
    }

    public function getContentInfo(): ContentInfo
    {
        return $this->contentInfo;
    }

    public function getContentMetadataUpdateStruct(): ContentMetadataUpdateStruct
    {
        return $this->contentMetadataUpdateStruct;
    }
}
