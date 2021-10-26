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
interface CacheIdentifierGeneratorInterface
{
    /**
     * @param string $patternName patterns determining how the tag will look like, defined in ibexa.core.persistence.cache.tag_patterns
     * @param array $values containing scalars, mostly integers and strings
     * @param bool $withPrefix used mainly by keys, if set to true, tags will be prefixed with ibx-
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function generateTag(string $patternName, array $values = [], bool $withPrefix = false): string;

    /**
     * @param string $patternName patterns determining how the tag will look like, defined in ibexa.core.persistence.cache.key_patterns
     * @param array $values containing scalars, mostly integers and strings
     * @param bool $withPrefix used mainly by keys, if set to true, tags will be prefixed with ibx-
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function generateKey(string $patternName, array $values = [], bool $withPrefix = false): string;
}
