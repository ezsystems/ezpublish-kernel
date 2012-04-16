<?php
/**
 * File containing the Parser class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Server\Input\Parser;
use eZ\Publish\API\REST\Common\Input\Parser;
use eZ\Publish\API\REST\Common\UrlHandler;

abstract class Base extends Parser
{
    /**
     * URL handler
     *
     * @var \eZ\Publish\API\REST\Common\UrlHandler
     */
    protected $urlHandler;

    /**
     * Creates a new parser.
     *
     * @param UrlHandler $urlHandler
     * @return void
     */
    public function __construct( UrlHandler $urlHandler )
    {
        $this->urlHandler = $urlHandler;
    }
}
