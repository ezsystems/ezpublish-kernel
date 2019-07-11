<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Event\URLAlias;

use eZ\Publish\API\Repository\Events\URLAlias\BeforeRefreshSystemUrlAliasesForLocationEvent as BeforeRefreshSystemUrlAliasesForLocationEventInterface;
use eZ\Publish\API\Repository\Values\Content\Location;
use Symfony\Contracts\EventDispatcher\Event;

final class BeforeRefreshSystemUrlAliasesForLocationEvent extends Event implements BeforeRefreshSystemUrlAliasesForLocationEventInterface
{
    /** @var \eZ\Publish\API\Repository\Values\Content\Location */
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
