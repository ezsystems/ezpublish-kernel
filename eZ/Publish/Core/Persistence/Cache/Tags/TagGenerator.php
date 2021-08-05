<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Cache\Tags;

class TagGenerator implements TagGeneratorInterface
{
    private const PLACEHOLDER = '-%s';
    private const SUFFIX_SEPARATOR = '-';

    public function generate(string $patternName, array $values = [], bool $isPrefix = false): string
    {
        $pattern = constant(TagIdentifierPatterns::class . '::' . strtoupper($patternName));

        if (empty($values)) {
            $computedTag = str_replace(self::PLACEHOLDER, '', $pattern);
        } else {
            $computedTag = sprintf($pattern, ...$values);
        }

        return $computedTag . ($isPrefix ? self::SUFFIX_SEPARATOR : '');
    }
}
