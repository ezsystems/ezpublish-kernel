<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\URLAlias;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\Event\AfterEvent;

final class RefreshSystemUrlAliasesForLocationEvent extends AfterEvent
{
    public const NAME = 'ezplatform.event.url_alias.system_refresh';

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Location
     */
    private $location;

    public function __construct(Location $location)
    {
        $this->location = $location;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }
}
