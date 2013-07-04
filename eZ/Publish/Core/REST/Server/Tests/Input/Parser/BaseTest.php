<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\REST\Common\RequestParser;
use eZ\Publish\Core\REST\Common\Input;
use eZ\Publish\Core\REST\Server\Tests\BaseTest as ParentBaseTest;

/**
 * Base test for input parsers.
 */
abstract class BaseTest extends ParentBaseTest
{
    /**
     * @var \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher
     */
    protected $parsingDispatcherMock;

    /**
     * @var \eZ\Publish\Core\REST\Common\RequestParser\eZPublish
     */
    protected $requestParser;

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
     * Get the Request parser
     *
     * @return \eZ\Publish\Core\REST\Common\RequestParser\eZPublish
     */
    protected function getRequestParser()
    {
        if ( !isset( $this->requestParser ) )
        {
            $this->requestParser = new RequestParser\eZPublish;
        }
        return $this->requestParser;
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

    protected function getParser()
    {
        $parser = $this->internalGetParser();
        $parser->setRequestParser( $this->getRequestParser() );
        return $parser;
    }

    /**
     * Must return the tested parser object.
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\Base
     */
    abstract protected function internalGetParser();
}
