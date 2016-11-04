<?php

/**
 * File containing the Parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\Input;

/**
 * Base class for input parser.
 */
abstract class Parser
{
    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \eZ\Publish\API\Repository\Values\ValueObject
     */
    abstract public function parse(array $data, ParsingDispatcher $parsingDispatcher);
}
