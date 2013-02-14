<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\REST\Common\UrlHandler;
use eZ\Publish\Core\REST\Common\Input;

/**
 * Base test for input parsers.
 */
abstract class BaseTest extends \eZ\Publish\Core\REST\Server\Tests\BaseTest
{
    /**
     * @var \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher
     */
    protected $parsingDispatcherMock;

    /**
     * @var \eZ\Publish\Core\REST\Common\UrlHandler\eZPublish
     */
    protected $urlHandler;

    /**
     * @var \eZ\Publish\Core\REST\Common\Input\ParserTools
     */
    protected $parserTools;

    /**
     * Get the parsing dispatcher
     *
     * @return \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher
     */
    protected function getParsingDispatcherMock()
    {
        if ( !isset( $this->parsingDispatcherMock ) )
        {
            $this->parsingDispatcherMock = $this->getMock(
                '\\eZ\\Publish\\Core\\REST\\Common\\Input\\ParsingDispatcher',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->parsingDispatcherMock;
    }

    /**
     * Get the URL handler
     *
     * @return \eZ\Publish\Core\REST\Common\UrlHandler\eZPublish
     */
    protected function getUrlHandler()
    {
        if ( !isset( $this->urlHandler ) )
        {
            $this->urlHandler = new UrlHandler\eZPublish;
        }
        return $this->urlHandler;
    }

    /**
     * Get the parser tools
     *
     * @return \eZ\Publish\Core\REST\Common\Input\ParserTools
     */
    protected function getParserTools()
    {
        if ( !isset( $this->parserTools ) )
        {
            $this->parserTools = new Input\ParserTools;
        }
        return $this->parserTools;
    }
}
