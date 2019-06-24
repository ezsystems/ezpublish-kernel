<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\URLAlias;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\URLAlias;
use eZ\Publish\Core\Event\BeforeEvent;

final class BeforeCreateUrlAliasEvent extends BeforeEvent
{
    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Location
     */
    private $location;

    private $path;

    private $languageCode;

    private $forwarding;

    private $alwaysAvailable;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\URLAlias|null
     */
    private $urlAlias;

    public function __construct(Location $location, $path, $languageCode, $forwarding, $alwaysAvailable)
    {
        $this->location = $location;
        $this->path = $path;
        $this->languageCode = $languageCode;
        $this->forwarding = $forwarding;
        $this->alwaysAvailable = $alwaysAvailable;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getLanguageCode()
    {
        return $this->languageCode;
    }

    public function getForwarding()
    {
        return $this->forwarding;
    }

    public function getAlwaysAvailable()
    {
        return $this->alwaysAvailable;
    }

    public function getUrlAlias(): ?URLAlias
    {
        return $this->urlAlias;
    }

    public function setUrlAlias(?URLAlias $urlAlias): void
    {
        $this->urlAlias = $urlAlias;
    }

    public function hasUrlAlias(): bool
    {
        return $this->urlAlias instanceof URLAlias;
    }
}
