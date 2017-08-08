<?php

namespace eZ\Publish\Core\Search\Common\Exception;

use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;

/**
 * Exception thrown FullText search string is invalid.
 */
class InvalidFullTextSearchString extends InvalidArgumentException
{
    /**
     * Creates a new exception when $value is invalid.
     *
     * @param mixed $value
     */
    public function __construct($value)
    {
        parent::__construct($value, 'Search query does not contain a valid FullText search string');
    }
}