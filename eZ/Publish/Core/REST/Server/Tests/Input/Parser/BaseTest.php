<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\REST\Common\Input;
use eZ\Publish\Core\REST\Server\Tests\BaseTest as ParentBaseTest;
use eZ\Publish\Core\REST\Common\Input\ParsingDispatcher;
use eZ\Publish\Core\REST\Common\RequestParser;

/**
 * Base test for input parsers.
 */
abstract class BaseTest extends ParentBaseTest
{
    /** @var \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher|\PHPUnit\Framework\MockObject\MockObject */
    protected $parsingDispatcherMock;

    /** @var \eZ\Publish\Core\REST\Common\RequestParser|\PHPUnit\Framework\MockObject\MockObject */
    protected $requestParserMock;

    /** @var \eZ\Publish\Core\REST\Common\Input\ParserTools */
    protected $parserTools;

    /**
     * Get the parsing dispatcher.
     *
     * @return \eZ\Publish\Core\REST\Common\Input\ParsingDispatcher
     */
    protected function getParsingDispatcherMock()
    {
        if (!isset($this->parsingDispatcherMock)) {
            $this->parsingDispatcherMock = $this->createMock(ParsingDispatcher::class);
        }

        return $this->parsingDispatcherMock;
    }

    /**
     * Returns the parseHref invocation expectations, as an array of:
     * 0. route to parse the href from (/content/objects/59
     * 1. attribute name we are looking for (contentId)
     * 2. expected return value (59)*.
     *
     * @return array
     */
    public function getParseHrefExpectationsMap()
    {
        return [];
    }

    /**
     * Get the Request parser.
     *
     * @return \eZ\Publish\Core\REST\Common\RequestParser|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getRequestParserMock()
    {
        if (!isset($this->requestParserMock)) {
            $that = &$this;

            $callback = function ($href, $attribute) use ($that) {
                foreach ($that->getParseHrefExpectationsMap() as $map) {
                    if ($map[0] == $href && $map[1] == $attribute) {
                        if ($map[2] instanceof \Exception) {
                            throw $map[2];
                        } else {
                            return $map[2];
                        }
                    }
                }

                return null;
            };

            $this->requestParserMock = $this->createMock(RequestParser::class);

            $this->requestParserMock
                ->expects($this->any())
                ->method('parseHref')
                ->will($this->returnCallback($callback));
        }

        return $this->requestParserMock;
    }

    /**
     * Get the parser tools.
     *
     * @return \eZ\Publish\Core\REST\Common\Input\ParserTools
     */
    protected function getParserTools()
    {
        if (!isset($this->parserTools)) {
            $this->parserTools = new Input\ParserTools();
        }

        return $this->parserTools;
    }

    protected function getParser()
    {
        $parser = $this->internalGetParser();
        $parser->setRequestParser($this->getRequestParserMock());

        return $parser;
    }

    /**
     * Must return the tested parser object.
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\Base
     */
    abstract protected function internalGetParser();
}
