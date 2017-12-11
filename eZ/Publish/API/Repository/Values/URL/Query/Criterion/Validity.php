<?php

namespace eZ\Publish\API\Repository\Values\URL\Query\Criterion;

/**
 * Matches URLs based on validity flag.
 */
class Validity extends Matcher
{
    /**
     * @var bool|null
     */
    public $isValid;

    /**
     * Validity constructor.
     *
     * @param bool|null $isValid
     */
    public function __construct($isValid = null)
    {
        $this->isValid = $isValid;
    }
}
