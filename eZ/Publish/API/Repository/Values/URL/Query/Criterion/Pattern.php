<?php

namespace eZ\Publish\API\Repository\Values\URL\Query\Criterion;

/**
 * Matches URLs which contains the pattern.
 */
class Pattern extends Matcher
{
    /**
     * @var string|null
     */
    public $pattern;

    /**
     * Pattern constructor.
     *
     * @param string|null $pattern
     */
    public function __construct($pattern = null)
    {
        $this->pattern = $pattern;
    }
}
