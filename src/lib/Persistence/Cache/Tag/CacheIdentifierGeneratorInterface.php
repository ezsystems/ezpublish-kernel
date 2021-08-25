<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Core\Persistence\Cache\Tag;

/**
 * @internal
 */
interface CacheIdentifierGeneratorInterface
{
    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function generateTag(string $patternName, array $values = [], bool $withPrefix = false): string;

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function generateKey(string $patternName, array $values = [], bool $withPrefix = false): string;
}
