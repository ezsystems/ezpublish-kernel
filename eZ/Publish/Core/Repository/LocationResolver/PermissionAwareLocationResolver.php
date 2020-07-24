<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Repository\LocationResolver;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Location;

final class PermissionAwareLocationResolver implements LocationResolver
{
    /** @var \eZ\Publish\API\Repository\LocationService */
    private $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     */
    public function resolveLocation(ContentInfo $contentInfo): Location
    {
        try {
            $location = $this->locationService->loadLocation($contentInfo->mainLocationId);
        } catch (NotFoundException | UnauthorizedException $e) {
            // try different locations if main location is not accessible for the user
            $locations = $this->locationService->loadLocations($contentInfo);
            if (empty($locations)) {
                throw $e;
            }

            // foreach to keep forward compatibility with a type of returned loadLocations() result
            foreach ($locations as $location) {
                return $location;
            }
        }

        return $location;
    }
}
