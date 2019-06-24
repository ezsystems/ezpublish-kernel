<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\Content;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\Core\Event\BeforeEvent;

final class BeforeCreateContentEvent extends BeforeEvent
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\ContentCreateStruct
     */
    private $contentCreateStruct;

    /**
     * @var array
     */
    private $locationCreateStructs;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Content|null
     */
    private $content;

    public function __construct(ContentCreateStruct $contentCreateStruct, array $locationCreateStructs)
    {
        $this->contentCreateStruct = $contentCreateStruct;
        $this->locationCreateStructs = $locationCreateStructs;
    }

    public function getContentCreateStruct(): ContentCreateStruct
    {
        return $this->contentCreateStruct;
    }

    public function getLocationCreateStructs(): array
    {
        return $this->locationCreateStructs;
    }

    public function getContent(): ?Content
    {
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
