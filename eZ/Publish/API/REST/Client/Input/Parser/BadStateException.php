<?php
/**
 * File containing the BadStateException parser class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Client\Input\Parser;
use eZ\Publish\API\REST\Client\Exceptions;

use eZ\Publish\API\REST\Common\Input\Parser;
use eZ\Publish\API\REST\Common\Input\ParsingDispatcher;


/**
 * Parser for ExceptionList
 */
class BadStateException extends Parser
{
    /**
     * Parse input structure
     *
     * @param array $data
     * @param ParsingDispatcher $parsingDispatcher
     * @return ValueObject
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        throw new Exceptions\BadStateException(
            $data['message'],
            $data['_code']
        );
    }
}
