<?php

/**
 * File containing the IdManager base class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Publish\Core\REST\Client\Tests;

use eZ\Publish\Core\REST\Common;

/**
 * Base class for ID manager used in the tests suite.
 */
class IdManager
{
    /**
     * URL handler.
     *
     * @var \eZ\Publish\Core\REST\Common\RequestParser
     */
    protected $requestParser;

    /**
     * Creates a new ID manager based on $requestParser.
     *
     * @param \eZ\Publish\Core\REST\Common\RequestParser $requestParser
     */
    public function __construct(Common\RequestParser $requestParser)
    {
        $this->requestParser = $requestParser;
    }

    /**
     * Generates a repository specific ID.
     *
     * Generates a repository specific ID for an object of $type from the
     * database ID $rawId.
     *
     * @param string $type
     * @param mixed $rawId
     *
     * @return mixed
     */
    public function generateId($type, $rawId)
    {
        return $this->requestParser->generate(
            $type,
            array(
                $type => $rawId,
            )
        );
    }

    /**
     * Parses the given $id for $type into its raw form.
     *
     * Takes a repository specific $id of $type and returns the raw database ID
     * for the object.
     *
     * @param string $type
     * @param mixed $id
     *
     * @return mixed
     */
    public function parseId($type, $id)
    {
        $values = $this->requestParser->parse($type, $id);

        return $values[$type];
    }
}
