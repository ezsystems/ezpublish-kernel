<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Core\Persistence\Cache;

trait CacheIdentifierTrait
{
    /**
     * Escape an argument for use in cache keys when needed.
     *
     * WARNING: Only use the result of this in cache keys, it won't work to use loading the item from backend on miss.
     *
     * @param string $identifier
     *
     * @return string
     */
    public function escapeForCacheKey(string $identifier): string
    {
        return \str_replace(
            ['_', '/', ':', '(', ')', '@', '\\', '{', '}'],
            ['__', '_S', '_C', '_BO', '_BC', '_A', '_BS', '_CBO', '_CBC'],
            $identifier
        );
    }

    /**
     * Returns shifted array without location 1, as there is no scenario where this locationId is used to invalidate cache items.
     * It results in memory savings as set "lp-1" is not created and thus doesn't include huge amount of items coming
     * from the fact that all the location paths contain /1/ part.
     *
     * @param array<string> $pathIds
     *
     * @return array<string>
     */
    public function removeRootLocationPathId(array $pathIds): array
    {
        if ($pathIds[0] === '1') {
            array_shift($pathIds);
        }

        return $pathIds;
    }
}
