<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Events\URLAlias;

use eZ\Publish\API\Repository\Events\BeforeEvent;
use eZ\Publish\API\Repository\Values\Content\URLAlias;

interface BeforeCreateGlobalUrlAliasEvent extends BeforeEvent
{
    public function getResource();

    public function getPath();

    public function getLanguageCode();

    public function getForwarding();

    public function getAlwaysAvailable();

    public function getUrlAlias(): URLAlias;

    public function setUrlAlias(?URLAlias $urlAlias): void;

    public function hasUrlAlias(): bool;
}
