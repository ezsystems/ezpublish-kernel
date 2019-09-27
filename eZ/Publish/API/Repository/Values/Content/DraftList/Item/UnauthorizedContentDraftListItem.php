<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\Content\DraftList\Item;

use eZ\Publish\API\Repository\Values\Content\DraftList\ContentDraftListItemInterface;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;

/**
 * Item of content drafts list which represents draft to which user has no access for.
 */
class UnauthorizedContentDraftListItem implements ContentDraftListItemInterface
{
    /** @var string */
    private $module;

    /** @var string */
    private $function;

    /** @var array */
    private $payload;

    /**
     * @param string $module
     * @param string $function
     * @param array $payload
     */
    public function __construct(string $module, string $function, array $payload)
    {
        $this->module = $module;
        $this->function = $function;
        $this->payload = $payload;
    }

    /**
     * @return string
     */
    public function getModule(): string
    {
        return $this->module;
    }

    /**
     * @return string
     */
    public function getFunction(): string
    {
        return $this->function;
    }

    /**
     * @return array
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\Content\VersionInfo|null
     */
    public function getVersionInfo(): ?VersionInfo
    {
        return null;
    }

    /**
     * @return bool
     */
    public function hasVersionInfo(): bool
    {
        return false;
    }
}
