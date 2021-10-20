<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Core\Persistence\Cache\Identifier;

/**
 * @internal
 */
final class CacheIdentifierSanitizer
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
}
