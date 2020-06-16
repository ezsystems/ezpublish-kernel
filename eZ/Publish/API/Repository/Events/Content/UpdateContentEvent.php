<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\Content;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\SPI\Repository\Event\AfterEvent;

final class UpdateContentEvent extends AfterEvent
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Content */
    private $content;

    /** @var \eZ\Publish\API\Repository\Values\Content\VersionInfo */
    private $versionInfo;

    /** @var \eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct */
    private $contentUpdateStruct;

    /** @var string[]|null */
    private $fieldIdentifiersToValidate;

    public function __construct(
        Content $content,
        VersionInfo $versionInfo,
        ContentUpdateStruct $contentUpdateStruct,
        ?array $fieldIdentifiersToValidate = null
    ) {
        $this->content = $content;
        $this->versionInfo = $versionInfo;
        $this->contentUpdateStruct = $contentUpdateStruct;
        $this->fieldIdentifiersToValidate = $fieldIdentifiersToValidate;
    }

    public function getContent(): Content
    {
        return $this->content;
    }

    public function getVersionInfo(): VersionInfo
    {
        return $this->versionInfo;
    }

    public function getContentUpdateStruct(): ContentUpdateStruct
    {
        return $this->contentUpdateStruct;
    }

    /**
     * @return string[]|null
     */
    public function getFieldIdentifiersToValidate(): ?array
    {
        return $this->fieldIdentifiersToValidate;
    }
}
