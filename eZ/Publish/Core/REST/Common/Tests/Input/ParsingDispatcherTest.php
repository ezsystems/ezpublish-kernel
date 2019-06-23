<?php

/**
 * File containing the ParsingDispatcherTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\Tests\Input;

use eZ\Publish\Core\REST\Common;
use eZ\Publish\Core\REST\Common\Input\Parser;
use PHPUnit\Framework\TestCase;

/**
 * ParsingDispatcher test class.
 */
class ParsingDispatcherTest extends TestCase
{
    /**
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     */
    public function testParseMissingContentType()
    {
        $dispatcher = new Common\Input\ParsingDispatcher();

        $dispatcher->parse([], 'text/unknown');
    }

    public function testParse()
    {
        $parser = $this->createParserMock();
        $dispatcher = new Common\Input\ParsingDispatcher(['text/html' => $parser]);

        $parser
            ->expects($this->at(0))
            ->method('parse')
            ->with([42], $dispatcher)
            ->will($this->returnValue(23));

        $this->assertSame(
            23,
            $dispatcher->parse([42], 'text/html')
        );
    }

    /**
     * Verifies that the charset specified in the Content-Type is ignored.
     */
    public function testParseCharset()
    {
        $parser = $this->createParserMock();
        $dispatcher = new Common\Input\ParsingDispatcher(['text/html' => $parser]);

        $parser
            ->expects($this->at(0))
            ->method('parse')
            ->with([42], $dispatcher)
            ->will($this->returnValue(23));

        $this->assertSame(
            23,
            $dispatcher->parse([42], 'text/html; charset=UTF-8; version=1.0')
        );
    }

    public function testParseVersion()
    {
        $parserVersionOne = $this->createParserMock();
        $parserVersionTwo = $this->createParserMock();
        $dispatcher = new Common\Input\ParsingDispatcher(
            [
                'text/html' => $parserVersionOne,
                'text/html; version=2' => $parserVersionTwo,
            ]
        );

        $parserVersionOne->expects($this->never())->method('parse');
        $parserVersionTwo->expects($this->once())->method('parse');

        $dispatcher->parse([42], 'text/html; version=2');
    }

    public function testParseStripFormat()
    {
        $parser = $this->createParserMock();
        $dispatcher = new Common\Input\ParsingDispatcher(['text/html' => $parser]);

        $parser
            ->expects($this->at(0))
            ->method('parse')
            ->with([42], $dispatcher)
            ->will($this->returnValue(23));

        $this->assertSame(
            23,
            $dispatcher->parse([42], 'text/html+json')
        );
    }

    /**
     * @return \eZ\Publish\Core\REST\Common\Input\Parser|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createParserMock()
    {
        return $this->createMock(Parser::class);
    }
}
