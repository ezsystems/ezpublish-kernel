<?php
/**
 * File containing the SessionInput parser class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\Core\REST\Common\Exceptions;
use eZ\Publish\Core\REST\Server\Values\SessionInput as SessionInputValue;

/**
 * Parser for SessionInput
 */
class SessionInput extends Base
{
    /**
     * Parse input structure
     *
     * @param array $data
     * @param \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher $parsingDispatcher
     *
     * @return \eZ\Publish\Core\REST\Server\Values\SessionInput
     */
    public function parse( array $data, ParsingDispatcher $parsingDispatcher )
    {
        $sessionInput = new SessionInputValue();

        if ( !array_key_exists( 'login', $data ) )
        {
            throw new Exceptions\Parser( "Missing 'login' attribute for SessionInput." );
        }

        $sessionInput->login = $data['login'];

        if ( !array_key_exists( 'password', $data ) )
        {
            throw new Exceptions\Parser( "Missing 'password' attribute for SessionInput." );
        }

        $sessionInput->password = $data['password'];

        return $sessionInput;
    }
}
