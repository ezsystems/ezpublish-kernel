<?php

namespace eZ\Publish\API\Repository\Values\URL\Query\Criterion;

/**
 * Matches URLs based on validity flag.
 */
class Validity extends Matcher
{
    /**
     * If true the matcher will selects only valid URLs.
     *
     * @var bool
     */
    public $isValid;

    /**
     * Validity constructor.
     *
     * @param bool $isValid
     */
    public function __construct($isValid)
    {
        $this->isValid = $isValid;
    }
}
