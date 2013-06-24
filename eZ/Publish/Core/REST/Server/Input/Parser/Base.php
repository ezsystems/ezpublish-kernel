<?php
/**
 * File containing the Base parser class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Input\Parser;

use eZ\Publish\Core\REST\Common\Input\Parser;
use eZ\Publish\Core\REST\Common\RequestParser;

abstract class Base extends Parser
{
    /**
     * URL handler
     *
     * @var \eZ\Publish\Core\REST\Common\RequestParser
     */
    protected $requestParser;

    public function setRequestParser( RequestParser $requestParser )
    {
        $this->requestParser = $requestParser;
    }
}
