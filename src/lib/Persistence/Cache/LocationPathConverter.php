<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Core\Persistence\Cache;

/**
 * @internal
 */
final class LocationPathConverter
{
    private const PATH_STRING_SEPARATOR = '/';
    private const ROOT_LOCATION_PATH_ID = 1;

    /**
     * @param string $pathString
     *
     * @return array<string>
     */
    public function convertToPathIds(string $pathString): array
    {
        if (!$pathString) {
            return [];
        }

        $pathIds = \explode(
            self::PATH_STRING_SEPARATOR,
            trim($pathString, self::PATH_STRING_SEPARATOR)
        );

        /*
         * Skipping locationId=1, as there is no scenario where this locationId is used to invalidate cache items.
         * It results in memory savings as set "lp-1" is not created and thus doesn't include huge amount of items
         * coming from the fact that all the location paths contain /1/ part.
         */
        if ((int) $pathIds[0] === self::ROOT_LOCATION_PATH_ID) {
            array_shift($pathIds);
        }

        return $pathIds;
    }
}
