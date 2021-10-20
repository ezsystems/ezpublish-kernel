<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Core\Persistence\Cache\Identifier;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

/**
 * @internal
 */
final class CacheIdentifierGenerator implements CacheIdentifierGeneratorInterface
{
    private const PLACEHOLDER = '-%s';

    /** @var string */
    private $prefix;

    /** @var array<string,string> */
    private $tagPatterns;

    /** @var array<string,string> */
    private $keyPatterns;

    public function __construct(string $prefix, array $tagPatterns, array $keyPatterns)
    {
        $this->prefix = $prefix;
        $this->tagPatterns = $tagPatterns;
        $this->keyPatterns = $keyPatterns;
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function generateTag(string $patternName, array $values = [], bool $withPrefix = false): string
    {
        if (!isset($this->tagPatterns[$patternName])) {
            throw new InvalidArgumentException($patternName, sprintf(
                'Undefined tag pattern "%s". Known pattern names are: "%s"',
                $patternName,
                implode('", "', array_keys($this->tagPatterns))
            ));
        }

        return $this->generate($this->tagPatterns[$patternName], $values, $withPrefix);
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function generateKey(string $patternName, array $values = [], bool $withPrefix = false): string
    {
        if (!isset($this->keyPatterns[$patternName])) {
            throw new InvalidArgumentException($patternName, sprintf(
                'Undefined key pattern "%s". Known pattern names are: "%s"',
                $patternName,
                implode('", "', array_keys($this->keyPatterns))
            ));
        }

        return $this->generate($this->keyPatterns[$patternName], $values, $withPrefix);
    }

    private function generate(string $pattern, array $values, bool $withPrefix = false): string
    {
        if (empty($values)) {
            $cacheIdentifier = str_replace(self::PLACEHOLDER, '', $pattern);
        } else {
            $cacheIdentifier = vsprintf($pattern, $values);
        }

        if ($withPrefix) {
            $cacheIdentifier = $this->prefix . $cacheIdentifier;
        }

        return $cacheIdentifier;
    }
}
