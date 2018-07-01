<?php

/**
 * File containing the SessionInput parser class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\BaseParser;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\Core\REST\Server\Values\SessionInput as SessionInputValue;

/**
 * Parser for SessionInput.
 */
class SessionInput extends BaseParser
{
    /**
     * Parse input structure.
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \eZ\Publish\Core\REST\Server\Values\SessionInput
     */
    public function parse(array $data, ParsingDispatcher $parsingDispatcher)
    {
        $sessionInput = new SessionInputValue();

        if (!array_key_exists('login', $data)) {
            throw new Exceptions\Parser("Missing 'login' attribute for SessionInput.");
        }

        $sessionInput->login = $data['login'];

        if (!array_key_exists('password', $data)) {
            throw new Exceptions\Parser("Missing 'password' attribute for SessionInput.");
        }

        $sessionInput->password = $data['password'];

        return $sessionInput;
    }
}
