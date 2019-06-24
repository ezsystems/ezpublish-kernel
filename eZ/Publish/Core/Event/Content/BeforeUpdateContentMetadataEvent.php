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
use eZ\Publish\Core\Event\BeforeEvent;
use UnexpectedValueException;

final class BeforeUpdateContentMetadataEvent extends BeforeEvent
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\ContentInfo
     */
    private $contentInfo;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\ContentMetadataUpdateStruct
     */
    private $contentMetadataUpdateStruct;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Content|null
     */
    private $content;

    public function __construct(ContentInfo $contentInfo, ContentMetadataUpdateStruct $contentMetadataUpdateStruct)
    {
        $this->contentInfo = $contentInfo;
        $this->contentMetadataUpdateStruct = $contentMetadataUpdateStruct;
    }

    public function getContentInfo(): ContentInfo
    {
        return $this->contentInfo;
    }

    public function getContentMetadataUpdateStruct(): ContentMetadataUpdateStruct
    {
        return $this->contentMetadataUpdateStruct;
    }

    public function getContent(): Content
    {
        if (!$this->hasContent()) {
            throw new UnexpectedValueException(sprintf('Return value is not set or not a type of %s. Check hasContent() or set it by setContent() before you call getter.', Content::class));
        }

        return $this->content;
    }

    public function setContent(?Content $content): void
    {
        $this->content = $content;
    }

    public function hasContent(): bool
    {
        return $this->content instanceof Content;
    }
}
