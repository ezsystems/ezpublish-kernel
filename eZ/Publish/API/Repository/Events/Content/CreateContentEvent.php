<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\Content;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\SPI\Repository\Event\AfterEvent;

final class CreateContentEvent extends AfterEvent
{
    /** @var \eZ\Publish\API\Repository\Values\Content\ContentCreateStruct */
    private $contentCreateStruct;

    /** @var array */
    private $locationCreateStructs;

    /** @var \eZ\Publish\API\Repository\Values\Content\Content */
    private $content;

    /** @var string[]|null */
    private $fieldIdentifiersToValidate;

    public function __construct(
        Content $content,
        ContentCreateStruct $contentCreateStruct,
        array $locationCreateStructs,
        ?array $fieldIdentifiersToValidate = null
    ) {
        $this->content = $content;
        $this->contentCreateStruct = $contentCreateStruct;
        $this->locationCreateStructs = $locationCreateStructs;
        $this->fieldIdentifiersToValidate = $fieldIdentifiersToValidate;
    }

    public function getContentCreateStruct(): ContentCreateStruct
    {
        return $this->contentCreateStruct;
    }

    public function getLocationCreateStructs(): array
    {
        return $this->locationCreateStructs;
    }

    public function getContent(): Content
    {
        return $this->content;
    }

    /**
     * @return string[]|null
     */
    public function getFieldIdentifiersToValidate(): ?array
    {
        return $this->fieldIdentifiersToValidate;
    }
}
