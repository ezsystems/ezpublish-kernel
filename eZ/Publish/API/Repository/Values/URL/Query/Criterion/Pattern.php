<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\API\Repository\Values\URL\Query\Criterion;

/**
 * Matches URLs which contains the pattern.
 */
class Pattern extends Matcher
{
    /**
     * String which needs to part of URL e.g. ez.no.
     *
     * @var string
     */
    public $pattern;

    /**
     * Pattern constructor.
     *
     * @param string $pattern
     */
    public function __construct(string $pattern)
    {
        if ($pattern === '') {
            throw new \InvalidArgumentException('URL pattern cannot be empty.');
        }

        $this->pattern = $pattern;
    }
}
