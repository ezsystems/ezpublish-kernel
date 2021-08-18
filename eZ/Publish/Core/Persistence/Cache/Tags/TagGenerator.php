<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\Persistence\Cache\Tags;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

/**
 * @internal
 */
final class TagGenerator implements TagGeneratorInterface
{
    private const PLACEHOLDER = '-%s';

    /** @var string */
    private $prefix;

    /** @var array<string,string> */
    private $patterns;

    public function __construct(string $prefix, array $patterns)
    {
        $this->prefix = $prefix;
        $this->patterns = $patterns;
    }

    /**
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException
     */
    public function generate(string $patternName, array $values = [], bool $withPrefix = false): string
    {
        if (!isset($this->patterns[$patternName])) {
            throw new InvalidArgumentException($patternName, 'Undefined tag pattern');
        }

        $pattern = $this->patterns[$patternName];

        if (empty($values)) {
            $tag = str_replace(self::PLACEHOLDER, '', $pattern);
        } else {
            $tag = vsprintf($pattern, $values);
        }

        if ($withPrefix) {
            $tag = $this->prefix . $tag;
        }

        return $tag;
    }
}
