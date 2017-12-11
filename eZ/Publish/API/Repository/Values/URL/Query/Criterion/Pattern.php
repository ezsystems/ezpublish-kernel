<?php

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
    public function __construct($pattern)
    {
        if ($pattern === null || $pattern === '') {
            throw new \InvalidArgumentException('URL pattern should not be empty!');
        }

        $this->pattern = $pattern;
    }
}
