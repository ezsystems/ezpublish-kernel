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
use eZ\Publish\SPI\Repository\Event\BeforeEvent;
use UnexpectedValueException;

final class BeforeUpdateContentEvent extends BeforeEvent
{
    /** @var \eZ\Publish\API\Repository\Values\Content\VersionInfo */
    private $versionInfo;

    /** @var \eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct */
    private $contentUpdateStruct;

    /** @var \eZ\Publish\API\Repository\Values\Content\Content|null */
    private $content;

    /** @var string[]|null */
    private $fieldIdentifiersToValidate;

    public function __construct(
        VersionInfo $versionInfo,
        ContentUpdateStruct $contentUpdateStruct,
        ?array $fieldIdentifiersToValidate = null
    ) {
        $this->versionInfo = $versionInfo;
        $this->contentUpdateStruct = $contentUpdateStruct;
        $this->fieldIdentifiersToValidate = $fieldIdentifiersToValidate;
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

    public function getContent(): Content
    {
        if (!$this->hasContent()) {
            throw new UnexpectedValueException(sprintf('Return value is not set or not of type %s. Check hasContent() or set it using setContent() before you call the getter.', Content::class));
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
