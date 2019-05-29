<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\URLAlias;

use eZ\Publish\API\Repository\Values\Content\URLAlias;
use eZ\Publish\Core\Event\AfterEvent;

final class CreateGlobalUrlAliasEvent extends AfterEvent
{
    public const NAME = 'ezplatform.event.url_alias.create_global';

    private $resource;

    private $path;

    private $languageCode;

    private $forwarding;

    private $alwaysAvailable;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\URLAlias
     */
    private $urlAlias;

    public function __construct(
        URLAlias $urlAlias,
        $resource,
        $path,
        $languageCode,
        $forwarding,
        $alwaysAvailable
    ) {
        $this->resource = $resource;
        $this->path = $path;
        $this->languageCode = $languageCode;
        $this->forwarding = $forwarding;
        $this->alwaysAvailable = $alwaysAvailable;
        $this->urlAlias = $urlAlias;
    }

    public function getResource()
    {
        return $this->resource;
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

    public function getUrlAlias(): URLAlias
    {
        return $this->urlAlias;
    }
}
